<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Devis;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class FinancesController extends Controller
{
    public function index(): View
    {
        $stats = $this->getFinancialStatistics();
        $recentInvoices = $this->getRecentInvoices();
        $recentDevis = $this->getRecentDevis(); // ADD THIS LINE
        $recentPayments = $this->getRecentPayments();
        $activeSubscriptions = $this->getActiveSubscriptions();
        $recentExpenses = $this->getRecentExpenses();
        $chartData = $this->getChartData();
        $users = User::where('role', 'user')->where('is_active', true)->get();

        return view('admin.finances', compact(
            'stats', 'recentInvoices', 'recentDevis', 'recentPayments', // ADD recentDevis HERE
            'activeSubscriptions', 'recentExpenses', 'chartData', 'users'
        ));
    }

    private function getFinancialStatistics(): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        // Revenue
        $currentRevenue = Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$currentMonth, now()])
            ->sum('total_amount') ?? 0;
            
        $lastMonthRevenue = Invoice::where('status', Invoice::STATUS_PAID)
            ->whereBetween('paid_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])
            ->sum('total_amount') ?? 0;

        // Expenses
        $currentExpenses = Expense::paid()
            ->whereBetween('expense_date', [$currentMonth, now()])
            ->sum('amount') ?? 0;
            
        $lastMonthExpenses = Expense::paid()
            ->whereBetween('expense_date', [$lastMonth, $lastMonth->copy()->endOfMonth()])
            ->sum('amount') ?? 0;

        // Net Profit
        $currentNetProfit = $currentRevenue - $currentExpenses;
        $lastMonthNetProfit = $lastMonthRevenue - $lastMonthExpenses;

        $revenueGrowth = $lastMonthNetProfit > 0 
            ? round((($currentNetProfit - $lastMonthNetProfit) / abs($lastMonthNetProfit)) * 100, 1)
            : ($currentNetProfit > 0 ? 100 : 0);

        $invoicesThisMonth = Invoice::thisMonth()->count();
        $newInvoicesWeek = Invoice::whereBetween('issued_at', [now()->startOfWeek(), now()])->count();

        $overdueAmount = Invoice::overdue()->sum('total_amount') ?? 0;
        $overdueCount = Invoice::overdue()->count();

        return [
            'total_revenue' => $currentRevenue,
            'total_expenses' => $currentExpenses,
            'net_profit' => $currentNetProfit,
            'revenue_growth' => $revenueGrowth,
            'invoices_count' => $invoicesThisMonth,
            'new_invoices_week' => $newInvoicesWeek,
            'overdue_amount' => max(0, $overdueAmount),
            'overdue_count' => $overdueCount,
        ];
    }

    private function getRecentInvoices()
    {
        return Invoice::with('user')
            ->orderBy('issued_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->user->display_name,
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'status_label' => $invoice->status_label,
                ];
            });
    }

    private function getRecentPayments()
    {
        return Invoice::with('user')
            ->where('status', Invoice::STATUS_PAID)
            ->orderBy('paid_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'client_name' => $invoice->user->display_name,
                    'amount' => $invoice->total_amount,
                    'date' => $invoice->paid_at->format('d/m/Y'),
                ];
            });
    }

    private function getActiveSubscriptions()
    {
        return User::where('role', 'user')
            ->where('is_active', true)
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'member_name' => $user->display_name,
                    'plan_label' => $user->membership_plan_label ?? 'Hot Desk',
                    'amount' => $user->price ?? 0,
                    'billing_cycle' => $user->billing_cycle_label ?? 'Mois',
                    'next_billing_date' => now()->addMonth()->format('d/m/Y'),
                ];
            });
    }

    private function getRecentExpenses()
    {
        return Expense::orderBy('expense_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'date' => $expense->expense_date->format('d/m/Y'),
                    'description' => $expense->title,
                    'category' => $expense->category_label,
                    'amount' => $expense->amount,
                ];
            });
    }

    private function getChartData(): array
    {
        $months = [];
        $revenues = [];
        $expenses = [];

        for ($i = 8; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $date->locale('fr')->format('M');
            
            $monthRevenue = Invoice::where('status', Invoice::STATUS_PAID)
                ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount') ?? 0;
            $revenues[] = round($monthRevenue, 2);

            $monthExpenses = Expense::paid()
                ->whereBetween('expense_date', [$startOfMonth, $endOfMonth])
                ->sum('amount') ?? 0;
            $expenses[] = round($monthExpenses, 2);
        }

        return [
            'months' => $months,
            'revenue' => $revenues,
            'expenses' => $expenses,
            'revenue_distribution' => [30, 25, 35, 10],
        ];
    }

    // ========== INVOICE MANAGEMENT ==========

    public function createInvoice(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount' => 'required|numeric|min:0.01',
                'tax_amount' => 'nullable|numeric|min:0',
                'description' => 'required|string',
                'items' => 'nullable|array',
                'due_days' => 'required|integer|min:1', // Already validates as integer
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'user_id' => $validated['user_id'],
                'amount' => (float) $validated['amount'],
                'tax_amount' => (float) ($validated['tax_amount'] ?? 0),
                'total_amount' => (float) $validated['amount'] + (float) ($validated['tax_amount'] ?? 0),
                'status' => Invoice::STATUS_SENT,
                'issued_at' => now(),
                'due_at' => now()->addDays((int) $validated['due_days']), // Cast to int
                'description' => $validated['description'],
                'items' => $validated['items'] ?? [],
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Facture créée avec succès',
                'data' => ['invoice' => $invoice]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la facture'
            ], 500);
        }
    }

    public function downloadInvoicePDF($id)
    {
        try {
            $invoice = Invoice::with('user')->findOrFail($id);
            
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
            Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération du PDF');
        }
    }
    // Add this method in FinancesController after createInvoice()
    public function updateInvoice(Request $request, $id): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $invoice = Invoice::findOrFail($id);
            $invoice->update([
                'invoice_number' => $request->invoice_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Numéro de facture mis à jour avec succès',
                'data' => ['invoice' => $invoice]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update invoice: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ], 500);
        }
    }

    // ========== DEVIS MANAGEMENT ==========
    public function createDevis(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'devis_number' => 'required|string|max:50',  // MANUAL ENTRY
                'client_name' => 'required|string|max:255',  // MANUAL ENTRY
                'amount' => 'required|numeric|min:0.01',
                'tax_amount' => 'nullable|numeric|min:0',
                'description' => 'required|string',
                'items' => 'nullable|array',
                'valid_days' => 'required|integer|min:1',
                'notes' => 'nullable|string',
                'terms' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $devis = Devis::create([
                'devis_number' => $validated['devis_number'],
                'client_name' => $validated['client_name'],
                'user_id' => null,  // No user association
                'amount' => (float) $validated['amount'],
                'tax_amount' => (float) ($validated['tax_amount'] ?? 0),
                'total_amount' => (float) $validated['amount'] + (float) ($validated['tax_amount'] ?? 0),
                'status' => Devis::STATUS_SENT,
                'issued_at' => now(),
                'valid_until' => now()->addDays((int) $validated['valid_days']),
                'description' => $validated['description'],
                'items' => $validated['items'] ?? [],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Devis créé avec succès',
                'data' => ['devis' => $devis]
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to create devis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

// Update getRecentDevis method
    private function getRecentDevis()
    {
        return Devis::orderBy('issued_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($devis) {
                return [
                    'id' => $devis->id,
                    'devis_number' => $devis->devis_number,
                    'client_name' => $devis->client_name,  // Now from manual entry
                    'amount' => $devis->total_amount,
                    'status' => $devis->status,
                    'status_label' => $devis->status_label,
                    'issued_at' => $devis->issued_at->format('d/m/Y'),
                    'valid_until' => $devis->valid_until->format('d/m/Y'),
                ];
            });
    }

// ADD NEW DELETE METHOD
    public function deleteDevis($id): JsonResponse
    {
        try {
            $devis = Devis::findOrFail($id);
            $devisNumber = $devis->devis_number;
            $devis->delete();

            return response()->json([
                'success' => true,
                'message' => "Devis {$devisNumber} supprimé avec succès"
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete devis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du devis'
            ], 500);
        }
    }
    public function downloadDevisPDF($id)
    {
        try {
            $devis = Devis::findOrFail($id);
            
            $company = [
                'name' => 'LA STATION',
                'address' => '32 Impass Siam, 1er étage, appartement 3, Océan',
                'city' => 'Rabat Rabat-Salé-Zemmour-Zaer',
                'country' => 'Morocco',
                'ice' => '002672845000021',
                'if' => '47284949',
                'rc' => '147923',
            ];

            $pdf = Pdf::loadView('pdf.devis', compact('devis', 'company'));
            return $pdf->download("devis_{$devis->devis_number}.pdf");

        } catch (Exception $e) {
            Log::error('Failed to generate devis PDF: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la génération du PDF');
        }
    }

    

    // ========== EXPENSE MANAGEMENT ==========

   public function createExpense(Request $request): JsonResponse
    {
        try {
            $validator = \Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0.01',
                'category' => ['required', Rule::in(array_keys(Expense::getAvailableCategories()))],
                'vendor' => 'nullable|string|max:255',
                'expense_date' => 'required|date|before_or_equal:today',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            $expense = Expense::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount' => (float) $validated['amount'],
                'category' => $validated['category'],
                'vendor' => $validated['vendor'] ?? null,
                'expense_date' => $validated['expense_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => Expense::STATUS_PAID,
                'created_by' => null, // ← Always null since no auth
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dépense ajoutée avec succès',
                'data' => ['expense' => $expense]
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to create expense: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }


    // ========== AJAX ENDPOINTS ==========

    public function getStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getFinancialStatistics()
        ]);
    }

    public function getChartDataAjax(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getChartData()
        ]);
    }

}