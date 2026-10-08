<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ReportTemplate;
use App\Models\Service;
use App\Models\ServiceAssignment;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Companies
        $soe = Company::create([
            'name' => 'Sun on Earth',
            'code' => 'SOE',
            'email' => 'operations@sunonearth.in',
            'phone' => '+91 98765 43210',
            'address' => '42, Solar Innovation Hub, Outer Ring Road, Bangalore - 560103',
            'contact_person' => 'Vikram Singhania',
            'report_header_info' => 'Sun on Earth Solar Solutions Pvt Ltd | ISO 9001:2015 Certified | www.sunonearth.in',
            'is_active' => true,
        ]);

        $sabha = Company::create([
            'name' => 'Sabha',
            'code' => 'SAB',
            'email' => 'support@sabhasolar.com',
            'phone' => '+91 98765 12345',
            'address' => '108, Renewable Enclave, HITEC City, Hyderabad - 500081',
            'contact_person' => 'K. V. Rao',
            'report_header_info' => 'Sabha Renewable Power Systems | EPC & O&M Division | www.sabhasolar.com',
            'is_active' => true,
        ]);

        // 2. Report Templates
        $serviceReportTemplate = ReportTemplate::create([
            'name' => 'Standard Solar Service Report',
            'slug' => 'service_report',
            'schema_definition' => [
                'steps' => [
                    'customer_details',
                    'system_details',
                    'module_inspection',
                    'structure_inspection',
                    'pcu_inspection',
                    'battery_inspection',
                    'complaint_details',
                    'remarks',
                    'photos_documents',
                    'review_submit',
                ],
            ],
            'is_active' => true,
        ]);

        $installStructureTemplate = ReportTemplate::create([
            'name' => 'Installation: Structure & Module Mounting',
            'slug' => 'installation_structure',
            'schema_definition' => [
                'steps' => [
                    'site_plant_info',
                    'panels_delivered',
                    'mounting_work',
                    'structure_work',
                    'safety_checklist',
                    'photos_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $installElectricalTemplate = ReportTemplate::create([
            'name' => 'Installation: Electrical & Cabling',
            'slug' => 'installation_electrical',
            'schema_definition' => [
                'steps' => [
                    'site_plant_info',
                    'earthing_work',
                    'ajb_work',
                    'cabling_work',
                    'dcdb_acdb_work',
                    'photos_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $installCommissioningTemplate = ReportTemplate::create([
            'name' => 'Installation: Inverter (PCU) & Commissioning',
            'slug' => 'installation_commissioning',
            'schema_definition' => [
                'steps' => [
                    'site_plant_info',
                    'pcu_installation',
                    'battery_installation',
                    'commissioning_testing',
                    'handover_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $siteInspectionTemplate = ReportTemplate::create([
            'name' => 'Site Inspection Report',
            'slug' => 'site_inspection',
            'schema_definition' => [
                'steps' => [
                    'customer_site_details',
                    'power_req_meters',
                    'cabling_conduits',
                    'earthing_rooms_protection',
                    'rooftop_logistics',
                    'photos_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $complaintAttendingTemplate = ReportTemplate::create([
            'name' => 'Complaint Attending Report',
            'slug' => 'complaint_attending',
            'schema_definition' => [
                'steps' => [
                    'plant_details',
                    'complaint_intake',
                    'attended_work',
                    'plant_checklist_9point',
                    'handover_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $dailyWorkReportTemplate = ReportTemplate::create([
            'name' => 'Daily Work Report',
            'slug' => 'daily_work_report',
            'schema_definition' => [
                'steps' => [
                    'shift_details',
                    'hourly_activity_log',
                    'meals_allowance',
                    'travel_conveyance',
                    'work_summary_signoff',
                ],
            ],
            'is_active' => true,
        ]);

        $customerFeedbackTemplate = ReportTemplate::create([
            'name' => 'Customer Feedback & Satisfaction Form',
            'slug' => 'customer_feedback',
            'schema_definition' => [
                'steps' => [
                    'customer_project_details',
                    'ratings_experience',
                    'system_service_feedback',
                    'comments_recommendation',
                    'customer_confirmation',
                ],
            ],
            'is_active' => true,
        ]);

        // 3. Service Types
        $routine = ServiceType::create([
            'name' => 'Routine Service',
            'code' => 'routine_service',
            'report_template_slug' => 'service_report',
            'description' => 'Periodic quarterly preventive maintenance and plant health checkup',
            'is_active' => true,
        ]);

        $breakdown = ServiceType::create([
            'name' => 'Breakdown Maintenance',
            'code' => 'breakdown_maintenance',
            'report_template_slug' => 'service_report',
            'description' => 'Urgent corrective repairs, inverter faults, or electrical trip resolution',
            'is_active' => true,
        ]);

        $inspection = ServiceType::create([
            'name' => 'Solar Module Inspection',
            'code' => 'module_inspection',
            'report_template_slug' => 'service_report',
            'description' => 'Comprehensive thermographic and electrical analysis of PV modules',
            'is_active' => true,
        ]);

        $installStructType = ServiceType::create([
            'name' => 'Installation: Structure & Module Mounting',
            'code' => 'installation_structure',
            'report_template_slug' => 'installation_structure',
            'description' => 'Civil, mechanical mounting, panel placement, and wind safety structure work',
            'is_active' => true,
        ]);

        $installElecType = ServiceType::create([
            'name' => 'Installation: Electrical & Cabling',
            'code' => 'installation_electrical',
            'report_template_slug' => 'installation_electrical',
            'description' => 'Earthing pits, string wiring, AJB, DCDB, ACDB, conduit, and cable trays',
            'is_active' => true,
        ]);

        $installCommType = ServiceType::create([
            'name' => 'Installation: Inverter (PCU) & Commissioning',
            'code' => 'installation_commissioning',
            'report_template_slug' => 'installation_commissioning',
            'description' => 'Inverter/PCU setup, battery bank connection, testing, and plant commissioning',
            'is_active' => true,
        ]);

        $siteInspectType = ServiceType::create([
            'name' => 'Site Inspection',
            'code' => 'site_inspection',
            'report_template_slug' => 'site_inspection',
            'description' => 'Pre-installation site feasibility, EB bills, load calculations, and rooftop assessment',
            'is_active' => true,
        ]);

        $complaintType = ServiceType::create([
            'name' => 'Complaint Attending',
            'code' => 'complaint_attending',
            'report_template_slug' => 'complaint_attending',
            'description' => 'Attending customer complaints, troubleshooting faults, and 9-point health verification',
            'is_active' => true,
        ]);

        $dailyWorkType = ServiceType::create([
            'name' => 'Daily Work Report',
            'code' => 'daily_work_report',
            'report_template_slug' => 'daily_work_report',
            'description' => 'Daily personnel timesheet, hourly activity log, meals, and conveyance tracking',
            'is_active' => true,
        ]);

        $feedbackType = ServiceType::create([
            'name' => 'Customer Feedback & Satisfaction Survey',
            'code' => 'customer_feedback',
            'report_template_slug' => 'customer_feedback',
            'description' => 'Post-installation, service, maintenance or inspection customer satisfaction audit',
            'is_active' => true,
        ]);

        // 4. Users (Admin + Engineers)
        $admin = User::create([
            'name' => 'Operations Admin',
            'email' => 'admin@solar.local',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'employee_code' => 'ADM-001',
            'phone' => '+91 98000 00001',
            'designation' => 'Operations Director',
            'status' => 'active',
            'joining_date' => '2024-01-01',
        ]);

        $raj = User::create([
            'name' => 'Raj Kumar',
            'email' => 'raj@solar.local',
            'password' => Hash::make('password123'),
            'role' => 'engineer',
            'employee_code' => 'ENG-101',
            'phone' => '+91 98000 00002',
            'designation' => 'Senior Solar Field Engineer',
            'company_id' => $soe->id,
            'status' => 'active',
            'joining_date' => '2024-03-15',
        ]);

        $kiran = User::create([
            'name' => 'Kiran Sharma',
            'email' => 'kiran@solar.local',
            'password' => Hash::make('password123'),
            'role' => 'engineer',
            'employee_code' => 'ENG-102',
            'phone' => '+91 98000 00003',
            'designation' => 'Field Operations Technician',
            'company_id' => $sabha->id,
            'status' => 'active',
            'joining_date' => '2024-06-01',
        ]);

        $anand = User::create([
            'name' => 'Anand Verma',
            'email' => 'anand@solar.local',
            'password' => Hash::make('password123'),
            'role' => 'engineer',
            'employee_code' => 'ENG-103',
            'phone' => '+91 98000 00004',
            'designation' => 'Commissioning Specialist',
            'company_id' => $soe->id,
            'status' => 'active',
            'joining_date' => '2024-07-01',
        ]);

        // 5. Customers & Sites
        // Customer 1: ABC Industries under Sun on Earth
        $abc = Customer::create([
            'company_id' => $soe->id,
            'name' => 'ABC Industries',
            'contact_person' => 'Ramesh Sharma',
            'phone' => '+91 94444 11111',
            'email' => 'ramesh@abcind.com',
            'address' => 'Plot 14, Peenya Industrial Area, Phase 2, Bangalore - 560058',
            'status' => 'active',
        ]);

        $bangalorePlant = Site::create([
            'customer_id' => $abc->id,
            'name' => 'Bangalore Plant',
            'address' => 'Plot 14, Peenya Phase 2, Bangalore - 560058',
            'contact_person' => 'Suresh (Plant Manager)',
            'phone' => '+91 94444 22222',
            'location_notes' => 'Main Production Shed Roof (100 kW Grid-Tied System)',
        ]);

        $peenyaWarehouse = Site::create([
            'customer_id' => $abc->id,
            'name' => 'Peenya Warehouse',
            'address' => 'Shed 3, 4th Cross, Peenya 1st Stage, Bangalore',
            'contact_person' => 'Anand (Site Incharge)',
            'phone' => '+91 94444 33333',
            'location_notes' => 'Ground Mounted 25 kW Array with Battery Bank',
        ]);

        // Customer 2: Apex Agro Foods under Sabha
        $apex = Customer::create([
            'company_id' => $sabha->id,
            'name' => 'Apex Agro Foods',
            'contact_person' => 'Sunil Reddy',
            'phone' => '+91 95555 11111',
            'email' => 'sunil@apexagro.in',
            'address' => 'Highway 44, Shamshabad, Hyderabad - 501218',
            'status' => 'active',
        ]);

        $coldStorage = Site::create([
            'customer_id' => $apex->id,
            'name' => 'Cold Storage Unit 1',
            'address' => 'Survey No 88, Shamshabad Rural, Hyderabad',
            'contact_person' => 'Venkatesh',
            'phone' => '+91 95555 22222',
            'location_notes' => '50 kW Hybrid System with PCU and Tubular Battery Bank',
        ]);

        // 6. Initial Seed Service (SOE-SRV-0001)
        $service = Service::create([
            'service_number' => 'SOE-SRV-0001',
            'company_id' => $soe->id,
            'customer_id' => $abc->id,
            'site_id' => $bangalorePlant->id,
            'service_type_id' => $routine->id,
            'assigned_user_id' => $raj->id,
            'priority' => 'normal',
            'scheduled_date' => '2026-09-23',
            'description' => 'Solar plant inspection and maintenance. Check modules, inverter readings, structure stability and battery condition.',
            'status' => 'assigned',
            'created_by_id' => $admin->id,
        ]);

        ServiceAssignment::create([
            'service_id' => $service->id,
            'user_id' => $raj->id,
            'assigned_by_id' => $admin->id,
            'assigned_at' => now(),
            'status' => 'assigned',
            'notes' => 'Please perform complete 10-step inspection.',
        ]);

        AuditLog::log($service, 'assigned', 'Service created and assigned to Raj Kumar by Admin', null, [
            'service_number' => $service->service_number,
            'assigned_to' => $raj->name,
            'status' => 'assigned',
        ], $admin->id);
    }
}
