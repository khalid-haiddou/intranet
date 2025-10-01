<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Exception;

class UserProfileController extends Controller
{
    /**
     * Display the user profile page
     */
    public function index(): View
    {
        // Pour tester sans auth, utilisez un ID fixe
        // En production, remplacez par: $user = auth()->user();
        $user = User::where('role', User::ROLE_USER)->first(); 
        
        if (!$user) {
            abort(404, 'Utilisateur non trouvé');
        }

        // Get user statistics
        $stats = $this->getUserStatistics($user);

        return view('users.profil', compact('user', 'stats'));
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics(User $user): array
    {
        // Days active (days since registration)
        $daysActive = $user->created_at->diffInDays(now());

        // Reservations count
        $reservationsCount = $user->spaceReservations()->count();

        // Events attended
        $eventsCount = $user->registeredEvents()->count();

        // Connections (login count - using last_login_at as proxy)
        $connectionsCount = $user->last_login_at ? 
            $user->created_at->diffInMonths(now()) * 4 : 0; // Approximation

        return [
            'days_active' => $daysActive,
            'reservations' => $reservationsCount,
            'events' => $eventsCount,
            'connections' => $connectionsCount,
        ];
    }

    /**
     * Update personal information
     */
    public function updatePersonalInfo(Request $request): JsonResponse
    {
        try {
            // Pour tester sans auth
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            if ($user->isIndividual()) {
                $validatedData = $request->validate([
                    'prenom' => 'required|string|max:100',
                    'nom' => 'required|string|max:100',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'phone' => 'required|string|max:20',
                    'address' => 'nullable|string|max:500',
                    'profession' => 'nullable|string|max:200',
                ]);
            } else {
                $validatedData = $request->validate([
                    'company_name' => 'required|string|max:200',
                    'legal_representative' => 'required|string|max:200',
                    'email' => 'required|email|unique:users,email,' . $user->id,
                    'phone' => 'required|string|max:20',
                    'address' => 'nullable|string|max:500',
                    'profession' => 'nullable|string|max:200',
                ]);
            }

            $user->update($validatedData);

            Log::info('User profile updated', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($validatedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Informations mises à jour avec succès',
                'data' => [
                    'user' => [
                        'display_name' => $user->display_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'profession' => $user->profession,
                    ]
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update profile', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'errors' => $request->validator ? $request->validator->errors() : []
            ], 422);
        }
    }

    /**
     * Upload avatar image
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $validatedData = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            ]);

            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            // Store new avatar
            $image = $request->file('avatar');
            $imageName = 'avatar_' . $user->id . '_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'uploads/avatars/' . $imageName;
            
            // Create directory if not exists
            if (!file_exists(public_path('uploads/avatars'))) {
                mkdir(public_path('uploads/avatars'), 0777, true);
            }
            
            // Move the file
            $image->move(public_path('uploads/avatars'), $imageName);

            // Update user
            $user->update(['avatar' => $imagePath]);

            Log::info('Avatar uploaded', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil mise à jour avec succès',
                'data' => [
                    'avatar_url' => asset($imagePath)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to upload avatar', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload de l\'image'
            ], 422);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $validatedData = $request->validate([
                'current_password' => 'required',
                'new_password' => ['required', 'confirmed', Password::min(8)],
            ]);

            // Verify current password
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validatedData['new_password'])
            ]);

            Log::info('User password changed', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe modifié avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update password', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mot de passe'
            ], 422);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $validatedData = $request->validate([
                'newsletter' => 'boolean',
            ]);

            $user->update($validatedData);

            Log::info('Notification settings updated', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres de notification mis à jour'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update notifications', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    /**
     * Deactivate account
     */
    public function deactivate(): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $user->update(['is_active' => false]);

            // Cancel all future reservations
            $user->spaceReservations()
                ->where('starts_at', '>', now())
                ->whereIn('status', ['confirmed', 'pending'])
                ->update(['status' => 'cancelled']);

            Log::warning('User account deactivated', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Compte désactivé avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to deactivate account', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation'
            ], 500);
        }
    }

    /**
     * Delete account (soft delete)
     */
    public function destroy(): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            // Cancel all reservations
            $user->spaceReservations()
                ->whereIn('status', ['confirmed', 'pending', 'checked_in'])
                ->update(['status' => 'cancelled']);

            // Soft delete or hard delete based on your needs
            $user->delete();

            Log::warning('User account deleted', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Compte supprimé avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete account', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }
}