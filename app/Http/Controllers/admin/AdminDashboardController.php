<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Space;
use App\Models\SpaceReservation;
use App\Models\SpaceMaintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index(): View
    {
        $stats = $this->getDashboardStatistics();
        $chartData = $this->getChartData();
        $alerts = $this->getPriorityAlerts();
        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard', compact('stats', 'chartData', 'alerts', 'recentActivity'));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Financial Statistics
        $currentRevenue = Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$currentMonth, now()])
            ->sum('total_amount') ?? 0;

        $lastMonthRevenue = Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])
            ->sum('total_amount') ?? 0;

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

        // Member Statistics
        $totalMembers = User::where('role', User::ROLE_USER)->count();
        $activeMembers = User::where('role', User::ROLE_USER)
            ->where('is_active', true)
            ->count();

        $newMembersThisMonth = User::where('role', User::ROLE_USER)
            ->whereBetween('created_at', [$currentMonth, now()])
            ->count();

        $newMembersLastMonth = User::where('role', User::ROLE_USER)
            ->whereBetween('created_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])
            ->count();

        $memberGrowth = $newMembersLastMonth > 0
            ? round((($newMembersThisMonth - $newMembersLastMonth) / $newMembersLastMonth) * 100, 1)
            : ($newMembersThisMonth > 0 ? 100 : 0);

        // Space Occupation Statistics
        $totalSpaces = Space::where('is_active', true)->count();
        $occupiedSpaces = Space::whereIn('status', [Space::STATUS_OCCUPIED, Space::STATUS_RESERVED])->count();
        $occupationRate = $totalSpaces > 0 ? round(($occupiedSpaces / $totalSpaces) * 100, 1) : 0;

        // Today's vs Yesterday's reservations
        $todayReservations = SpaceReservation::whereDate('starts_at', today())
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->count();

        $yesterdayReservations = SpaceReservation::whereDate('starts_at', today()->subDay())
            ->whereIn('status', ['confirmed', 'checked_in', 'completed'])
            ->count();

        $reservationsTrend = $yesterdayReservations > 0
            ? round((($todayReservations - $yesterdayReservations) / $yesterdayReservations) * 100, 1)
            : 0;

        // Alerts Count
        $overdueInvoices = Invoice::overdue()->count();
        $pendingMaintenance = SpaceMaintenance::whereIn('status', ['scheduled', 'in_progress'])->count();
        $pendingMembers = User::where('role', User::ROLE_USER)
            ->where('is_active', false)
            ->whereNull('email_verified_at')
            ->count();

        $totalAlerts = $overdueInvoices + $pendingMaintenance + $pendingMembers;

        return [
            // Financial
            'revenue' => $currentRevenue,
            'revenue_growth' => $revenueGrowth,
            'revenue_trend' => $revenueGrowth >= 0 ? 'positive' : 'negative',

            // Members
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'new_members' => $newMembersThisMonth,
            'member_growth' => $memberGrowth,
            'member_trend' => $memberGrowth >= 0 ? 'positive' : 'negative',

            // Spaces
            'occupation_rate' => $occupationRate,
            'today_reservations' => $todayReservations,
            'reservations_trend' => $reservationsTrend,

            // Alerts
            'total_alerts' => $totalAlerts,
            'overdue_invoices' => $overdueInvoices,
            'pending_maintenance' => $pendingMaintenance,
            'pending_members' => $pendingMembers,
        ];
    }

    /**
     * Get chart data for revenue and space distribution
     */
    private function getChartData(): array
    {
        // Revenue chart data (last 9 months)
        $revenueData = [];
        $months = [];

        for ($i = 8; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $date->locale('fr')->format('M');

            $monthRevenue = Invoice::where('status', Invoice::STATUS_PAID)
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount') ?? 0;

            $revenueData[] = round($monthRevenue, 2);
        }

        // Space occupation distribution
        $hotDeskCount = User::where('role', User::ROLE_USER)
            ->where('membership_plan', 'hot-desk')
            ->where('is_active', true)
            ->count();

        $bureauDedieCount = User::where('role', User::ROLE_USER)
            ->where('membership_plan', 'bureau-dedie')
            ->where('is_active', true)
            ->count();

        $bureauPriveCount = User::where('role', User::ROLE_USER)
            ->where('membership_plan', 'bureau-prive')
            ->where('is_active', true)
            ->count();

        $totalSpaces = Space::where('is_active', true)->count();
        $occupiedSpaces = $hotDeskCount + $bureauDedieCount + $bureauPriveCount;
        $freeSpaces = max(0, $totalSpaces - $occupiedSpaces);

        return [
            'revenue' => [
                'labels' => $months,
                'data' => $revenueData,
            ],
            'occupation' => [
                'labels' => ['Hot Desk', 'Bureau Dédié', 'Bureau Privé', 'Libre'],
                'data' => [$hotDeskCount, $bureauDedieCount, $bureauPriveCount, $freeSpaces],
            ],
        ];
    }

    /**
     * Get priority alerts
     */
    private function getPriorityAlerts(): array
    {
        $alerts = [];

        // Overdue invoices
        $overdueCount = Invoice::overdue()->count();
        if ($overdueCount > 0) {
            $alerts[] = [
                'icon' => 'fas fa-credit-card',
                'icon_color' => '#E74C3C',
                'icon_bg' => 'rgba(231, 76, 60, 0.1)',
                'title' => "{$overdueCount} paiement" . ($overdueCount > 1 ? 's' : '') . " en retard",
                'description' => 'Relances automatiques envoyées',
            ];
        }

        // Upcoming maintenance
        $upcomingMaintenance = SpaceMaintenance::where('status', 'scheduled')
            ->whereBetween('scheduled_at', [now(), now()->addDays(2)])
            ->orderBy('scheduled_at')
            ->first();

        if ($upcomingMaintenance) {
            $alerts[] = [
                'icon' => 'fas fa-tools',
                'icon_color' => '#F39C12',
                'icon_bg' => 'rgba(243, 156, 18, 0.1)',
                'title' => 'Maintenance programmée',
                'description' => $upcomingMaintenance->space->name . ' - ' . $upcomingMaintenance->scheduled_at->format('d/m à H:i'),
            ];
        }

        // Pending member requests
        $pendingCount = User::where('role', User::ROLE_USER)
            ->where('is_active', false)
            ->whereNull('email_verified_at')
            ->count();

        if ($pendingCount > 0) {
            $alerts[] = [
                'icon' => 'fas fa-user-clock',
                'icon_color' => '#3498DB',
                'icon_bg' => 'rgba(52, 152, 219, 0.1)',
                'title' => "{$pendingCount} demande" . ($pendingCount > 1 ? 's' : '') . " d'adhésion",
                'description' => 'En attente de validation',
            ];
        }

        // Add a default message if no alerts
        if (empty($alerts)) {
            $alerts[] = [
                'icon' => 'fas fa-check-circle',
                'icon_color' => '#27AE60',
                'icon_bg' => 'rgba(39, 174, 96, 0.1)',
                'title' => 'Aucune alerte',
                'description' => 'Tout fonctionne normalement',
            ];
        }

        return $alerts;
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $activities = [];

        // Recent member registrations
        $recentMembers = User::where('role', User::ROLE_USER)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentMembers as $member) {
            $activities[] = [
                'icon' => 'fas fa-user-check',
                'icon_color' => '#27AE60',
                'icon_bg' => 'rgba(39, 174, 96, 0.1)',
                'title' => 'Nouveau membre inscrit',
                'description' => $member->display_name . ' - ' . ($member->is_active ? 'Actif' : 'En attente'),
                'time' => $member->created_at->diffForHumans(),
            ];
        }

        // Recent reservations
        $recentReservations = SpaceReservation::with(['space', 'user'])
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentReservations as $reservation) {
            $activities[] = [
                'icon' => 'fas fa-calendar-check',
                'icon_color' => '#9B59B6',
                'icon_bg' => 'rgba(155, 89, 182, 0.1)',
                'title' => 'Nouvelle réservation',
                'description' => $reservation->user->display_name . ' - ' . $reservation->space->name,
                'time' => $reservation->created_at->diffForHumans(),
            ];
        }

        // Recent payments
        $recentPayments = Invoice::where('status', Invoice::STATUS_PAID)
            ->with('user')
            ->orderBy('paid_at', 'desc')
            ->take(2)
            ->get();

        foreach ($recentPayments as $payment) {
            $activities[] = [
                'icon' => 'fas fa-money-bill-wave',
                'icon_color' => '#FFCC01',
                'icon_bg' => 'rgba(255, 204, 1, 0.1)',
                'title' => 'Paiement reçu',
                'description' => $payment->user->display_name . ' - ' . number_format($payment->total_amount, 2) . ' MAD',
                'time' => $payment->paid_at->diffForHumans(),
            ];
        }

        // Sort by most recent and limit to 6 items
        usort($activities, function($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });

        return array_slice($activities, 0, 6);
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getData(): JsonResponse
    {
        try {
            $stats = $this->getDashboardStatistics();
            $chartData = $this->getChartData();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'chartData' => $chartData,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données'
            ], 500);
        }
    }

    /**
     * Refresh dashboard statistics
     */
    public function refresh(): JsonResponse
    {
        try {
            $stats = $this->getDashboardStatistics();
            $alerts = $this->getPriorityAlerts();
            $recentActivity = $this->getRecentActivity();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'alerts' => $alerts,
                    'activity' => $recentActivity,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'actualisation'
            ], 500);
        }
    }
}