<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiTemplateFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_admin_can_dispatch_installation_bundle_to_three_technicians(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $company = Company::first();
        $customer = Customer::first();
        $site = Site::first();

        $engineers = User::where('role', 'engineer')->get();
        $this->assertGreaterThanOrEqual(3, $engineers->count());

        $tech1 = $engineers[0];
        $tech2 = $engineers[1];
        $tech3 = $engineers[2];

        $response = $this->post(route('admin.services.store'), [
            'dispatch_mode' => 'installation_bundle',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'priority' => 'high',
            'structure_engineer_id' => $tech1->id,
            'electrical_engineer_id' => $tech2->id,
            'commissioning_engineer_id' => $tech3->id,
            'description' => '100 kW Rooftop Commercial Installation (Bundled)',
        ]);

        $response->assertRedirect(route('admin.services.index'));
        $response->assertSessionHas('success');

        // Check 3 services created
        $services = Service::where('customer_id', $customer->id)
            ->where('site_id', $site->id)
            ->where('description', 'like', '%100 kW Rooftop Commercial Installation%')
            ->get();

        $this->assertCount(3, $services);

        $structureService = $services->firstWhere('assigned_user_id', $tech1->id);
        $electricalService = $services->firstWhere('assigned_user_id', $tech2->id);
        $commissioningService = $services->firstWhere('assigned_user_id', $tech3->id);

        $this->assertNotNull($structureService);
        $this->assertNotNull($electricalService);
        $this->assertNotNull($commissioningService);

        $this->assertEquals('installation_structure', $structureService->serviceType->report_template_slug);
        $this->assertEquals('installation_electrical', $electricalService->serviceType->report_template_slug);
        $this->assertEquals('installation_commissioning', $commissioningService->serviceType->report_template_slug);

        // Notifications
        $this->assertTrue(Notification::where('user_id', $tech1->id)->exists());
        $this->assertTrue(Notification::where('user_id', $tech2->id)->exists());
        $this->assertTrue(Notification::where('user_id', $tech3->id)->exists());
    }

    public function test_three_technicians_can_start_and_submit_all_three_installation_parts(): void
    {
        $engineers = User::where('role', 'engineer')->get();
        $tech1 = $engineers[0]; // Structure
        $tech2 = $engineers[1]; // Electrical
        $tech3 = $engineers[2]; // Commissioning

        $company = Company::first();
        $customer = Customer::first();
        $site = Site::first();

        // 1. Structure Part
        $type1 = ServiceType::where('report_template_slug', 'installation_structure')->first();
        $svc1 = Service::create([
            'service_number' => 'SRV-TEST-S1',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $type1->id,
            'assigned_user_id' => $tech1->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        $this->actingAs($tech1);
        $startResp1 = $this->post(route('engineer.services.start', $svc1->id));
        $report1 = Report::where('service_id', $svc1->id)->firstOrFail();
        $startResp1->assertRedirect(route('engineer.reports.edit', $report1->id));
        $this->assertEquals('installation_structure', $report1->template->slug);

        // Edit view loads
        $editResp1 = $this->get(route('engineer.reports.edit', $report1->id));
        $editResp1->assertOk();
        $editResp1->assertSee('Installation Part 1');

        // Submit Structure Report
        $submitResp1 = $this->post(route('engineer.reports.submit', $report1->id), [
            'current_step' => 6,
        ]);
        $submitResp1->assertRedirect(route('engineer.reports.show', $report1->id));
        $report1->refresh();
        $this->assertEquals('submitted', $report1->status);

        // 2. Electrical Part
        $type2 = ServiceType::where('report_template_slug', 'installation_electrical')->first();
        $svc2 = Service::create([
            'service_number' => 'SRV-TEST-E2',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $type2->id,
            'assigned_user_id' => $tech2->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        $this->actingAs($tech2);
        $startResp2 = $this->post(route('engineer.services.start', $svc2->id));
        $report2 = Report::where('service_id', $svc2->id)->firstOrFail();
        $startResp2->assertRedirect(route('engineer.reports.edit', $report2->id));
        $this->assertEquals('installation_electrical', $report2->template->slug);

        $editResp2 = $this->get(route('engineer.reports.edit', $report2->id));
        $editResp2->assertOk();
        $editResp2->assertSee('Installation Part 2');

        // Submit Electrical Report
        $submitResp2 = $this->post(route('engineer.reports.submit', $report2->id), [
            'current_step' => 6,
        ]);
        $submitResp2->assertRedirect(route('engineer.reports.show', $report2->id));
        $report2->refresh();
        $this->assertEquals('submitted', $report2->status);

        // 3. Commissioning Part
        $type3 = ServiceType::where('report_template_slug', 'installation_commissioning')->first();
        $svc3 = Service::create([
            'service_number' => 'SRV-TEST-C3',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $type3->id,
            'assigned_user_id' => $tech3->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:00',
            'status' => 'assigned',
            'priority' => 'high',
        ]);

        $this->actingAs($tech3);
        $startResp3 = $this->post(route('engineer.services.start', $svc3->id));
        $report3 = Report::where('service_id', $svc3->id)->firstOrFail();
        $startResp3->assertRedirect(route('engineer.reports.edit', $report3->id));
        $this->assertEquals('installation_commissioning', $report3->template->slug);

        $editResp3 = $this->get(route('engineer.reports.edit', $report3->id));
        $editResp3->assertOk();
        $editResp3->assertSee('Installation Part 3');

        // Submit Commissioning Report
        $submitResp3 = $this->post(route('engineer.reports.submit', $report3->id), [
            'current_step' => 5,
        ]);
        $submitResp3->assertRedirect(route('engineer.reports.show', $report3->id));
        $report3->refresh();
        $this->assertEquals('submitted', $report3->status);

        // 4. Admin Review & Approval & Print
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Check Structure Review & Print
        $adminView1 = $this->get(route('admin.reports.show', $report1->id));
        $adminView1->assertOk();
        $adminView1->assertSee('Installation: Structure & Mounting', false);

        $printResp1 = $this->get(route('admin.reports.print', $report1->id));
        $printResp1->assertOk();
        $printResp1->assertSee('INSTALLATION: STRUCTURE & MODULES', false);

        $apprResp1 = $this->post(route('admin.reports.approve', $report1->id));
        $apprResp1->assertSessionHas('success');
        $report1->refresh();
        $this->assertEquals('approved', $report1->status);

        // Check Electrical Review & Print
        $adminView2 = $this->get(route('admin.reports.show', $report2->id));
        $adminView2->assertOk();
        $adminView2->assertSee('Installation: Electrical & Cabling', false);

        $printResp2 = $this->get(route('admin.reports.print', $report2->id));
        $printResp2->assertOk();
        $printResp2->assertSee('INSTALLATION: ELECTRICAL & CABLING', false);

        $apprResp2 = $this->post(route('admin.reports.approve', $report2->id));
        $apprResp2->assertSessionHas('success');
        $report2->refresh();
        $this->assertEquals('approved', $report2->status);

        // Check Commissioning Review & Print
        $adminView3 = $this->get(route('admin.reports.show', $report3->id));
        $adminView3->assertOk();
        $adminView3->assertSee('Installation: PCU & Commissioning', false);

        $printResp3 = $this->get(route('admin.reports.print', $report3->id));
        $printResp3->assertOk();
        $printResp3->assertSee('INSTALLATION: PCU & COMMISSIONING', false);

        $apprResp3 = $this->post(route('admin.reports.approve', $report3->id));
        $apprResp3->assertSessionHas('success');
        $report3->refresh();
        $this->assertEquals('approved', $report3->status);
    }

    public function test_standalone_daily_work_report_workflow(): void
    {
        $engineer = User::where('role', 'engineer')->first();
        $this->actingAs($engineer);

        // Create daily report
        $createResp = $this->post(route('engineer.daily-reports.create'));
        $dailyReport = Report::where('engineer_id', $engineer->id)
            ->whereNull('service_id')
            ->latest('id')
            ->firstOrFail();

        $createResp->assertRedirect(route('engineer.reports.edit', $dailyReport->id));
        $this->assertEquals('daily_work_report', $dailyReport->template->slug);

        // Save draft with shift data & hourly log
        $saveDraftResp = $this->postJson(route('engineer.reports.save-draft', $dailyReport->id), [
            'current_step' => 3,
            'sections' => [
                'shift_details' => [
                    'employee_name' => $engineer->name,
                    'designation' => 'Solar Engineer',
                    'report_date' => now()->toDateString(),
                    'work_started_time' => '09:00',
                    'work_stopped_time' => '18:00',
                    'place_of_work' => 'Peenya Industrial Plant',
                ],
                'travel_conveyance' => [
                    'vehicle_used' => 'Bike',
                    'starting_km' => '1200',
                    'ending_km' => '1245',
                    'diff_km' => '45',
                    'rate_per_km' => '4.5',
                    'amount' => '202.50',
                ],
                'meals_allowance' => [
                    'lunch_yes' => true,
                    'lunch_amount' => '150',
                ],
            ],
        ]);
        $saveDraftResp->assertJson(['success' => true]);

        // Submit daily report
        $submitResp = $this->post(route('engineer.reports.submit', $dailyReport->id), [
            'current_step' => 5,
        ]);
        $submitResp->assertRedirect(route('engineer.reports.show', $dailyReport->id));
        $dailyReport->refresh();
        $this->assertEquals('submitted', $dailyReport->status);

        // Admin reviews and approves
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $adminShow = $this->get(route('admin.reports.show', $dailyReport->id));
        $adminShow->assertOk();
        $adminShow->assertSee('Daily Work Timesheet');

        $printResp = $this->get(route('admin.reports.print', $dailyReport->id));
        $printResp->assertOk();
        $printResp->assertSee('DAILY WORK TIMESHEET & CONVEYANCE', false);

        $approveResp = $this->post(route('admin.reports.approve', $dailyReport->id));
        $approveResp->assertSessionHas('success');
        $dailyReport->refresh();
        $this->assertEquals('approved', $dailyReport->status);
    }

    public function test_site_inspection_and_complaint_attending_workflows(): void
    {
        $engineer = User::where('role', 'engineer')->first();
        $company = Company::first();
        $customer = Customer::first();
        $site = Site::first();

        // 1. Site Inspection
        $surveyType = ServiceType::where('report_template_slug', 'site_inspection')->firstOrFail();
        $surveyService = Service::create([
            'service_number' => 'SRV-SURVEY-01',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $surveyType->id,
            'assigned_user_id' => $engineer->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:30',
            'status' => 'assigned',
            'priority' => 'normal',
        ]);

        $this->actingAs($engineer);
        $this->post(route('engineer.services.start', $surveyService->id));
        $surveyReport = Report::where('service_id', $surveyService->id)->firstOrFail();
        $this->assertEquals('site_inspection', $surveyReport->template->slug);

        $this->post(route('engineer.reports.submit', $surveyReport->id), ['current_step' => 6]);
        $surveyReport->refresh();
        $this->assertEquals('submitted', $surveyReport->status);

        // 2. Complaint Attending
        $complaintType = ServiceType::where('report_template_slug', 'complaint_attending')->firstOrFail();
        $complaintService = Service::create([
            'service_number' => 'SRV-COMPLAINT-01',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $complaintType->id,
            'assigned_user_id' => $engineer->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '14:00',
            'status' => 'assigned',
            'priority' => 'urgent',
        ]);

        $this->post(route('engineer.services.start', $complaintService->id));
        $complaintReport = Report::where('service_id', $complaintService->id)->firstOrFail();
        $this->assertEquals('complaint_attending', $complaintReport->template->slug);

        $this->post(route('engineer.reports.submit', $complaintReport->id), ['current_step' => 5]);
        $complaintReport->refresh();
        $this->assertEquals('submitted', $complaintReport->status);

        // Admin Review & Print for both
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // Survey
        $surveyAdmin = $this->get(route('admin.reports.show', $surveyReport->id));
        $surveyAdmin->assertOk();
        $surveyAdmin->assertSee('Site Inspection Report');

        $surveyPrint = $this->get(route('admin.reports.print', $surveyReport->id));
        $surveyPrint->assertOk();
        $surveyPrint->assertSee('SITE INSPECTION SURVEY REPORT');

        // Complaint
        $complaintAdmin = $this->get(route('admin.reports.show', $complaintReport->id));
        $complaintAdmin->assertOk();
        $complaintAdmin->assertSee('Complaint Attending Sheet');

        $complaintPrint = $this->get(route('admin.reports.print', $complaintReport->id));
        $complaintPrint->assertOk();
        $complaintPrint->assertSee('COMPLAINT ATTENDING SHEET');
    }

    public function test_engineer_can_complete_and_submit_customer_feedback_form_and_admin_approve(): void
    {
        $engineer = User::where('role', 'engineer')->first();
        $this->actingAs($engineer);

        $company = Company::first();
        $customer = Customer::first();
        $site = Site::first();

        $feedbackType = ServiceType::where('report_template_slug', 'customer_feedback')->firstOrFail();
        $feedbackService = Service::create([
            'service_number' => 'SRV-FEEDBACK-01',
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $feedbackType->id,
            'assigned_user_id' => $engineer->id,
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:00',
            'status' => 'assigned',
            'priority' => 'medium',
        ]);

        // 1. Start Service & Create Report
        $this->post(route('engineer.services.start', $feedbackService->id));
        $report = Report::where('service_id', $feedbackService->id)->firstOrFail();
        $this->assertEquals('customer_feedback', $report->template->slug);
        $this->assertEquals('draft', $report->status);

        // 2. Engineer Saves Draft with 10-Point Ratings and Signature
        $draftData = [
            'current_step' => 5,
            'sections' => [
                'customer_project_details' => [
                    'customer_name' => $customer->name,
                    'site_address' => $site->address,
                    'contact_person' => $customer->contact_person,
                    'mobile_no' => $customer->phone,
                    'system_capacity' => '10 kW Solar Rooftop',
                    'feedback_date' => now()->toDateString(),
                    'installation_date' => now()->subMonths(1)->toDateString(),
                    'type_of_visit' => 'service',
                ],
                'ratings_experience' => [
                    'rate_quality_work' => 5,
                    'rate_quality_materials' => 5,
                    'rate_professionalism' => 5,
                    'rate_behaviour_communication' => 5,
                    'rate_punctuality' => 4,
                    'rate_cleanliness' => 5,
                    'rate_explanation_operation' => 5,
                    'rate_explanation_safety' => 4,
                    'rate_response_questions' => 5,
                    'rate_overall_satisfaction' => 5,
                ],
                'solar_system_feedback' => [
                    'work_satisfactory' => 'YES',
                    'performing_as_explained' => 'YES',
                    'components_explained' => 'YES',
                    'safety_explained' => 'YES',
                    'maintenance_explained' => 'YES',
                ],
                'service_complaint_feedback' => [
                    'concern_understood' => 'YES',
                    'response_time_satisfactory' => 'YES',
                    'issue_rectified' => 'YES',
                    'solution_explained' => 'YES',
                    'system_functioning' => 'YES',
                    'work_pending' => 'NO',
                ],
                'comments_suggestions' => [
                    'what_done_well' => 'Very polite team, neat conduit installation',
                    'what_to_improve' => 'Faster inverter WiFi pairing',
                    'other_feedback' => 'Keep up the great work',
                    'pending_issue_details' => 'None',
                ],
                'overall_recommendation' => [
                    'overall_experience' => 5,
                    'recommend_services' => 'YES',
                    'contact_for_testimonial' => 'YES',
                ],
                'customer_confirmation' => [
                    'client_confirmed' => true,
                    'customer_name' => $customer->name,
                    'contact_no' => $customer->phone,
                    'feedback_date' => now()->toDateString(),
                    'client_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                ],
            ],
        ];

        $draftResponse = $this->postJson(route('engineer.reports.save-draft', $report->id), $draftData);
        $draftResponse->assertOk();
        $draftResponse->assertJson(['success' => true]);

        // 3. Engineer Submits Report
        $submitResponse = $this->post(route('engineer.reports.submit', $report->id), ['current_step' => 5]);
        $submitResponse->assertRedirect(route('engineer.reports.show', $report->id));

        $report->refresh();
        $this->assertEquals('submitted', $report->status);

        // Engineer Show View
        $engShow = $this->get(route('engineer.reports.show', $report->id));
        $engShow->assertOk();
        $engShow->assertSee('Customer Feedback', false);
        $engShow->assertSee('Quality of installation / work');

        // 4. Admin Verification & Approval
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $adminShow = $this->get(route('admin.reports.show', $report->id));
        $adminShow->assertOk();
        $adminShow->assertSee('Customer Feedback', false);
        $adminShow->assertSee('Very polite team, neat conduit installation');

        $adminPrint = $this->get(route('admin.reports.print', $report->id));
        $adminPrint->assertOk();
        $adminPrint->assertSee('CUSTOMER FEEDBACK', false);
        $adminPrint->assertSee('SUN ON EARTH SOLAR TECHNOLOGIES', false);

        // Approve
        $approveResponse = $this->post(route('admin.reports.approve', $report->id));
        $approveResponse->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('approved', $report->status);
        $this->assertEquals('completed', $report->service->status);
    }
}
