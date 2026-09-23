<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportPhoto;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SolarFieldOperationsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_complete_solar_field_operations_25_point_acceptance_workflow(): void
    {
        // 1. Admin logs in.
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // 2. Admin creates "Sun on Earth" (or verifies existing entity).
        $compResponse = $this->post(route('admin.companies.store'), [
            'name' => 'Sun on Earth Solutions',
            'code' => 'SOES',
            'contact_person' => 'Managing Director',
            'phone' => '+91 98888 77777',
            'email' => 'contact@soes.in',
            'address' => 'Electronic City Phase 1, Bangalore',
            'report_header_info' => 'Sun on Earth Solutions Pvt Ltd | ISO 9001:2015',
        ]);
        $compResponse->assertRedirect(route('admin.companies.index'));
        $soeCompany = Company::where('code', 'SOES')->firstOrFail();

        // 3. Admin creates an employee.
        $empResponse = $this->post(route('admin.employees.store'), [
            'name' => 'Raj Kumar Test',
            'email' => 'rajtest@solar.local',
            'password' => 'password123',
            'employee_code' => 'ENG-999',
            'phone' => '+91 99999 00001',
            'designation' => 'Senior Solar Field Engineer',
            'company_id' => $soeCompany->id,
            'role' => 'engineer',
            'joining_date' => '2026-01-01',
        ]);
        $empResponse->assertRedirect(route('admin.employees.index'));
        $engineer = User::where('email', 'rajtest@solar.local')->firstOrFail();

        // 4. Admin creates a customer.
        $custResponse = $this->post(route('admin.customers.store'), [
            'company_id' => $soeCompany->id,
            'name' => 'ABC Industries Test',
            'contact_person' => 'Ramesh Sharma',
            'phone' => '+91 94444 11111',
            'email' => 'ramesh@abctest.com',
            'address' => 'Plot 14, Peenya Industrial Area, Bangalore',
        ]);
        $custResponse->assertRedirect(route('admin.customers.index'));
        $customer = Customer::where('name', 'ABC Industries Test')->firstOrFail();

        // 5. Admin creates a site.
        $siteResponse = $this->post(route('admin.sites.store'), [
            'customer_id' => $customer->id,
            'name' => 'Bangalore Plant Site',
            'address' => 'Plot 14, Peenya Phase 2, Bangalore',
            'contact_person' => 'Suresh Plant Incharge',
            'phone' => '+91 94444 22222',
            'location_notes' => 'Main Production Shed Roof',
        ]);
        $site = Site::where('name', 'Bangalore Plant Site')->firstOrFail();

        // 6. Admin creates a service & 7. assigns to the employee.
        $serviceType = ServiceType::first();
        $srvResponse = $this->post(route('admin.services.store'), [
            'company_id' => $soeCompany->id,
            'customer_id' => $customer->id,
            'site_id' => $site->id,
            'service_type_id' => $serviceType->id,
            'priority' => 'normal',
            'scheduled_date' => '2026-09-23',
            'description' => 'Solar plant inspection and maintenance. Check modules, inverter readings, structure stability and battery condition.',
            'assigned_user_id' => $engineer->id,
        ]);
        $service = Service::where('customer_id', $customer->id)->firstOrFail();
        $this->assertEquals('assigned', $service->status);
        $this->assertEquals($engineer->id, $service->assigned_user_id);

        // Verify engineer notification dispatched
        $this->assertDatabaseHas('notifications', [
            'user_id' => $engineer->id,
            'type' => 'service_assigned',
        ]);

        // 8. Employee logs in.
        $this->actingAs($engineer);

        // 9. Employee sees the assigned service on mobile dashboard.
        $dashResponse = $this->get(route('engineer.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee($service->service_number);
        $dashResponse->assertSee('ABC Industries Test');

        // 10. Employee opens the service and starts work.
        $startResponse = $this->post(route('engineer.services.start', $service->id));
        $service->refresh();
        $this->assertEquals('in_progress', $service->status);
        $report = Report::where('service_id', $service->id)->firstOrFail();
        $this->assertEquals('draft', $report->status);

        // 11. Employee fills the Service Report (10-Step wizard) & 13. saves draft.
        $saveDraftResponse = $this->postJson(route('engineer.reports.save-draft', $report->id), [
            'current_step' => 4,
            'sections' => [
                'customer_details' => [
                    'customer_name' => $customer->name,
                    'customer_address' => $site->address,
                    'service_date' => '2026-09-23',
                    'service_time' => '14:30',
                    'phone_head' => '+91 94444 11111',
                    'phone_incharge' => '+91 94444 22222',
                    'phone_maintenance' => '+91 94444 33333',
                    'phone_others' => '',
                ],
                'system_details' => [
                    'system_capacity' => '100 kW',
                    'date_of_installation' => '2023-05-15',
                    'plant_type' => 'grid_tied',
                ],
                'module_inspection' => [
                    'condition' => 'good',
                    'meter_amps' => '18.4 A',
                    'meter_amps_time' => '14:35',
                    'meter_volts' => '415 V',
                    'meter_volts_time' => '14:36',
                    'remarks' => 'Modules clean, minimal dust',
                ],
                'structure_inspection' => [
                    'condition' => 'stable_rigid',
                    'materials_used' => 'Hot Dip Galvanized Steel',
                    'coating_condition' => 'good_galvanized',
                    'remarks' => 'Torque markings intact',
                ],
                'pcu_inspection' => [
                    'capacity' => '100 kVA',
                    'phase' => 'three_phase',
                    'voltage_phase_1' => '238',
                    'voltage_phase_2' => '239',
                    'voltage_phase_3' => '237',
                    'current' => '41 A',
                    'condition' => 'normal_operational',
                    'solar_readings' => '482 kWh',
                    'array_voltage' => '640 V',
                    'battery_voltage' => '54 V',
                    'remarks' => 'Inverter running normally without derating',
                ],
                'battery_inspection' => [
                    'battery_capacity' => '200 Ah',
                    'number_of_batteries' => '16',
                    'battery_voltage' => '52.4',
                    'distilled_water_before' => 'Low',
                    'distilled_water_after' => 'Full (5L)',
                    'battery_connectors' => 'good_greased',
                    'battery_stand_condition' => 'stable',
                    'remarks' => 'Topped up all 16 cells',
                ],
                'complaint_details' => [
                    'complaint_details' => '',
                    'rectified_report_detailed' => '',
                ],
                'remarks' => [
                    'general_remarks' => 'Plant performing at 98% expected yield.',
                    'checked_by_name' => 'Suresh Plant Incharge',
                    'checked_by_phone' => '+91 94444 22222',
                    'checked_by_notes' => 'Work completed satisfactorily.',
                ],
            ],
        ]);
        $saveDraftResponse->assertJson(['success' => true]);

        // 12. Employee uploads photographs.
        $fakePhoto = UploadedFile::fake()->image('cleaning_evidence.jpg', 800, 600);
        $photoUploadResponse = $this->postJson(route('engineer.reports.upload-photo', $report->id), [
            'photo' => $fakePhoto,
            'section_key' => 'module_inspection',
            'photo_type' => 'cleaning_photo',
            'caption' => 'Solar panels cleaned during maintenance',
        ]);
        $photoUploadResponse->assertJson(['success' => true]);
        $this->assertDatabaseHas('report_photos', [
            'report_id' => $report->id,
            'photo_type' => 'cleaning_photo',
        ]);

        // 14. Employee reopens draft.
        $editReportView = $this->get(route('engineer.reports.edit', $report->id));
        $editReportView->assertStatus(200);
        $editReportView->assertSee('100 kW');
        $editReportView->assertSee('cleaning_evidence.jpg');

        // 15. Employee submits report.
        $submitResponse = $this->post(route('engineer.reports.submit', $report->id));
        $submitResponse->assertRedirect(route('engineer.reports.show', $report->id));
        $report->refresh();
        $service->refresh();
        $this->assertEquals('submitted', $report->status);
        $this->assertEquals('report_submitted', $service->status);

        // 16. Admin receives notification.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'type' => 'report_submitted',
        ]);

        // 17. Admin opens report review.
        $this->actingAs($admin);
        $adminReviewView = $this->get(route('admin.reports.show', $report->id));
        $adminReviewView->assertStatus(200);
        $adminReviewView->assertSee('18.4 A');
        $adminReviewView->assertSee('cleaning_evidence.jpg');

        // 18. Admin requests correction with mandatory reason.
        $corrResponse = $this->post(route('admin.reports.request-correction', $report->id), [
            'correction_notes' => 'Please upload the battery cleaning photograph and enter the battery voltage.',
        ]);
        $report->refresh();
        $service->refresh();
        $this->assertEquals('correction_required', $report->status);
        $this->assertEquals('correction_required', $service->status);
        $this->assertEquals('Please upload the battery cleaning photograph and enter the battery voltage.', $report->correction_notes);

        // 19. Employee receives notification & opens report.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $engineer->id,
            'type' => 'correction_requested',
        ]);

        $this->actingAs($engineer);
        $empCorrView = $this->get(route('engineer.reports.edit', $report->id));
        $empCorrView->assertStatus(200);
        $empCorrView->assertSee('Please upload the battery cleaning photograph');

        // 20. Employee corrects report (uploads battery photo and updates voltage).
        $fakeBatteryPhoto = UploadedFile::fake()->image('battery_cleaning.jpg', 800, 600);
        $this->postJson(route('engineer.reports.upload-photo', $report->id), [
            'photo' => $fakeBatteryPhoto,
            'section_key' => 'battery_inspection',
            'photo_type' => 'battery_photo',
        ]);

        // 21. Employee resubmits report.
        $resubmitResponse = $this->post(route('engineer.reports.submit', $report->id));
        $resubmitResponse->assertRedirect(route('engineer.reports.show', $report->id));
        $report->refresh();
        $service->refresh();
        $this->assertEquals('resubmitted', $report->status);
        $this->assertEquals('report_submitted', $service->status);

        // 22. Admin approves.
        $this->actingAs($admin);
        $approveResponse = $this->post(route('admin.reports.approve', $report->id));
        $approveResponse->assertSessionHas('success');
        $report->refresh();
        $service->refresh();

        // 23. Report becomes approved/locked and Service completed.
        $this->assertEquals('approved', $report->status);
        $this->assertEquals('completed', $service->status);
        $this->assertNotNull($report->approved_at);
        $this->assertNotNull($report->reviewed_by_id);

        // Employee cannot edit approved report
        $this->actingAs($engineer);
        $blockedEdit = $this->get(route('engineer.reports.edit', $report->id));
        $blockedEdit->assertRedirect(route('engineer.reports.show', $report->id));

        // 24. Admin can see the service/report in company-wise reporting.
        $this->actingAs($admin);
        $analyticsView = $this->get(route('admin.analytics'));
        $analyticsView->assertStatus(200);
        $analyticsView->assertSee('Sun on Earth Solutions');
        $analyticsView->assertSee($service->service_number);

        // 25. Admin can filter the report by employee, company, and date.
        $filteredAnalytics = $this->get(route('admin.analytics', [
            'company_id' => $soeCompany->id,
            'assigned_user_id' => $engineer->id,
            'status' => 'completed',
        ]));
        $filteredAnalytics->assertStatus(200);
        $filteredAnalytics->assertSee($service->service_number);
    }
}
