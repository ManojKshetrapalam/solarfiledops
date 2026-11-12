<?php

namespace Database\Seeders;

use App\Models\FrontendContent;
use App\Services\FrontendContentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FrontendContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            // 1. Application Branding
            ['key' => 'branding.app_name', 'category' => 'branding', 'default_value' => 'Sun on Earth Solar Group'],
            ['key' => 'branding.legal_name', 'category' => 'branding', 'default_value' => 'Sun on Earth Solar Solutions Pvt Ltd'],
            ['key' => 'branding.tagline', 'category' => 'branding', 'default_value' => 'Field Operations & Service Management'],
            ['key' => 'branding.footer_text', 'category' => 'branding', 'default_value' => 'Sun on Earth Solar Solutions Pvt Ltd • Solar Field Management System'],

            // 2. Dashboard
            ['key' => 'dashboard.title', 'category' => 'dashboard', 'default_value' => 'Operations Dashboard'],
            ['key' => 'dashboard.stats_active_jobs', 'category' => 'dashboard', 'default_value' => 'Active Jobs'],
            ['key' => 'dashboard.stats_pending_verification', 'category' => 'dashboard', 'default_value' => 'Pending Verification'],
            ['key' => 'dashboard.stats_completed_services', 'category' => 'dashboard', 'default_value' => 'Completed Services'],
            ['key' => 'dashboard.recent_activity', 'category' => 'dashboard', 'default_value' => 'Recent Operational Activity'],

            // 3. Navigation and Menus
            ['key' => 'navigation.dashboard', 'category' => 'navigation', 'default_value' => 'Dashboard'],
            ['key' => 'navigation.companies', 'category' => 'navigation', 'default_value' => 'Companies'],
            ['key' => 'navigation.technicians', 'category' => 'navigation', 'default_value' => 'Engineers / Technicians'],
            ['key' => 'navigation.customers', 'category' => 'navigation', 'default_value' => 'Customers'],
            ['key' => 'navigation.systems', 'category' => 'navigation', 'default_value' => 'Solar Plants / Systems'],
            ['key' => 'navigation.services', 'category' => 'navigation', 'default_value' => 'Service & Maintenance'],
            ['key' => 'navigation.reports', 'category' => 'navigation', 'default_value' => 'Maintenance & Service Reports'],
            ['key' => 'navigation.analytics', 'category' => 'navigation', 'default_value' => 'Reporting & Analytics'],
            ['key' => 'navigation.notifications', 'category' => 'navigation', 'default_value' => 'Notifications'],
            ['key' => 'navigation.audit_logs', 'category' => 'navigation', 'default_value' => 'Audit Trail'],
            ['key' => 'navigation.data_management', 'category' => 'navigation', 'default_value' => 'Import Data'],
            ['key' => 'navigation.content_manager', 'category' => 'navigation', 'default_value' => 'Frontend Content Manager'],

            // 4. Module Names
            ['key' => 'modules.service_operations', 'category' => 'modules', 'default_value' => 'Service Operations & Maintenance'],
            ['key' => 'modules.insights_controls', 'category' => 'modules', 'default_value' => 'Insights & Controls'],
            ['key' => 'modules.governance', 'category' => 'modules', 'default_value' => 'Administration & Governance'],
            ['key' => 'modules.daily_work', 'category' => 'modules', 'default_value' => 'Daily Work Report'],
            ['key' => 'modules.customer_feedback', 'category' => 'modules', 'default_value' => 'Customer Feedback'],
            ['key' => 'modules.installation', 'category' => 'modules', 'default_value' => 'Installation & Commissioning'],

            // 5. Page Headings
            ['key' => 'headings.services_index', 'category' => 'headings', 'default_value' => 'Service Operations & Maintenance'],
            ['key' => 'headings.services_create', 'category' => 'headings', 'default_value' => 'Dispatch New Service / Job'],
            ['key' => 'headings.reports_index', 'category' => 'headings', 'default_value' => 'Maintenance & Service Reports'],
            ['key' => 'headings.submitted_reports', 'category' => 'headings', 'default_value' => 'Submitted Reports'],
            ['key' => 'headings.companies_index', 'category' => 'headings', 'default_value' => 'Companies'],
            ['key' => 'headings.employees_index', 'category' => 'headings', 'default_value' => 'Field Engineers & Technicians'],
            ['key' => 'headings.customers_index', 'category' => 'headings', 'default_value' => 'Customers'],
            ['key' => 'headings.customers_subtitle', 'category' => 'headings', 'default_value' => 'Domestic / Non-domestic & Institutions'],

            // 6. Form Labels
            ['key' => 'form_labels.company', 'category' => 'form_labels', 'default_value' => 'Company'],
            ['key' => 'form_labels.customer', 'category' => 'form_labels', 'default_value' => 'Customer'],
            ['key' => 'form_labels.site', 'category' => 'form_labels', 'default_value' => 'Site / Solar Plant'],
            ['key' => 'form_labels.service_type', 'category' => 'form_labels', 'default_value' => 'Service / Maintenance Type'],
            ['key' => 'form_labels.assigned_engineer', 'category' => 'form_labels', 'default_value' => 'Assigned Engineers / Technicians'],
            ['key' => 'form_labels.scheduled_date', 'category' => 'form_labels', 'default_value' => 'Scheduled Date'],
            ['key' => 'form_labels.priority', 'category' => 'form_labels', 'default_value' => 'Priority'],
            ['key' => 'form_labels.description', 'category' => 'form_labels', 'default_value' => 'Description / Instructions'],

            // 7. Form Placeholders
            ['key' => 'form_placeholders.search_services', 'category' => 'form_placeholders', 'default_value' => 'Search by Job #, customer, or site...'],
            ['key' => 'form_placeholders.search_companies', 'category' => 'form_placeholders', 'default_value' => 'Search companies...'],
            ['key' => 'form_placeholders.search_employees', 'category' => 'form_placeholders', 'default_value' => 'Search by name, email, employee code...'],
            ['key' => 'form_placeholders.search_customers', 'category' => 'form_placeholders', 'default_value' => 'Search by name, phone, email...'],
            ['key' => 'form_placeholders.service_notes', 'category' => 'form_placeholders', 'default_value' => 'Add operational instructions or notes for the technician...'],

            // 8. Buttons and Actions
            ['key' => 'buttons.create_service', 'category' => 'buttons', 'default_value' => '+ Create Service/ Maintainance'],
            ['key' => 'buttons.create_company', 'category' => 'buttons', 'default_value' => '+ Add Company'],
            ['key' => 'buttons.create_employee', 'category' => 'buttons', 'default_value' => '+ Add Engineer / Staff'],
            ['key' => 'buttons.approve', 'category' => 'buttons', 'default_value' => 'Approve'],
            ['key' => 'buttons.reject', 'category' => 'buttons', 'default_value' => 'Reject'],
            ['key' => 'buttons.request_correction', 'category' => 'buttons', 'default_value' => 'Request Correction'],
            ['key' => 'buttons.save_changes', 'category' => 'buttons', 'default_value' => 'Save Changes'],
            ['key' => 'buttons.publish', 'category' => 'buttons', 'default_value' => 'Publish Changes'],
            ['key' => 'buttons.reset_default', 'category' => 'buttons', 'default_value' => 'Reset to Default'],
            ['key' => 'buttons.print', 'category' => 'buttons', 'default_value' => 'Print / Download PDF'],

            // 9. Notifications and Messages
            ['key' => 'notifications.title', 'category' => 'notifications', 'default_value' => 'System Notifications'],
            ['key' => 'notifications.mark_all_read', 'category' => 'notifications', 'default_value' => 'Mark all as read'],
            ['key' => 'notifications.no_notifications', 'category' => 'notifications', 'default_value' => 'No notifications found'],
            ['key' => 'notifications.report_submitted', 'category' => 'notifications', 'default_value' => 'A field report has been submitted for verification'],

            // 10. PDF and Printable Reports
            ['key' => 'reports_print.verified_by', 'category' => 'reports_print', 'default_value' => 'Verified by'],
            ['key' => 'reports_print.admin_operations', 'category' => 'reports_print', 'default_value' => 'Admin Operations'],
            ['key' => 'reports_print.service_engineer', 'category' => 'reports_print', 'default_value' => 'Service Engineer / Technician'],
            ['key' => 'reports_print.commissioned_by', 'category' => 'reports_print', 'default_value' => 'Commissioned By'],
            ['key' => 'reports_print.client_signature', 'category' => 'reports_print', 'default_value' => 'Client Signature & Seal'],
            ['key' => 'reports_print.job_number', 'category' => 'reports_print', 'default_value' => 'Job #'],
            ['key' => 'reports_print.customer_contact', 'category' => 'reports_print', 'default_value' => 'Customer Contact (Authority)'],
            ['key' => 'reports_print.authorised_incharge', 'category' => 'reports_print', 'default_value' => 'Authorised / Incharge'],

            // 11. Login and Authentication Screens
            ['key' => 'auth.welcome_title', 'category' => 'auth_screens', 'default_value' => 'Welcome to Sun on Earth Solar Group'],
            ['key' => 'auth.login_subtitle', 'category' => 'auth_screens', 'default_value' => 'Sign in to access your field management workspace'],
            ['key' => 'auth.login_button', 'category' => 'auth_screens', 'default_value' => 'Sign In'],
            ['key' => 'auth.first_login_prompt', 'category' => 'auth_screens', 'default_value' => 'For security, you must change your temporary password before continuing.'],
            ['key' => 'auth.password_change_button', 'category' => 'auth_screens', 'default_value' => 'Update Password & Continue'],

            // 12. Mobile Interface Labels
            ['key' => 'mobile.home', 'category' => 'mobile_labels', 'default_value' => 'Home'],
            ['key' => 'mobile.jobs', 'category' => 'mobile_labels', 'default_value' => 'Jobs'],
            ['key' => 'mobile.timesheet', 'category' => 'mobile_labels', 'default_value' => 'Timesheet'],
            ['key' => 'mobile.reports', 'category' => 'mobile_labels', 'default_value' => 'Reports'],
            ['key' => 'mobile.profile', 'category' => 'mobile_labels', 'default_value' => 'Profile'],
            ['key' => 'mobile.offline_banner', 'category' => 'mobile_labels', 'default_value' => 'Offline mode. Draft changes are stored locally on your device.'],
        ];

        foreach ($contents as $data) {
            FrontendContent::firstOrCreate(
                [
                    'key' => $data['key'],
                    'company_id' => null,
                ],
                [
                    'category' => $data['category'],
                    'default_value' => $data['default_value'],
                    'published_value' => $data['default_value'],
                    'custom_value' => null,
                    'published_at' => now(),
                ]
            );
        }

        FrontendContentService::clearAllCaches();
    }
}
