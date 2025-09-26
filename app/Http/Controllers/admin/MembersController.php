<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class MembersController extends Controller
{
    /**
     * Display the members management page
     */
    public function index(Request $request): View
    {
        // Get filter parameters
        $filters = [
            'plan' => $request->get('plan'),
            'status' => $request->get('status'),
            'search' => $request->get('search'),
            'account_type' => $request->get('account_type'),
        ];

        // Get members with filters
        $members = $this->getFilteredMembers($filters);
        
        // Get statistics
        $stats = $this->getMembersStatistics();
        
        // Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        // Get pending requests
        $pendingRequests = $this->getPendingRequests();

        return view('admin.members', compact('members', 'stats', 'recentActivity', 'pendingRequests', 'filters'));
    }

    /**
     * Get filtered members list
     */
    private function getFilteredMembers(array $filters)
    {
        $query = User::query()->where('role', User::ROLE_USER);

        // Apply filters
        if (!empty($filters['plan'])) {
            $query->where('membership_plan', $filters['plan']);
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'pending':
                    $query->where('is_active', false)->whereNull('email_verified_at');
                    break;
            }
        }

        if (!empty($filters['account_type'])) {
            $query->where('account_type', $filters['account_type']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', $search)
                  ->orWhere('prenom', 'like', $search)
                  ->orWhere('nom', 'like', $search)
                  ->orWhere('company_name', 'like', $search);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Get members statistics
     */
    private function getMembersStatistics(): array
    {
        $totalMembers = User::where('role', User::ROLE_USER)->count();
        $activeMembers = User::where('role', User::ROLE_USER)->where('is_active', true)->count();
        $inactiveMembers = User::where('role', User::ROLE_USER)->where('is_active', false)->count();
        $pendingMembers = User::where('role', User::ROLE_USER)
            ->where('is_active', false)
            ->whereNull('email_verified_at')
            ->count();

        // Calculate trends (compared to last month)
        $lastMonthTotal = User::where('role', User::ROLE_USER)
            ->where('created_at', '<', now()->subMonth())
            ->count();
        
        $newMembersThisMonth = $totalMembers - $lastMonthTotal;
        $growthPercentage = $lastMonthTotal > 0 ? round(($newMembersThisMonth / $lastMonthTotal) * 100, 1) : 0;

        // Membership plan breakdown
        $membershipBreakdown = [
            'hot-desk' => User::byMembershipPlan('hot-desk')->byRole('user')->count(),
            'bureau-dedie' => User::byMembershipPlan('bureau-dedie')->byRole('user')->count(),
            'bureau-prive' => User::byMembershipPlan('bureau-prive')->byRole('user')->count(),
        ];

        return [
            'total' => $totalMembers,
            'active' => $activeMembers,
            'inactive' => $inactiveMembers,
            'pending' => $pendingMembers,
            'active_percentage' => $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100, 1) : 0,
            'growth_percentage' => $growthPercentage,
            'new_this_month' => $newMembersThisMonth,
            'membership_breakdown' => $membershipBreakdown,
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        // Get recently registered users
        $recentRegistrations = User::where('role', User::ROLE_USER)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'registration',
                    'message' => 'Nouveau membre inscrit',
                    'details' => $user->display_name . ' - ' . ($user->is_active ? 'Actif' : 'En attente validation'),
                    'time' => $user->created_at->diffForHumans(),
                    'icon' => 'fas fa-user-plus',
                    'color' => 'success'
                ];
            });

        // Get recently activated users
        $recentlyActivated = User::where('role', User::ROLE_USER)
            ->where('is_active', true)
            ->where('updated_at', '>', now()->subDays(7))
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'activation',
                    'message' => 'Membre activé',
                    'details' => $user->display_name . ' - Accès ' . $user->membership_plan_label,
                    'time' => $user->updated_at->diffForHumans(),
                    'icon' => 'fas fa-user-check',
                    'color' => 'info'
                ];
            });

        return $recentRegistrations->merge($recentlyActivated)->take(10)->toArray();
    }

    /**
     * Get pending requests
     */
    private function getPendingRequests(): array
    {
        $pendingVerification = User::where('role', User::ROLE_USER)
            ->where('is_active', false)
            ->whereNull('email_verified_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'verification',
                    'message' => 'Vérification d\'identité',
                    'details' => $user->display_name . ' - Documents en attente',
                    'time' => $user->created_at->diffForHumans(),
                    'user_id' => $user->id,
                    'icon' => 'fas fa-clock',
                    'color' => 'warning'
                ];
            });

        return $pendingVerification->toArray();
    }

    /**
     * Get member details via AJAX
     */
    public function show(User $user): JsonResponse
    {
        try {
            $memberData = [
                'id' => $user->id,
                'display_name' => $user->display_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'account_type' => $user->account_type,
                'account_type_label' => $user->isIndividual() ? 'Particulier' : 'Entreprise',
                'membership_plan' => $user->membership_plan,
                'membership_plan_label' => $user->membership_plan_label,
                'billing_cycle' => $user->billing_cycle,
                'billing_cycle_label' => $user->billing_cycle_label,
                'price' => $user->price,
                'price_description' => $user->price_description,
                'is_active' => $user->is_active,
                'status_label' => $user->is_active ? 'Actif' : 'Inactif',
                'created_at' => $user->created_at->format('d/m/Y H:i'),
                'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
            ];

            // Add specific fields based on account type
            if ($user->isIndividual()) {
                $memberData['prenom'] = $user->prenom;
                $memberData['nom'] = $user->nom;
                $memberData['cin'] = $user->cin;
            } else {
                $memberData['company_name'] = $user->company_name;
                $memberData['rc'] = $user->rc;
                $memberData['ice'] = $user->ice;
                $memberData['legal_representative'] = $user->legal_representative;
            }

            return response()->json([
                'success' => true,
                'data' => $memberData
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get member details', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails du membre'
            ], 500);
        }
    }

    /**
     * Toggle member status (activate/deactivate)
     */
    public function toggleStatus(User $user): JsonResponse
    {
        try {
            $newStatus = !$user->is_active;
            $user->update(['is_active' => $newStatus]);

            $action = $newStatus ? 'activé' : 'désactivé';

            Log::info('Member status changed', [
                'user_id' => $user->id,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Membre {$action} avec succès",
                'data' => [
                    'user_id' => $user->id,
                    'is_active' => $newStatus,
                    'status_label' => $newStatus ? 'Actif' : 'Inactif'
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to toggle member status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }

    /**
     * Approve member (activate and verify email)
     */
    public function approve(User $user): JsonResponse
    {
        try {
            $user->update([
                'is_active' => true,
                'email_verified_at' => now()
            ]);

            Log::info('Member approved', [
                'user_id' => $user->id,
                'approved_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membre approuvé avec succès',
                'data' => [
                    'user_id' => $user->id,
                    'is_active' => true,
                    'status_label' => 'Actif'
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to approve member', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation'
            ], 500);
        }
    }

    /**
     * Reject member
     */
    public function reject(User $user): JsonResponse
    {
        try {
            // Instead of deleting, we can mark as rejected or delete based on requirements
            $user->delete();

            Log::info('Member rejected and deleted', [
                'user_id' => $user->id,
                'rejected_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membre rejeté avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reject member', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet'
            ], 500);
        }
    }

    /**
     * Update member information
     */
    public function update(Request $request, User $user): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'required|string|max:20',
                'address' => 'nullable|string|max:500',
                'membership_plan' => 'required|in:hot-desk,bureau-dedie,bureau-prive',
                'billing_cycle' => 'required|in:daily,weekly,biweekly,monthly',
                'price' => 'required|numeric|min:0',
                
                // Individual fields
                'prenom' => $user->isIndividual() ? 'required|string|max:100' : 'nullable',
                'nom' => $user->isIndividual() ? 'required|string|max:100' : 'nullable',
                'cin' => $user->isIndividual() ? 'required|string|max:20|unique:users,cin,' . $user->id : 'nullable',
                
                // Company fields
                'company_name' => $user->isCompany() ? 'required|string|max:200' : 'nullable',
                'rc' => $user->isCompany() ? 'required|string|max:50|unique:users,rc,' . $user->id : 'nullable',
                'ice' => $user->isCompany() ? 'required|string|max:50|unique:users,ice,' . $user->id : 'nullable',
                'legal_representative' => $user->isCompany() ? 'required|string|max:200' : 'nullable',
            ]);

            $user->update($validatedData);

            Log::info('Member updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membre mis à jour avec succès',
                'data' => [
                    'user_id' => $user->id,
                    'display_name' => $user->display_name,
                    'membership_plan_label' => $user->membership_plan_label,
                    'price_description' => $user->price_description
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update member', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'errors' => $request->validator ? $request->validator->errors() : []
            ], 422);
        }
    }

    /**
     * Get members statistics for AJAX
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->getMembersStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get members stats', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Export members data
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'plan' => $request->get('plan'),
                'status' => $request->get('status'),
                'account_type' => $request->get('account_type'),
            ];

            $members = $this->getFilteredMembers($filters)->items();

            $csvData = [];
            $csvData[] = ['Nom', 'Email', 'Téléphone', 'Type de compte', 'Plan', 'Prix', 'Statut', 'Date d\'inscription'];

            foreach ($members as $member) {
                $csvData[] = [
                    $member->display_name,
                    $member->email,
                    $member->phone,
                    $member->isIndividual() ? 'Particulier' : 'Entreprise',
                    $member->membership_plan_label,
                    $member->price_description,
                    $member->is_active ? 'Actif' : 'Inactif',
                    $member->created_at->format('d/m/Y')
                ];
            }

            $filename = 'members_export_' . date('Y_m_d_H_i_s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('Failed to export members', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de l\'exportation des données');
        }
    }
}