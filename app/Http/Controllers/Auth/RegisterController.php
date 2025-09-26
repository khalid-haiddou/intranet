<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Exception;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm(): View
    {
        return view('admin/register');
    }

    /**
     * Handle user registration
     */
    public function register(RegisterUserRequest $request): RedirectResponse|JsonResponse
    {
        try {
            DB::beginTransaction();

            // Get validated data
            $validatedData = $request->getValidatedData();

            // Create user
            $user = $this->createUser($validatedData);

            // Log the registration
            Log::info('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'account_type' => $user->account_type,
                'membership_plan' => $user->membership_plan,
            ]);

            DB::commit();

            // Handle response based on request type
            if ($request->expectsJson()) {
                return $this->jsonSuccessResponse($user);
            }

            return $this->webSuccessResponse($user);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('User registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return $this->jsonErrorResponse($e->getMessage());
            }

            return back()->withInput($request->except(['password', 'password_confirmation']))
                        ->withErrors(['registration' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.']);
        }
    }

    /**
     * Create a new user instance
     */
    private function createUser(array $data): User
    {
        $userData = [
            'account_type' => $data['account_type'],
            'role' => $data['role'] ?? User::ROLE_USER,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'] ?? null,
            'membership_plan' => $data['membership_plan'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'password' => $data['password'], // Will be hashed automatically
            'newsletter' => $data['newsletter'] ?? false,
            'terms_accepted' => true,
            'is_active' => true,
        ];

        // Add account-specific fields
        if ($data['account_type'] === User::TYPE_INDIVIDUAL) {
            $userData = array_merge($userData, [
                'prenom' => $data['prenom'],
                'nom' => $data['nom'],
                'cin' => $data['cin'],
            ]);
        } else {
            $userData = array_merge($userData, [
                'company_name' => $data['company_name'],
                'rc' => $data['rc'],
                'ice' => $data['ice'],
                'legal_representative' => $data['legal_representative'],
            ]);
        }

        return User::create($userData);
    }

    /**
     * Handle successful JSON response
     */
    private function jsonSuccessResponse(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie ! Veuillez vous connecter pour accéder à votre espace.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->display_name,
                    'email' => $user->email,
                    'account_type' => $user->account_type,
                    'membership_plan' => $user->membership_plan_label,
                    'role' => $user->role,
                ],
                'redirect_url' => route('login'),
            ]
        ], 201);
    }

    /**
     * Handle successful web response
     */
    private function webSuccessResponse(User $user): RedirectResponse
    {
        $message = sprintf(
            'Compte créé avec succès pour %s ! Veuillez vous connecter pour accéder à votre espace.',
            $user->display_name
        );

        // Redirect to login page instead of auto-login
        return redirect()->route('login')
                       ->with('success', $message);
    }

    /**
     * Handle JSON error response
     */
    private function jsonErrorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'inscription',
            'error' => $message
        ], 422);
    }

    /**
     * Get registration statistics (for admin use)
     */
    public function getRegistrationStats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_admins' => User::byRole('admin')->count(),
                'total_individuals' => User::byAccountType('individual')->count(),
                'total_companies' => User::byAccountType('company')->count(),
                'active_users' => User::active()->count(),
                'membership_breakdown' => [
                    'hot_desk' => User::byMembershipPlan('hot-desk')->count(),
                    'bureau_dedie' => User::byMembershipPlan('bureau-dedie')->count(),
                    'bureau_prive' => User::byMembershipPlan('bureau-prive')->count(),
                ],
                'recent_registrations' => User::latest()->take(5)->get([
                    'id', 'email', 'account_type', 'membership_plan', 'created_at'
                ])->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'email' => $user->email,
                        'account_type' => $user->account_type,
                        'membership_plan' => $user->membership_plan,
                        'registered_at' => $user->created_at->format('d/m/Y H:i'),
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get registration stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Bulk update user roles (admin only)
     */
    public function updateUserRole(User $user, string $role): JsonResponse
    {
        try {
            if (!in_array($role, ['admin', 'user'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rôle invalide'
                ], 422);
            }

            $user->update(['role' => $role]);

            Log::info('User role updated', [
                'user_id' => $user->id,
                'new_role' => $role,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle mis à jour avec succès",
                'data' => [
                    'user_id' => $user->id,
                    'new_role' => $role
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update user role', [
                'user_id' => $user->id,
                'role' => $role,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle'
            ], 500);
        }
    }

    /**
     * Activate/deactivate user account (admin only)
     */
    public function toggleUserStatus(User $user): JsonResponse
    {
        try {
            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            $action = $newStatus ? 'activé' : 'désactivé';

            Log::info('User account status changed', [
                'user_id' => $user->id,
                'new_status' => $newStatus,
                'changed_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Compte {$action} avec succès",
                'data' => [
                    'user_id' => $user->id,
                    'is_active' => $newStatus
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle user status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }
}