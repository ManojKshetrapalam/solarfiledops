<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContentManagerController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DataManagementController;
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

// Authenticated Password Change
Route::middleware('auth')->group(function () {
    Route::get('/auth/change-password', [AuthController::class, 'showChangePasswordForm'])->name('auth.change-password');
    Route::post('/auth/change-password', [AuthController::class, 'updatePassword'])->name('auth.change-password.update');
});

// Admin Routes (Role: super_admin, admin, admin_operations)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/setup-wizard', [AuthController::class, 'setupWizard'])->name('setup-wizard');

    // Companies: index is accessible by both Super Admin and Admin Operations (read-only company info)
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');

    // Employees / Technicians: index is viewable by both (operational assignment & list of staff)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');

    // Super Admin Only: Company creation/editing, Employee creation/management, Data Imports, Audit Logs
    Route::middleware('super_admin')->group(function () {
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::patch('/companies/{company}', [CompanyController::class, 'update']);
        Route::post('/companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');

        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::patch('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');
        Route::post('/employees/{employee}/regenerate-temp-password', [EmployeeController::class, 'regenerateTemporaryPassword'])->name('employees.regenerate-temp-password');
        Route::post('/employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs');

        // Data Management & Migration (System settings / Excel migrations)
        Route::prefix('data-management')->name('data-management.')->group(function () {
            Route::get('/', [DataManagementController::class, 'index'])->name('index');
            Route::get('/templates', [DataManagementController::class, 'templates'])->name('templates');
            Route::get('/templates/all', [DataManagementController::class, 'downloadAllTemplates'])->name('download-all-templates');
            Route::get('/templates/{type}', [DataManagementController::class, 'downloadTemplate'])->name('download-template');
            Route::post('/preview', [DataManagementController::class, 'preview'])->name('preview');
            Route::post('/confirm', [DataManagementController::class, 'confirm'])->name('confirm');
            Route::get('/error-report/{import?}', [DataManagementController::class, 'downloadErrorReport'])->name('download-error-report');
            Route::get('/credential-sheet/{import?}', [DataManagementController::class, 'downloadCredentialSheet'])->name('download-credential-sheet');
            Route::get('/history', [DataManagementController::class, 'history'])->name('history');
            Route::get('/history/{import}', [DataManagementController::class, 'showHistory'])->name('history.show');
            Route::post('/audit-copy-credential', [DataManagementController::class, 'auditCopyCredential'])->name('audit-copy-credential');
            Route::get('/reveal-credential/{employee}', [DataManagementController::class, 'revealCredential'])->name('reveal-credential');
        });
    });

    // Employee show (accessible by both Super Admin and Admin Operations after 'create' route)
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    // Customers & Sites
    Route::resource('customers', CustomerController::class)->except(['destroy']);
    Route::resource('sites', SiteController::class)->except(['destroy']);

    // Services (dispatch & assign)
    Route::resource('services', AdminServiceController::class)->except(['destroy', 'edit', 'update']);
    Route::post('/services/{service}/assign', [AdminServiceController::class, 'assign'])->name('services.assign');
    Route::post('/services/{service}/status', [AdminServiceController::class, 'updateStatus'])->name('services.update-status');

    // Reports Review & Verification (approve, reject, request-correction, print)
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

    // Frontend Content Manager (CMS)
    Route::prefix('content-manager')->name('content-manager.')->middleware(['content_manager'])->group(function () {
        Route::get('/', [ContentManagerController::class, 'index'])->name('index');
        Route::patch('/{content}', [ContentManagerController::class, 'update'])->name('update');
        Route::post('/{content}/publish', [ContentManagerController::class, 'publish'])->name('publish');
        Route::post('/publish-all', [ContentManagerController::class, 'publishAll'])->name('publish-all');
        Route::post('/{content}/reset', [ContentManagerController::class, 'reset'])->name('reset');
        Route::get('/{content}/history', [ContentManagerController::class, 'history'])->name('history');
        Route::post('/toggle-preview', [ContentManagerController::class, 'togglePreview'])->name('toggle-preview');
        Route::post('/create-override', [ContentManagerController::class, 'createCompanyOverride'])->name('create-override');
    });
});

// Client-side API for Frontend Content Dictionary
Route::get('/api/frontend-content', [\App\Http\Controllers\Api\FrontendContentApiController::class, 'index'])->name('api.frontend-content');

// Mobile Field Engineer Routes (Role: engineer or active staff)
Route::prefix('engineer')->name('engineer.')->middleware(['auth', 'engineer'])->group(function () {
    Route::get('/dashboard', [EngineerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [EngineerDashboardController::class, 'profile'])->name('profile');

    // Services
    Route::get('/services', [EngineerServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [EngineerServiceController::class, 'show'])->name('services.show');
    Route::post('/services/{service}/start', [EngineerServiceController::class, 'startService'])->name('services.start');

    // Reports & Work Logs
    Route::get('/reports', [EngineerReportController::class, 'index'])->name('reports.index');
    Route::get('/daily-reports', [EngineerReportController::class, 'dailyReportsIndex'])->name('daily-reports.index');
    Route::match(['get', 'post'], '/daily-reports/create', [EngineerReportController::class, 'createDailyReport'])->name('daily-reports.create');
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
