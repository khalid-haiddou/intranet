<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm(): View
    {
        return view('login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request): RedirectResponse|JsonResponse
    {
        // Rate limiting
        $this->checkRateLimit($request);

        try {
            // Validate login credentials
            $credentials = $this->validateLogin($request);

            // Attempt authentication
            if ($this->attemptLogin($request, $credentials)) {
                return $this->sendLoginResponse($request);
            }

            // Authentication failed
            return $this->sendFailedLoginResponse($request);

        } catch (ValidationException $e) {
            // Handle validation errors
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                           ->withErrors($e->errors())
                           ->withInput($request->except('password'));
        }
    }

    /**
     * Validate login request
     */
    protected function validateLogin(Request $request): array
    {
        return $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'remember' => 'boolean'
        ], [
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ]);
    }

    /**
     * Attempt to log the user into the application
     */
    protected function attemptLogin(Request $request, array $credentials): bool
    {
        $remember = $request->filled('remember') && $request->boolean('remember');
        
        // Remove remember from credentials
        unset($credentials['remember']);
        
        // Add is_active check to credentials
        $credentials['is_active'] = true;

        return Auth::attempt($credentials, $remember);
    }

    /**
     * Send the response after the user was authenticated
     */
    protected function sendLoginResponse(Request $request): RedirectResponse|JsonResponse
    {
        $request->session()->regenerate();
        
        $user = Auth::user();
        $user->updateLastLogin();

        // Clear rate limiting
        RateLimiter::clear($this->throttleKey($request));

        // Log successful login
        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $message = sprintf('Bienvenue %s ! Connexion réussie.', $user->display_name);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->display_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'account_type' => $user->account_type,
                    'membership_plan' => $user->membership_plan_label,
                ],
                'redirect_url' => $this->getRedirectUrl($user)
            ]);
        }

        return redirect()->to($this->getRedirectUrl($user))
                       ->with('success', $message);
    }

    /**
     * Get redirect URL based on user role
     */
    protected function getRedirectUrl(User $user): string
    {
        // Check for intended URL first
        if (session()->has('url.intended')) {
            return session()->pull('url.intended');
        }

        // Role-based redirect
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_USER => route('dashboard'),
            default => route('user.dashboard'), // fallback
        };
    }

    /**
     * Send failed login response
     */
    protected function sendFailedLoginResponse(Request $request): RedirectResponse|JsonResponse
    {
        // Increment rate limiting
        RateLimiter::hit($this->throttleKey($request), 60 * 5); // 5 minutes

        // Log failed login attempt
        Log::warning('Failed login attempt', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        $message = 'Ces identifiants ne correspondent à aucun compte actif.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [
                    'email' => [$message]
                ]
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => $message,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Vous avez été déconnecté avec succès.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('login')
            ]);
        }

        return redirect()->route('login')
                       ->with('success', $message);
    }

    /**
     * Check rate limiting for login attempts
     */
    protected function checkRateLimit(Request $request): void
    {
        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) { // 5 attempts per 5 minutes
            $seconds = RateLimiter::availableIn($key);
            
            $message = sprintf(
                'Trop de tentatives de connexion. Veuillez réessayer dans %d minutes.',
                ceil($seconds / 60)
            );
            
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'email' => $message,
                ])->status(429);
            }
            
            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }
    }

    /**
     * Get the throttle key for the given request
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }

    /**
     * Get login statistics (for admin dashboard)
     */
    public function getLoginStats(): JsonResponse
    {
        try {
            $stats = [
                'total_logins_today' => User::whereDate('last_login_at', today())->count(),
                'total_active_users' => User::active()->count(),
                'recent_logins' => User::whereNotNull('last_login_at')
                    ->latest('last_login_at')
                    ->take(10)
                    ->get([
                        'id', 'email', 'account_type', 'role', 'last_login_at'
                    ])
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'email' => $user->email,
                            'account_type' => $user->account_type,
                            'role' => $user->role,
                            'last_login' => $user->last_login_at?->format('d/m/Y H:i'),
                        ];
                    }),
                'login_activity' => [
                    'today' => User::whereDate('last_login_at', today())->count(),
                    'yesterday' => User::whereDate('last_login_at', today()->subDay())->count(),
                    'this_week' => User::whereBetween('last_login_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])->count(),
                    'this_month' => User::whereBetween('last_login_at', [
                        now()->startOfMonth(),
                        now()->endOfMonth()
                    ])->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get login stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Check if user exists (for password reset functionality)
     */
    public function checkUserExists(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'exists' => $user !== null,
            'active' => $user && $user->is_active
        ]);
    }

    /**
     * Get current authenticated user info
     */
    public function me(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->display_name,
                'email' => $user->email,
                'role' => $user->role,
                'account_type' => $user->account_type,
                'membership_plan' => $user->membership_plan_label,
                'billing_cycle' => $user->billing_cycle_label,
                'price_description' => $user->price_description,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at?->format('d/m/Y H:i'),
            ]
        ]);
    }
}