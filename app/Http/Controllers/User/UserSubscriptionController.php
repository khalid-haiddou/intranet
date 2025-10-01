<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class UserSubscriptionController extends Controller
{
    /**
     * Display the subscription page
     */
    public function index(): View
    {
        // Pour tester sans auth
        $user = User::where('role', User::ROLE_USER)->first();
        // En production: $user = auth()->user();

        if (!$user) {
            abort(404, 'Utilisateur non trouvé');
        }

        // Get subscription data
        $subscriptionData = $this->getSubscriptionData($user);
        
        // Get usage statistics
        $usageStats = $this->getUsageStatistics($user);
        
        // Get invoices history
        $invoices = $this->getInvoicesHistory($user);

        return view('users.abonnement', compact('user', 'subscriptionData', 'usageStats', 'invoices'));
    }

    /**
     * Get subscription data
     */
    private function getSubscriptionData(User $user): array
    {
        // Calculate days remaining until next billing
        $today = now();
        $nextBillingDate = $this->calculateNextBillingDate($user);
        $daysRemaining = (int) $today->diffInDays($nextBillingDate, false); // CAST TO INTEGER

        // Calculate taxes (20% TVA)
        $taxRate = 0.20;
        $basePrice = $user->price ?? 0;
        $taxAmount = $basePrice * $taxRate;
        $totalAmount = $basePrice + $taxAmount;

        return [
            'plan_name' => $user->membership_plan_label,
            'plan_type' => $user->membership_plan,
            'billing_cycle' => $user->billing_cycle_label,
            'is_active' => $user->is_active,
            'member_since' => $user->created_at->locale('fr')->isoFormat('MMMM YYYY'),
            'days_remaining' => max(0, $daysRemaining), // Already integer now
            'base_price' => $basePrice,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'next_billing_date' => $nextBillingDate->format('d M Y'),
            'auto_renewal' => true,
        ];
    }

    /**
     * Calculate next billing date based on billing cycle
     */
    private function calculateNextBillingDate(User $user): Carbon
    {
        $today = now();
        
        return match($user->billing_cycle) {
            'daily' => $today->copy()->addDay(),
            'weekly' => $today->copy()->addWeek(),
            'biweekly' => $today->copy()->addWeeks(2),
            'monthly' => $today->copy()->addMonth(),
            default => $today->copy()->addMonth(),
        };
    }

    /**
     * Get usage statistics for current month
     */
    private function getUsageStatistics(User $user): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Get reservations for this month
        $monthReservations = $user->spaceReservations()
            ->whereBetween('starts_at', [$startOfMonth, $endOfMonth])
            ->whereIn('status', ['completed', 'checked_in'])
            ->get();

        // Calculate total hours
        $totalHours = $monthReservations->sum(function ($reservation) {
            return $reservation->duration_hours;
        });

        // Calculate days used (unique dates)
        $daysUsed = $monthReservations->pluck('starts_at')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->count();

        // Calculate usage percentage (assuming 160 hours = 100% for monthly)
        $maxHours = match($user->billing_cycle) {
            'daily' => 8,
            'weekly' => 40,
            'biweekly' => 80,
            'monthly' => 160,
            default => 160,
        };

        $usagePercentage = $maxHours > 0 ? round(($totalHours / $maxHours) * 100, 1) : 0;
        $usagePercentage = min(100, $usagePercentage); // Cap at 100%

        return [
            'total_hours' => round($totalHours, 0),
            'days_used' => $daysUsed,
            'usage_percentage' => $usagePercentage,
            'max_hours' => $maxHours,
        ];
    }

    /**
     * Get invoices history
     */
    private function getInvoicesHistory(User $user)
    {
        return Invoice::where('user_id', $user->id)
            ->orderBy('issued_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'month' => $invoice->issued_at->locale('fr')->isoFormat('MMMM YYYY'),
                    'issued_date' => $invoice->issued_at->format('d M Y'),
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'status_label' => $invoice->status === Invoice::STATUS_PAID ? 'Payé' : 'En attente',
                ];
            });
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice($invoiceId)
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $invoice = Invoice::where('user_id', $user->id)
                ->where('id', $invoiceId)
                ->firstOrFail();

            $invoice->load('user');

            $company = [
                'name' => 'LA STATION',
                'address' => '32 Impass Siam, 1er étage, appartement 3, Océan',
                'city' => 'Rabat Rabat-Salé-Zemmour-Zaer',
                'country' => 'Morocco',
                'ice' => '002672845000021',
                'if' => '47284949',
                'rc' => '147923',
            ];

            $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company'));
            return $pdf->download("facture_{$invoice->invoice_number}.pdf");

        } catch (Exception $e) {
            Log::error('Failed to download invoice', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors du téléchargement de la facture');
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            // Désactiver le compte à la fin de la période de facturation
            // Pour l'instant, on désactive immédiatement (à adapter selon votre logique)
            $user->update(['is_active' => false]);

            // Annuler les réservations futures
            $user->spaceReservations()
                ->where('starts_at', '>', now())
                ->whereIn('status', ['confirmed', 'pending'])
                ->update(['status' => 'cancelled']);

            Log::warning('Subscription cancelled', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Votre abonnement sera annulé à la fin de la période de facturation actuelle'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to cancel subscription', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de l\'abonnement'
            ], 500);
        }
    }

    /**
     * Get subscription data via AJAX (RENAMED METHOD)
     */
    public function getSubscriptionDataAjax(): JsonResponse
    {
        try {
            $user = User::where('role', User::ROLE_USER)->first();
            // En production: $user = auth()->user();

            $subscriptionData = $this->getSubscriptionData($user);
            $usageStats = $this->getUsageStatistics($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'subscription' => $subscriptionData,
                    'usage' => $usageStats,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get subscription data', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données'
            ], 500);
        }
    }
}