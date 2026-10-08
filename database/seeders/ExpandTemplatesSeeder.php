<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ReportTemplate;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExpandTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
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
            ],
            [
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
            ],
            [
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
            ],
            [
                'name' => 'Installation: Inverter (PCU) & Commissioning',
                'slug' => 'installation_commissioning',
                'schema_definition' => [
                    'steps' => [
                        'site_plant_info',
                        'pcu_installation',
                        'battery_installation',
                        'commissioning_testing',
                        'handover_signoff',
                        'photos_signoff',
                    ],
                ],
            ],
            [
                'name' => 'Site Inspection Feasibility Survey',
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
            ],
            [
                'name' => 'Complaint Attending Sheet',
                'slug' => 'complaint_attending',
                'schema_definition' => [
                    'steps' => [
                        'plant_details',
                        'complaint_intake',
                        'attended_work',
                        'plant_checklist_9point',
                        'handover_signoff',
                        'photos_signoff',
                    ],
                ],
            ],
            [
                'name' => 'Daily Work Report & Timesheet',
                'slug' => 'daily_work_report',
                'schema_definition' => [
                    'steps' => [
                        'shift_details',
                        'hourly_activity_log',
                        'meals_allowance',
                        'travel_conveyance',
                        'work_summary_signoff',
                        'photos_receipts',
                    ],
                ],
            ],
        ];

        foreach ($templates as $t) {
            ReportTemplate::updateOrCreate(
                ['slug' => $t['slug']],
                [
                    'name' => $t['name'],
                    'schema_definition' => $t['schema_definition'],
                    'is_active' => true,
                ]
            );
        }

        $serviceTypes = [
            [
                'name' => 'Periodic Service Inspection',
                'code' => 'service_report',
                'report_template_slug' => 'service_report',
                'description' => 'Comprehensive 10-step scheduled maintenance & diagnostic report',
            ],
            [
                'name' => 'Preventive Maintenance',
                'code' => 'preventive_maintenance',
                'report_template_slug' => 'service_report',
                'description' => 'Quarterly or bi-annual plant maintenance and system tune-up',
            ],
            [
                'name' => 'Breakdown Maintenance',
                'code' => 'breakdown_maintenance',
                'report_template_slug' => 'service_report',
                'description' => 'Unscheduled emergency breakdown attendance and repair',
            ],
            [
                'name' => 'Installation - Part 1: Structure & Modules',
                'code' => 'installation_structure',
                'report_template_slug' => 'installation_structure',
                'description' => 'Civil & mechanical module mounting, structure assembly, tilt alignment, wind safety',
            ],
            [
                'name' => 'Installation - Part 2: Electrical & Cabling',
                'code' => 'installation_electrical',
                'report_template_slug' => 'installation_electrical',
                'description' => 'DC/AC earthing pits, AJB string configuration, DCDB/ACDB routing, cabling lines 1-5',
            ],
            [
                'name' => 'Installation - Part 3: PCU & Commissioning',
                'code' => 'installation_commissioning',
                'report_template_slug' => 'installation_commissioning',
                'description' => 'Inverter parameters, battery bank stands/cabins, 9-point commissioning tests, handover',
            ],
            [
                'name' => 'Site Inspection & Feasibility Survey',
                'code' => 'site_inspection',
                'report_template_slug' => 'site_inspection',
                'description' => 'Pre-installation survey, EB sanction meters, day/night load, conduit routes, rooftop logistics',
            ],
            [
                'name' => 'Complaint Attending & Breakdown',
                'code' => 'complaint_attending',
                'report_template_slug' => 'complaint_attending',
                'description' => 'Customer complaint attendance, on-site troubleshooting, spares replacement, 9-point check',
            ],
            [
                'name' => 'Daily Work Log & Timesheet',
                'code' => 'daily_work_report',
                'report_template_slug' => 'daily_work_report',
                'description' => 'Field engineer daily activity timesheet, vehicle KM conveyance claim, meal allowances',
            ],
        ];

        foreach ($serviceTypes as $st) {
            ServiceType::updateOrCreate(
                ['code' => $st['code']],
                [
                    'name' => $st['name'],
                    'report_template_slug' => $st['report_template_slug'],
                    'description' => $st['description'],
                    'is_active' => true,
                ]
            );
        }

        // Ensure 3rd technician exists for 3-part installation projects
        $soe = Company::where('code', 'SOE')->first() ?? Company::first();
        if ($soe) {
            User::updateOrCreate(
                ['email' => 'anand@solar.local'],
                [
                    'name' => 'Anand Verma',
                    'password' => Hash::make('password123'),
                    'employee_code' => 'ENG-103',
                    'phone' => '+91 98765 00003',
                    'designation' => 'Solar Commissioning Engineer',
                    'company_id' => $soe->id,
                    'role' => 'engineer',
                    'status' => 'active',
                    'joining_date' => '2026-03-01',
                ]
            );
        }
    }
}
