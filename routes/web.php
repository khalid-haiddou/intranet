<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\PollsController;
use App\Http\Controllers\Admin\SpacesController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Admin\FinancesController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Dashboard Routes (for now without middleware)
Route::group(['prefix' => 'dashboard'], function () {
    
    // Members Management Routes
    Route::get('/members', [MembersController::class, 'index'])->name('dashboard.members');
    Route::get('/members/stats', [MembersController::class, 'getStats'])->name('members.stats');
    Route::get('/members/export', [MembersController::class, 'export'])->name('members.export');
    
    // Individual Member Routes
    Route::get('/members/{user}', [MembersController::class, 'show'])->name('members.show');
    Route::put('/members/{user}', [MembersController::class, 'update'])->name('members.update');
    Route::post('/members/{user}/toggle-status', [MembersController::class, 'toggleStatus'])->name('members.toggle-status');
    Route::post('/members/{user}/approve', [MembersController::class, 'approve'])->name('members.approve');
    Route::post('/members/{user}/reject', [MembersController::class, 'reject'])->name('members.reject');
    
    // Registration Stats (from existing RegisterController)
    Route::get('/registration/stats', [RegisterController::class, 'getRegistrationStats'])->name('registration.stats');
    Route::put('/users/{user}/role', [RegisterController::class, 'updateUserRole'])->name('users.update-role');
    Route::post('/users/{user}/toggle-status', [RegisterController::class, 'toggleUserStatus'])->name('users.toggle-status');
    
});


// Polls Management Routes
Route::prefix('dashboard/sondages')->group(function () {
    // Polls Management Routes
    Route::get('/', [PollsController::class, 'index'])->name('dashboard.polls');
    Route::post('/', [PollsController::class, 'store'])->name('polls.store');
    Route::get('/stats', [PollsController::class, 'getStats'])->name('polls.stats');
    Route::get('/templates', [PollsController::class, 'getTemplates'])->name('polls.templates');

    // Individual Poll Routes
    Route::get('/{poll}', [PollsController::class, 'show'])->name('polls.show');
    Route::put('/{poll}', [PollsController::class, 'update'])->name('polls.update');
    Route::delete('/{poll}', [PollsController::class, 'destroy'])->name('polls.destroy');
    Route::post('/{poll}/publish', [PollsController::class, 'publish'])->name('polls.publish');
    Route::post('/{poll}/end', [PollsController::class, 'end'])->name('polls.end');
    Route::get('/{poll}/export', [PollsController::class, 'export'])->name('polls.export');
});


// Spaces Management & Operations Routes
Route::prefix('dashboard/espaces')->group(function () {
    // Spaces Management
    Route::get('/', [SpacesController::class, 'index'])->name('dashboard.spaces');
    Route::post('/', [SpacesController::class, 'store'])->name('spaces.store');
    Route::get('/stats', [SpacesController::class, 'getStats'])->name('spaces.stats');
    Route::get('/dashboard', [SpacesController::class, 'getDashboard'])->name('spaces.dashboard');

    // Individual Space
    Route::get('/{space}', [SpacesController::class, 'show'])->name('spaces.show');
    Route::put('/{space}', [SpacesController::class, 'update'])->name('spaces.update');
    Route::get('/{space}/availability', [SpacesController::class, 'getAvailability'])->name('spaces.availability');

    // Reservations
    Route::post('/{space}/reservations', [SpacesController::class, 'createReservation'])->name('spaces.reservations.create');

    // Maintenance
    Route::post('/{space}/maintenance', [SpacesController::class, 'scheduleMaintenance'])->name('spaces.maintenance.schedule');
});

    //events
Route::prefix('dashboard')->group(function () {
    // Events page
    Route::get('/evenements', [EventController::class, 'index'])->name('admin.evenements');
    
    // API routes for events
    Route::prefix('api/events')->group(function () {
        Route::get('/', [EventController::class, 'getEvents'])->name('api.events.index');
        Route::post('/', [EventController::class, 'store'])->name('api.events.store');
        Route::put('/{event}', [EventController::class, 'update'])->name('api.events.update');
        Route::delete('/{event}', [EventController::class, 'destroy'])->name('api.events.destroy');
        
        // Participation routes
        Route::post('/{event}/participate', [EventController::class, 'participate'])->name('api.events.participate');
        Route::delete('/{event}/participate', [EventController::class, 'cancelParticipation'])->name('api.events.cancel-participation');
        
        // Stats and calendar
        Route::get('/stats', [EventController::class, 'getStats'])->name('api.events.stats');
        Route::get('/calendar', [EventController::class, 'getCalendarEvents'])->name('api.events.calendar');
    });
});

// Dashboardroutes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [AdminDashboardController::class, 'getData'])->name('dashboard.data');
    Route::get('/dashboard/refresh', [AdminDashboardController::class, 'refresh'])->name('dashboard.refresh');
});
//finances
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Main finances page
    Route::get('/finances', [FinancesController::class, 'index'])->name('finances');
    
    // Stats and chart data (AJAX)
    Route::get('/finances/stats', [FinancesController::class, 'getStats'])->name('finances.stats');
    Route::get('/finances/chart-data', [FinancesController::class, 'getChartDataAjax'])->name('finances.chart-data');
    
    // Invoice management
    Route::post('/finances/invoices', [FinancesController::class, 'createInvoice'])->name('finances.invoices.create');
    Route::get('/finances/invoices/{id}/pdf', [FinancesController::class, 'downloadInvoicePDF'])->name('finances.invoices.pdf');
    
    // Devis management
    
    
});

Route::post('/admin/finances/expenses', [FinancesController::class, 'createExpense'])->name('admin.finances.expenses.store');
Route::post('/admin/finances/devis', [FinancesController::class, 'createDevis'])->name('admin.finances.devis.store');
Route::get('/admin/finances/devis/{id}/pdf', [FinancesController::class, 'downloadDevisPDF'])->name('admin.finances.devis.pdf');
Route::delete('/admin/finances/devis/{id}', [FinancesController::class, 'deleteDevis'])->name('admin.finances.devis.delete');
Route::put('/admin/finances/invoices/{id}', [FinancesController::class, 'updateInvoice'])->name('admin.finances.invoices.update');
// Redirect root to login for now
Route::get('/', function () {
    return redirect()->route('login');
});




