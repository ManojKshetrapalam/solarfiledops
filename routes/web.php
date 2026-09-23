<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Engineer\DashboardController as EngineerDashboardController;
use App\Http\Controllers\Engineer\NotificationController as EngineerNotificationController;
use App\Http\Controllers\Engineer\ReportController as EngineerReportController;
use App\Http\Controllers\Engineer\ServiceController as EngineerServiceController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes (Role: admin)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Companies
    Route::resource('companies', CompanyController::class)->except(['show', 'destroy']);
    Route::post('/companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');

    // Employees
    Route::resource('employees', EmployeeController::class)->except(['destroy']);
    Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
    Route::post('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

    // Customers & Sites
    Route::resource('customers', CustomerController::class)->except(['destroy']);
    Route::resource('sites', SiteController::class)->except(['destroy']);

    // Services
    Route::resource('services', AdminServiceController::class)->except(['destroy', 'edit', 'update']);
    Route::post('/services/{service}/assign', [AdminServiceController::class, 'assign'])->name('services.assign');
    Route::post('/services/{service}/status', [AdminServiceController::class, 'updateStatus'])->name('services.update-status');

    // Reports Review & Verification
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [AdminReportController::class, 'show'])->name('reports.show');
    Route::post('/reports/{report}/approve', [AdminReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/request-correction', [AdminReportController::class, 'requestCorrection'])->name('reports.request-correction');
    Route::post('/reports/{report}/reject', [AdminReportController::class, 'reject'])->name('reports.reject');
    Route::get('/reports/{report}/print', [AdminReportController::class, 'printView'])->name('reports.print');

    // Analytics & Operational Reporting
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');
});

// Mobile Field Engineer Routes (Role: engineer or active staff)
Route::prefix('engineer')->name('engineer.')->middleware(['auth', 'engineer'])->group(function () {
    Route::get('/dashboard', [EngineerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [EngineerDashboardController::class, 'profile'])->name('profile');

    // Services
    Route::get('/services', [EngineerServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [EngineerServiceController::class, 'show'])->name('services.show');
    Route::post('/services/{service}/start', [EngineerServiceController::class, 'startService'])->name('services.start');

    // 10-Step Service Report
    Route::get('/reports', [EngineerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [EngineerReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/edit', [EngineerReportController::class, 'edit'])->name('reports.edit');
    Route::post('/reports/{report}/save-draft', [EngineerReportController::class, 'saveDraft'])->name('reports.save-draft');
    Route::post('/reports/{report}/upload-photo', [EngineerReportController::class, 'uploadPhoto'])->name('reports.upload-photo');
    Route::delete('/reports/photos/{photo}', [EngineerReportController::class, 'deletePhoto'])->name('reports.delete-photo');
    Route::post('/reports/{report}/upload-document', [EngineerReportController::class, 'uploadDocument'])->name('reports.upload-document');
    Route::post('/reports/{report}/submit', [EngineerReportController::class, 'submit'])->name('reports.submit');

    // Notifications
    Route::get('/notifications', [EngineerNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [EngineerNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [EngineerNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});
