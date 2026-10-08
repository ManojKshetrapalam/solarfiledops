<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DataImport;
use App\Models\Report;
use App\Models\ReportData;
use App\Models\ReportTemplate;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class DataMigrationService
{
    /**
     * Get entity configuration for all 10 migration types.
     */
    public function getEntityConfigs(): array
    {
        $configs = [
            'companies' => [
                'name' => 'Companies / Entities',
                'order' => 1,
                'description' => 'Corporate subsidiaries, business entities, and regional branch operations.',
                'file_name' => '01_Companies.xlsx',
                'headers' => [
                    'Company Code', 'Company Name', 'Contact Person', 'Mobile', 'Email', 'Address', 'Report Header Info', 'Status'
                ],
                'instructions' => [
                    ['Company Code', 'Required', 'Text (max 10)', 'Unique corporate code (e.g. SOE, SABHA). Used for relationships.', 'SOE'],
                    ['Company Name', 'Required', 'Text', 'Official legal business name.', 'Sun on Earth Solar Solutions Pvt Ltd'],
                    ['Contact Person', 'Optional', 'Text', 'Primary corporate administrator or director.', 'Vikram Singhania'],
                    ['Mobile', 'Optional', 'Text / Phone', 'Primary contact phone number.', '+91 98765 43210'],
                    ['Email', 'Required', 'Email format', 'Primary operational email for alerts.', 'operations@sunonearth.in'],
                    ['Address', 'Optional', 'Text', 'Headquarters or office physical address.', '42, Solar Innovation Hub, Bangalore'],
                    ['Report Header Info', 'Optional', 'Text', 'Official footer/header disclaimer printed on reports.', 'ISO 9001:2015 Certified Solar EPC'],
                    ['Status', 'Optional', 'Active / Inactive', 'Entity operational status. Defaults to Active.', 'Active'],
                ],
                'sample' => [
                    'SOE', 'Sun on Earth Solar Solutions Pvt Ltd', 'Vikram Singhania', '+91 98765 43210', 'operations@sunonearth.in', '42, Solar Innovation Hub, Bangalore', 'ISO 9001:2015 Certified Solar EPC', 'Active'
                ],
            ],

            'employees' => [
                'name' => 'Employees / Engineers',
                'order' => 2,
                'description' => 'Field engineers, technicians, and operations personnel. Auto-creates user logins with temporary passwords.',
                'file_name' => '02_Employees.xlsx',
                'headers' => [
                    'Employee Code', 'First Name', 'Last Name', 'Mobile', 'Email', 'Designation', 'Company Code', 'Joining Date', 'Role', 'Status'
                ],
                'instructions' => [
                    ['Employee Code', 'Required', 'Text', 'Unique employee badge or payroll number (e.g. EMP-101).', 'EMP-101'],
                    ['First Name', 'Required', 'Text', 'First name. Used to auto-generate login User ID & temp password.', 'Manoj'],
                    ['Last Name', 'Optional', 'Text', 'Surname / family name.', 'Kumar'],
                    ['Mobile', 'Required', 'Phone number', 'Mobile phone used for on-site verification & contact.', '+91 98765 00001'],
                    ['Email', 'Required', 'Valid Email', 'Corporate or personal email. Must be unique in system.', 'manoj.kumar@solar.local'],
                    ['Designation', 'Required', 'Text', 'Official job title (e.g. Senior Solar Engineer).', 'Senior Solar Field Engineer'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Entity code where employee belongs. Must match a valid Company.', 'SOE'],
                    ['Joining Date', 'Optional', 'YYYY-MM-DD', 'Date of employment.', '2026-01-15'],
                    ['Role', 'Optional', 'engineer / admin', 'System role. Defaults to engineer.', 'engineer'],
                    ['Status', 'Optional', 'Active / Inactive', 'Employee account status. Defaults to Active.', 'Active'],
                ],
                'sample' => [
                    'EMP-101', 'Manoj', 'Kumar', '+91 98765 00001', 'manoj.kumar@solar.local', 'Senior Solar Field Engineer', 'SOE', '2026-01-15', 'engineer', 'Active'
                ],
            ],

            'customers' => [
                'name' => 'Customers & Clients',
                'order' => 3,
                'description' => 'Commercial, industrial, residential, and agricultural solar plant owners.',
                'file_name' => '03_Customers.xlsx',
                'headers' => [
                    'Customer Code', 'Company Code', 'Customer Name', 'Contact Person', 'Mobile', 'Email', 'Address', 'Status'
                ],
                'instructions' => [
                    ['Customer Code', 'Required', 'Text', 'Unique customer identifier code (e.g. CUST-001). Used for Sites resolution.', 'CUST-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity managing this customer account.', 'SOE'],
                    ['Customer Name', 'Required', 'Text', 'Enterprise or client individual name.', 'Apex Industrial Logistics Hub'],
                    ['Contact Person', 'Optional', 'Text', 'Plant manager or administrative representative.', 'Rajesh Verma'],
                    ['Mobile', 'Optional', 'Phone', 'Primary contact phone number.', '+91 94444 11111'],
                    ['Email', 'Optional', 'Email', 'Billing or operational contact email.', 'contact@apexlogistics.in'],
                    ['Address', 'Optional', 'Text', 'Registered office or primary address.', 'Plot 18, Industrial Area Phase II, Bangalore'],
                    ['Status', 'Optional', 'Active / Inactive', 'Account status. Defaults to Active.', 'Active'],
                ],
                'sample' => [
                    'CUST-001', 'SOE', 'Apex Industrial Logistics Hub', 'Rajesh Verma', '+91 94444 11111', 'contact@apexlogistics.in', 'Plot 18, Industrial Area Phase II, Bangalore', 'Active'
                ],
            ],

            'sites' => [
                'name' => 'Sites & Plant Locations',
                'order' => 4,
                'description' => 'Rooftop and ground-mount installation sites associated with customers.',
                'file_name' => '04_Sites.xlsx',
                'headers' => [
                    'Site Code', 'Customer Code', 'Site Name', 'Site Address', 'System Capacity', 'Contact Person', 'Mobile', 'Location Notes'
                ],
                'instructions' => [
                    ['Site Code', 'Required', 'Text', 'Unique site location code (e.g. SITE-001). Used for service job links.', 'SITE-001'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Must match an existing Customer Code in database.', 'CUST-001'],
                    ['Site Name', 'Required', 'Text', 'Descriptive plant site name.', 'Warehouse 3 Rooftop Solar Plant'],
                    ['Site Address', 'Required', 'Text', 'Physical site address where panels are mounted.', 'Peenya Industrial Estate, Shed #4'],
                    ['System Capacity', 'Optional', 'Text', 'Rated solar capacity (e.g. 100 kW Rooftop, 50 kW Grid-Tied).', '100 kW Rooftop'],
                    ['Contact Person', 'Optional', 'Text', 'On-site supervisor or security incharge.', 'Gopal Krishna'],
                    ['Mobile', 'Optional', 'Phone', 'Site supervisor contact number.', '+91 98888 22222'],
                    ['Location Notes', 'Optional', 'Text', 'Gate entry guidelines, rooftop ladder key access, etc.', 'Ladder access at Building B rear'],
                ],
                'sample' => [
                    'SITE-001', 'CUST-001', 'Warehouse 3 Rooftop Solar Plant', 'Peenya Industrial Estate, Shed #4', '100 kW Rooftop', 'Gopal Krishna', '+91 98888 22222', 'Ladder access at Building B rear'
                ],
            ],

            'installations' => [
                'name' => 'Installation Records',
                'order' => 5,
                'description' => 'Historical module mounting, electrical routing, and commissioning installation jobs.',
                'file_name' => '05_Installation_Records.xlsx',
                'headers' => [
                    'Installation ID', 'Company Code', 'Customer Code', 'Site Code', 'Engineer Code', 'Installation Date', 'System Capacity', 'Status', 'Description'
                ],
                'instructions' => [
                    ['Installation ID', 'Required', 'Text', 'Unique historical installation record ID (e.g. INST-001).', 'INST-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity managing installation.', 'SOE'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Customer where work was performed.', 'CUST-001'],
                    ['Site Code', 'Required', 'Existing Site Code', 'Site location of installation.', 'SITE-001'],
                    ['Engineer Code', 'Required', 'Existing Employee Code', 'Lead technician or engineer who completed work.', 'EMP-101'],
                    ['Installation Date', 'Required', 'YYYY-MM-DD', 'Commissioning or installation completion date.', '2026-02-10'],
                    ['System Capacity', 'Optional', 'Text', 'Installed solar plant rating.', '100 kW'],
                    ['Status', 'Optional', 'completed / in_progress', 'Job status. Defaults to completed.', 'completed'],
                    ['Description', 'Optional', 'Text', 'Detailed notes on modules, structure, and inverter commissioning.', '100 kW Rooftop structure, AC/DC cabling, and PCU commissioned'],
                ],
                'sample' => [
                    'INST-001', 'SOE', 'CUST-001', 'SITE-001', 'EMP-101', '2026-02-10', '100 kW', 'completed', '100 kW Rooftop structure, AC/DC cabling, and PCU commissioned'
                ],
            ],

            'services' => [
                'name' => 'Service Records',
                'order' => 6,
                'description' => 'Scheduled preventive maintenance, health checks, and periodic diagnostic audits.',
                'file_name' => '06_Service_Records.xlsx',
                'headers' => [
                    'Service ID', 'Company Code', 'Customer Code', 'Site Code', 'Engineer Code', 'Service Date', 'Priority', 'Status', 'Description'
                ],
                'instructions' => [
                    ['Service ID', 'Required', 'Text', 'Unique historical service dispatch number (e.g. SRV-001).', 'SRV-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity.', 'SOE'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Customer record.', 'CUST-001'],
                    ['Site Code', 'Required', 'Existing Site Code', 'Site location.', 'SITE-001'],
                    ['Engineer Code', 'Required', 'Existing Employee Code', 'Attending engineer.', 'EMP-101'],
                    ['Service Date', 'Required', 'YYYY-MM-DD', 'Date service visit was performed.', '2026-03-01'],
                    ['Priority', 'Optional', 'low / normal / high / urgent', 'Priority level. Defaults to normal.', 'normal'],
                    ['Status', 'Optional', 'completed / in_progress', 'Service status. Defaults to completed.', 'completed'],
                    ['Description', 'Optional', 'Text', 'Description of routine maintenance performed.', 'Quarterly preventive maintenance and thermography check']
                ],
                'sample' => [
                    'SRV-001', 'SOE', 'CUST-001', 'SITE-001', 'EMP-101', '2026-03-01', 'normal', 'completed', 'Quarterly preventive maintenance and thermography check'
                ],
            ],

            'complaints' => [
                'name' => 'Complaints & Breakdowns',
                'order' => 7,
                'description' => 'Customer reported faults, inverter breakdown tickets, and troubleshooting sheets.',
                'file_name' => '07_Complaints.xlsx',
                'headers' => [
                    'Complaint ID', 'Company Code', 'Customer Code', 'Site Code', 'Engineer Code', 'Complaint Date', 'Priority', 'Status', 'Description'
                ],
                'instructions' => [
                    ['Complaint ID', 'Required', 'Text', 'Unique complaint ticket number (e.g. CMP-001).', 'CMP-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity.', 'SOE'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Customer record.', 'CUST-001'],
                    ['Site Code', 'Required', 'Existing Site Code', 'Site location.', 'SITE-001'],
                    ['Engineer Code', 'Required', 'Existing Employee Code', 'Troubleshooting engineer.', 'EMP-101'],
                    ['Complaint Date', 'Required', 'YYYY-MM-DD', 'Date complaint was attended.', '2026-03-15'],
                    ['Priority', 'Optional', 'low / normal / high / urgent', 'Urgency. Defaults to urgent.', 'urgent'],
                    ['Status', 'Optional', 'completed / in_progress', 'Ticket resolution status. Defaults to completed.', 'completed'],
                    ['Description', 'Optional', 'Text', 'Reported breakdown and repair action taken.', 'Grid trip on Phase 2; MC4 connector replaced and generation restored']
                ],
                'sample' => [
                    'CMP-001', 'SOE', 'CUST-001', 'SITE-001', 'EMP-101', '2026-03-15', 'urgent', 'completed', 'Grid trip on Phase 2; MC4 connector replaced and generation restored'
                ],
            ],

            'daily_work_reports' => [
                'name' => 'Daily Work Reports',
                'order' => 8,
                'description' => 'Engineer daily shift activity logs, hours worked, travel timesheets, and summaries.',
                'file_name' => '08_Daily_Work_Reports.xlsx',
                'headers' => [
                    'Report ID', 'Employee Code', 'Report Date', 'Hours Worked', 'Work Done', 'Location', 'Status'
                ],
                'instructions' => [
                    ['Report ID', 'Required', 'Text', 'Unique daily work report number (e.g. DWR-001).', 'DWR-001'],
                    ['Employee Code', 'Required', 'Existing Employee Code', 'Staff member reporting timesheet.', 'EMP-101'],
                    ['Report Date', 'Required', 'YYYY-MM-DD', 'Date of work shift.', '2026-03-20'],
                    ['Hours Worked', 'Optional', 'Numeric', 'Total shift hours (e.g. 8).', '8'],
                    ['Work Done', 'Required', 'Text', 'Summary of field activity and tasks completed.', 'Installed DCDB cabling and performed tilt alignment on 50 panels'],
                    ['Location', 'Optional', 'Text', 'Work location (e.g. Bangalore Field Site).', 'Peenya Industrial Site'],
                    ['Status', 'Optional', 'approved / submitted', 'Review status. Defaults to approved.', 'approved'],
                ],
                'sample' => [
                    'DWR-001', 'EMP-101', '2026-03-20', '8', 'Installed DCDB cabling and performed tilt alignment on 50 panels', 'Peenya Industrial Site', 'approved'
                ],
            ],

            'site_inspections' => [
                'name' => 'Site Inspection Reports',
                'order' => 9,
                'description' => 'Pre-installation feasibility surveys, rooftop dimensions, shading, and EB meter surveys.',
                'file_name' => '09_Site_Inspection_Reports.xlsx',
                'headers' => [
                    'Survey ID', 'Company Code', 'Customer Code', 'Site Code', 'Engineer Code', 'Survey Date', 'Status', 'Observations'
                ],
                'instructions' => [
                    ['Survey ID', 'Required', 'Text', 'Unique survey report number (e.g. SURV-001).', 'SURV-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity.', 'SOE'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Customer record.', 'CUST-001'],
                    ['Site Code', 'Required', 'Existing Site Code', 'Surveyed site location.', 'SITE-001'],
                    ['Engineer Code', 'Required', 'Existing Employee Code', 'Surveying engineer.', 'EMP-101'],
                    ['Survey Date', 'Required', 'YYYY-MM-DD', 'Date feasibility survey was conducted.', '2026-01-10'],
                    ['Status', 'Optional', 'approved / submitted', 'Survey approval status. Defaults to approved.', 'approved'],
                    ['Observations', 'Optional', 'Text', 'Rooftop condition, EB sanction load, and feasibility conclusion.', 'Rooftop RCC slab in good condition, 120 kW south-facing feasible']
                ],
                'sample' => [
                    'SURV-001', 'SOE', 'CUST-001', 'SITE-001', 'EMP-101', '2026-01-10', 'approved', 'Rooftop RCC slab in good condition, 120 kW south-facing feasible'
                ],
            ],

            'feedback' => [
                'name' => 'Customer Feedback',
                'order' => 10,
                'description' => 'Customer satisfaction audits, 10-point ratings, comments, and testimonial authorizations.',
                'file_name' => '10_Feedback.xlsx',
                'headers' => [
                    'Feedback ID', 'Company Code', 'Customer Code', 'Site Code', 'Engineer Code', 'Feedback Date', 'Overall Rating (1-5)', 'Would Recommend (YES/NO)', 'Comments', 'Status'
                ],
                'instructions' => [
                    ['Feedback ID', 'Required', 'Text', 'Unique feedback sheet ID (e.g. FBK-001).', 'FBK-001'],
                    ['Company Code', 'Required', 'Existing Company Code', 'Company Entity.', 'SOE'],
                    ['Customer Code', 'Required', 'Existing Customer Code', 'Customer record.', 'CUST-001'],
                    ['Site Code', 'Required', 'Existing Site Code', 'Site location.', 'SITE-001'],
                    ['Engineer Code', 'Required', 'Existing Employee Code', 'Engineer who conducted or logged feedback.', 'EMP-101'],
                    ['Feedback Date', 'Required', 'YYYY-MM-DD', 'Date feedback was collected.', '2026-03-25'],
                    ['Overall Rating (1-5)', 'Required', '1, 2, 3, 4, or 5', 'Overall customer experience score from 1 to 5.', '5'],
                    ['Would Recommend (YES/NO)', 'Optional', 'YES / NO / MAYBE', 'Customer willingness to recommend SolarOps.', 'YES'],
                    ['Comments', 'Optional', 'Text', 'Customer remarks, suggestions, and feedback verbatim.', 'Highly professional team, work executed seamlessly without interruptions'],
                    ['Status', 'Optional', 'approved / submitted', 'Verification status. Defaults to approved.', 'approved']
                ],
                'sample' => [
                    'FBK-001', 'SOE', 'CUST-001', 'SITE-001', 'EMP-101', '2026-03-25', '5', 'YES', 'Highly professional team, work executed seamlessly without interruptions', 'approved'
                ],
            ],
        ];

        foreach ($configs as &$cfg) {
            $cfg['title'] = $cfg['name'];
        }

        return $configs;
    }

    /**
     * Generate an Excel template with "Data" and "Instructions" sheets.
     */
    public function generateTemplate(string $type): Spreadsheet
    {
        $configs = $this->getEntityConfigs();
        if (!isset($configs[$type])) {
            throw new \InvalidArgumentException("Invalid migration entity type: {$type}");
        }

        $config = $configs[$type];
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Data
        $dataSheet = $spreadsheet->getActiveSheet();
        $dataSheet->setTitle('Data');

        // Headers
        $headers = $config['headers'];
        $dataSheet->fromArray([$headers], null, 'A1');

        // Sample Row
        if (!empty($config['sample'])) {
            $dataSheet->fromArray([$config['sample']], null, 'A2');
        }

        // Style Data Headers
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";

        $dataSheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'], // Slate-900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $dataSheet->getRowDimension(1)->setRowHeight(28);

        // Auto-fit column widths
        for ($i = 1; $i <= count($headers); $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $dataSheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Sheet 2: Instructions
        $instSheet = $spreadsheet->createSheet();
        $instSheet->setTitle('Instructions');

        $instHeaders = ['Column Name', 'Requirement', 'Data Type', 'Description / Validation Rules', 'Example Value'];
        $instSheet->fromArray([$instHeaders], null, 'A1');

        $instData = $config['instructions'];
        $instSheet->fromArray($instData, null, 'A2');

        // Style Instructions Headers
        $instHeaderRange = "A1:E1";
        $instSheet->getStyle($instHeaderRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F59E0B'], // Amber-500
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $instSheet->getRowDimension(1)->setRowHeight(28);

        for ($i = 1; $i <= 5; $i++) {
            $colLetter = Coordinate::stringFromColumnIndex($i);
            $instSheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Return focus to Sheet 1
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Create zip containing all 10 templates.
     */
    public function generateAllTemplatesZip(): string
    {
        $tempDir = storage_path('app/temp_templates_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = storage_path('app/SolarOps_Data_Migration_Templates.zip');
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create zip file at {$zipPath}");
        }

        $configs = $this->getEntityConfigs();
        foreach ($configs as $type => $conf) {
            $spreadsheet = $this->generateTemplate($type);
            $filePath = $tempDir . '/' . $conf['file_name'];
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);

            $zip->addFile($filePath, $conf['file_name']);
        }

        $zip->close();

        // Cleanup temp files
        foreach (glob($tempDir . '/*') as $f) {
            unlink($f);
        }
        rmdir($tempDir);

        return $zipPath;
    }

    /**
     * Validate and preview an uploaded Excel file.
     */
    public function validateAndPreview(UploadedFile $file, string $type): array
    {
        $configs = $this->getEntityConfigs();
        if (!isset($configs[$type])) {
            throw new \InvalidArgumentException("Invalid migration type: {$type}");
        }

        $config = $configs[$type];
        $spreadsheet = IOFactory::load($file->getRealPath());
        $dataSheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getActiveSheet();
        $rawRows = $dataSheet->toArray(null, true, true, false);

        if (empty($rawRows) || count($rawRows) < 2) {
            return [
                'success' => false,
                'error' => 'The uploaded Excel sheet contains no data rows.',
                'total_rows' => 0,
                'valid_count' => 0,
                'warning_count' => 0,
                'error_count' => 0,
                'duplicate_count' => 0,
                'rows' => [],
            ];
        }

        // Validate Header Columns
        $fileHeaders = array_map('trim', array_filter($rawRows[0] ?? []));
        $expectedHeaders = $config['headers'];

        // Normalize comparison
        $missingHeaders = [];
        foreach ($expectedHeaders as $exp) {
            $found = false;
            foreach ($fileHeaders as $fh) {
                if (strcasecmp(trim($exp), trim($fh)) === 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingHeaders[] = $exp;
            }
        }

        if (!empty($missingHeaders)) {
            return [
                'success' => false,
                'error' => 'Missing required column headers: ' . implode(', ', $missingHeaders),
                'total_rows' => count($rawRows) - 1,
                'valid_count' => 0,
                'warning_count' => 0,
                'error_count' => count($rawRows) - 1,
                'duplicate_count' => 0,
                'rows' => [],
            ];
        }

        // Map column index by header name
        $headerMap = [];
        foreach ($fileHeaders as $idx => $h) {
            foreach ($expectedHeaders as $eh) {
                if (strcasecmp(trim($h), trim($eh)) === 0) {
                    $headerMap[$eh] = $idx;
                }
            }
        }

        $parsedRows = [];
        $validCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $duplicateCount = 0;

        // In-file duplicate trackers
        $seenIdentifiers = [];
        $seenEmails = [];
        $seenUsernames = [];

        // Validate each row
        for ($r = 1; $r < count($rawRows); $r++) {
            $row = $rawRows[$r];
            // Skip completely empty rows
            $hasContent = false;
            foreach ($row as $cell) {
                if (!empty(trim((string)$cell))) {
                    $hasContent = true;
                    break;
                }
            }
            if (!$hasContent) {
                continue;
            }

            $rowResult = $this->validateRow($type, $row, $headerMap, $r + 1, $seenIdentifiers, $seenEmails, $seenUsernames);
            $parsedRows[] = $rowResult;

            if ($rowResult['status'] === 'ready') {
                $validCount++;
            } elseif ($rowResult['status'] === 'warning') {
                $warningCount++;
                $validCount++;
            } elseif ($rowResult['status'] === 'duplicate') {
                $duplicateCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success' => true,
            'type' => $type,
            'import_type' => $type,
            'config' => $config,
            'headers' => $expectedHeaders,
            'type_name' => $config['name'],
            'total_rows' => count($parsedRows),
            'valid_count' => $validCount,
            'warning_count' => $warningCount,
            'error_count' => $errorCount,
            'duplicate_count' => $duplicateCount,
            'can_import' => ($validCount > 0),
            'rows' => $parsedRows,
        ];
    }

    /**
     * Validate an individual row for a specific type.
     */
    protected function validateRow(
        string $type,
        array $row,
        array $headerMap,
        int $rowNumber,
        array &$seenIdentifiers,
        array &$seenEmails,
        array &$seenUsernames
    ): array {
        $get = fn($key) => isset($headerMap[$key]) ? trim((string)($row[$headerMap[$key]] ?? '')) : '';

        $errors = [];
        $warnings = [];
        $data = [];
        $generatedData = [];
        $isDuplicate = false;

        switch ($type) {
            case 'companies':
                $code = strtoupper($get('Company Code'));
                $name = $get('Company Name');
                $email = $get('Email');

                $data = [
                    'code' => $code,
                    'name' => $name,
                    'contact_person' => $get('Contact Person'),
                    'phone' => $get('Mobile'),
                    'email' => $email,
                    'address' => $get('Address'),
                    'report_header_info' => $get('Report Header Info'),
                    'is_active' => strtolower($get('Status')) !== 'inactive',
                ];

                if (empty($code)) $errors[] = "Company Code is required.";
                if (empty($name)) $errors[] = "Company Name is required.";
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";

                if (!empty($code)) {
                    if (isset($seenIdentifiers[$code])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Company Code '{$code}' in uploaded file.";
                    } elseif (Company::where('code', $code)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Company Code '{$code}' already exists in database.";
                    }
                    $seenIdentifiers[$code] = true;
                }
                break;

            case 'employees':
                $empCode = strtoupper($get('Employee Code'));
                $firstName = $get('First Name');
                $lastName = $get('Last Name');
                $mobile = $get('Mobile');
                $email = strtolower($get('Email'));
                $designation = $get('Designation');
                $companyCode = strtoupper($get('Company Code'));
                $role = strtolower($get('Role')) === 'admin' ? 'admin' : 'engineer';

                $fullName = trim($firstName . ' ' . $lastName);

                if (empty($empCode)) $errors[] = "Employee Code is required.";
                if (empty($firstName)) $errors[] = "First Name is required.";
                if (empty($mobile)) $errors[] = "Mobile number is required.";
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
                if (empty($designation)) $errors[] = "Designation is required.";
                if (empty($companyCode)) $errors[] = "Company Code is required.";

                // Foreign Key resolution
                $company = null;
                if (!empty($companyCode)) {
                    $company = Company::where('code', $companyCode)->first();
                    if (!$company) {
                        $errors[] = "Company Code '{$companyCode}' does not exist in database.";
                    }
                }

                // Check Duplicates
                if (!empty($empCode)) {
                    if (isset($seenIdentifiers[$empCode])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Employee Code '{$empCode}' in uploaded file.";
                    } elseif (User::where('employee_code', $empCode)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Employee Code '{$empCode}' already exists in database.";
                    }
                    $seenIdentifiers[$empCode] = true;
                }

                if (!empty($email)) {
                    if (isset($seenEmails[$email])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Email '{$email}' in uploaded file.";
                    } elseif (User::where('email', $email)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Email '{$email}' already exists in database.";
                    }
                    $seenEmails[$email] = true;
                }

                // Generate Username & Temporary Password
                $username = User::generateUniqueUsername($firstName, $lastName);
                // Ensure no conflict with other generated usernames in the batch
                while (isset($seenUsernames[$username])) {
                    $num = preg_match('/(\d+)$/', $username, $m) ? ((int)$m[1] + 1) : 2;
                    $username = preg_replace('/\d+$/', '', $username) . $num;
                }
                $seenUsernames[$username] = true;

                $temporaryPassword = strtolower(Str::slug($firstName, '')) . '123';
                if (strlen($temporaryPassword) < 6) {
                    $temporaryPassword = $username . '123';
                }

                $generatedData = [
                    'username' => $username,
                    'temporary_password' => $temporaryPassword,
                ];

                $data = [
                    'employee_code' => $empCode,
                    'name' => $fullName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $mobile,
                    'email' => $email,
                    'designation' => $designation,
                    'company_id' => $company?->id,
                    'company_code' => $companyCode,
                    'joining_date' => $this->parseDate($get('Joining Date')),
                    'role' => $role,
                    'status' => strtolower($get('Status')) === 'inactive' ? 'inactive' : 'active',
                ];
                break;

            case 'customers':
                $custCode = strtoupper($get('Customer Code'));
                $companyCode = strtoupper($get('Company Code'));
                $name = $get('Customer Name');

                if (empty($custCode)) $errors[] = "Customer Code is required.";
                if (empty($name)) $errors[] = "Customer Name is required.";
                if (empty($companyCode)) $errors[] = "Company Code is required.";

                $company = null;
                if (!empty($companyCode)) {
                    $company = Company::where('code', $companyCode)->first();
                    if (!$company) {
                        $errors[] = "Company Code '{$companyCode}' does not exist in database.";
                    }
                }

                if (!empty($custCode)) {
                    if (isset($seenIdentifiers[$custCode])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Customer Code '{$custCode}' in file.";
                    } elseif (Customer::where('customer_code', $custCode)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Customer Code '{$custCode}' already exists in database.";
                    }
                    $seenIdentifiers[$custCode] = true;
                }

                $data = [
                    'customer_code' => $custCode,
                    'company_id' => $company?->id,
                    'company_code' => $companyCode,
                    'name' => $name,
                    'contact_person' => $get('Contact Person'),
                    'phone' => $get('Mobile'),
                    'email' => $get('Email'),
                    'address' => $get('Address'),
                    'status' => strtolower($get('Status')) === 'inactive' ? 'inactive' : 'active',
                ];
                break;

            case 'sites':
                $siteCode = strtoupper($get('Site Code'));
                $custCode = strtoupper($get('Customer Code'));
                $name = $get('Site Name');

                if (empty($siteCode)) $errors[] = "Site Code is required.";
                if (empty($custCode)) $errors[] = "Customer Code is required.";
                if (empty($name)) $errors[] = "Site Name is required.";

                $customer = null;
                if (!empty($custCode)) {
                    $customer = Customer::where('customer_code', $custCode)->first();
                    if (!$customer) {
                        $errors[] = "Customer Code '{$custCode}' does not exist in database.";
                    }
                }

                if (!empty($siteCode)) {
                    if (isset($seenIdentifiers[$siteCode])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Site Code '{$siteCode}' in file.";
                    } elseif (Site::where('site_code', $siteCode)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Site Code '{$siteCode}' already exists in database.";
                    }
                    $seenIdentifiers[$siteCode] = true;
                }

                $data = [
                    'site_code' => $siteCode,
                    'customer_id' => $customer?->id,
                    'customer_code' => $custCode,
                    'name' => $name,
                    'address' => $get('Site Address') ?: ($customer?->address ?? 'Site Address'),
                    'system_capacity' => $get('System Capacity'),
                    'contact_person' => $get('Contact Person'),
                    'phone' => $get('Mobile'),
                    'location_notes' => $get('Location Notes'),
                ];
                break;

            case 'installations':
            case 'services':
            case 'complaints':
            case 'site_inspections':
            case 'feedback':
                $idKey = match ($type) {
                    'installations' => 'Installation ID',
                    'services' => 'Service ID',
                    'complaints' => 'Complaint ID',
                    'site_inspections' => 'Survey ID',
                    'feedback' => 'Feedback ID',
                };
                $recordId = strtoupper($get($idKey));
                $companyCode = strtoupper($get('Company Code'));
                $custCode = strtoupper($get('Customer Code'));
                $siteCode = strtoupper($get('Site Code'));
                $engCode = strtoupper($get('Engineer Code'));

                if (empty($recordId)) $errors[] = "{$idKey} is required.";
                if (empty($companyCode)) $errors[] = "Company Code is required.";
                if (empty($custCode)) $errors[] = "Customer Code is required.";
                if (empty($siteCode)) $errors[] = "Site Code is required.";
                if (empty($engCode)) $errors[] = "Engineer Code is required.";

                $company = !empty($companyCode) ? Company::where('code', $companyCode)->first() : null;
                if (!empty($companyCode) && !$company) $errors[] = "Company Code '{$companyCode}' does not exist.";

                $customer = !empty($custCode) ? Customer::where('customer_code', $custCode)->first() : null;
                if (!empty($custCode) && !$customer) $errors[] = "Customer Code '{$custCode}' does not exist.";

                $site = !empty($siteCode) ? Site::where('site_code', $siteCode)->first() : null;
                if (!empty($siteCode) && !$site) $errors[] = "Site Code '{$siteCode}' does not exist.";

                $engineer = !empty($engCode) ? User::where('employee_code', $engCode)->first() : null;
                if (!empty($engCode) && !$engineer) $errors[] = "Engineer Code '{$engCode}' does not exist.";

                if (!empty($recordId)) {
                    if (isset($seenIdentifiers[$recordId])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate {$idKey} '{$recordId}' in file.";
                    } elseif (Service::where('service_number', $recordId)->exists() || Report::where('report_number', $recordId)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "{$idKey} '{$recordId}' already exists in database.";
                    }
                    $seenIdentifiers[$recordId] = true;
                }

                $data = [
                    'record_id' => $recordId,
                    'company_id' => $company?->id,
                    'customer_id' => $customer?->id,
                    'site_id' => $site?->id,
                    'engineer_id' => $engineer?->id,
                    'date' => $this->parseDate($get('Installation Date') ?: ($get('Service Date') ?: ($get('Complaint Date') ?: ($get('Survey Date') ?: $get('Feedback Date'))))),
                    'status' => strtolower($get('Status') ?: 'completed'),
                    'description' => $get('Description') ?: ($get('Observations') ?: $get('Comments')),
                    'rating' => $get('Overall Rating (1-5)'),
                    'recommend' => $get('Would Recommend (YES/NO)'),
                ];
                break;

            case 'daily_work_reports':
                $reportId = strtoupper($get('Report ID'));
                $empCode = strtoupper($get('Employee Code'));

                if (empty($reportId)) $errors[] = "Report ID is required.";
                if (empty($empCode)) $errors[] = "Employee Code is required.";

                $engineer = !empty($empCode) ? User::where('employee_code', $empCode)->first() : null;
                if (!empty($empCode) && !$engineer) $errors[] = "Employee Code '{$empCode}' does not exist.";

                if (!empty($reportId)) {
                    if (isset($seenIdentifiers[$reportId])) {
                        $isDuplicate = true;
                        $errors[] = "Duplicate Report ID '{$reportId}' in file.";
                    } elseif (Report::where('report_number', $reportId)->exists()) {
                        $isDuplicate = true;
                        $errors[] = "Report ID '{$reportId}' already exists in database.";
                    }
                    $seenIdentifiers[$reportId] = true;
                }

                $data = [
                    'record_id' => $reportId,
                    'engineer_id' => $engineer?->id,
                    'engineer_name' => $engineer?->name,
                    'date' => $this->parseDate($get('Report Date')),
                    'hours_worked' => $get('Hours Worked') ?: 8,
                    'work_done' => $get('Work Done'),
                    'location' => $get('Location'),
                    'status' => strtolower($get('Status') ?: 'approved'),
                ];
                break;
        }

        $status = 'ready';
        if ($isDuplicate) {
            $status = 'duplicate';
        } elseif (!empty($errors)) {
            $status = 'error';
        } elseif (!empty($warnings)) {
            $status = 'warning';
        }

        return [
            'row_index' => $rowNumber,
            'row_number' => $rowNumber,
            'status' => $status === 'ready' ? 'valid' : $status,
            'data' => $data,
            'generated_data' => $generatedData,
            'username' => $generatedData['username'] ?? ($data['username'] ?? null),
            'temp_password' => $generatedData['temporary_password'] ?? null,
            'errors' => $errors,
            'warnings' => $warnings,
            'message' => !empty($errors) ? implode('; ', $errors) : (!empty($warnings) ? implode('; ', $warnings) : 'Valid and ready to import'),
        ];
    }

    /**
     * Parse date safely from string or excel serial.
     */
    protected function parseDate(?string $value): string
    {
        if (empty($value)) {
            return now()->format('Y-m-d');
        }

        try {
            // Check numeric Excel serial
            if (is_numeric($value) && (float)$value > 30000) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value))->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * Execute confirmed import transactionally.
     */
    public function executeImport(string $type, array $validatedRows, int $userId, string $fileName = 'Import.xlsx'): array
    {
        $configs = $this->getEntityConfigs();
        if (!isset($configs[$type])) {
            throw new \InvalidArgumentException("Invalid migration type: {$type}");
        }

        $createdCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $createdCredentials = [];
        $errorLog = [];

        DB::beginTransaction();
        try {
            foreach ($validatedRows as $row) {
                if ($row['status'] === 'error' || $row['status'] === 'duplicate') {
                    $skippedCount++;
                    continue;
                }

                $d = $row['data'];
                $gen = $row['generated_data'] ?? [];

                switch ($type) {
                    case 'companies':
                        Company::create([
                            'code' => $d['code'],
                            'name' => $d['name'],
                            'contact_person' => $d['contact_person'] ?? null,
                            'phone' => $d['phone'] ?? null,
                            'email' => $d['email'],
                            'address' => $d['address'] ?? null,
                            'report_header_info' => $d['report_header_info'] ?? null,
                            'is_active' => $d['is_active'] ?? true,
                        ]);
                        $createdCount++;
                        break;

                    case 'employees':
                        $user = new User([
                            'name' => $d['name'],
                            'username' => $gen['username'] ?? User::generateUniqueUsername($d['first_name'], $d['last_name'] ?? null),
                            'email' => $d['email'],
                            'phone' => $d['phone'],
                            'employee_code' => $d['employee_code'],
                            'designation' => $d['designation'],
                            'company_id' => $d['company_id'],
                            'role' => $d['role'] ?? 'engineer',
                            'status' => $d['status'] ?? 'active',
                            'joining_date' => $d['joining_date'],
                        ]);

                        $tempPass = $gen['temporary_password'] ?? (strtolower($gen['username']) . '123');
                        $user->setTemporaryPassword($tempPass, 7);
                        $user->save();

                        $createdCredentials[] = [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'employee_code' => $user->employee_code,
                            'username' => $user->username,
                            'temporary_password' => $tempPass,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'status' => 'Pending Password Change',
                        ];

                        $createdCount++;
                        break;

                    case 'customers':
                        Customer::create([
                            'company_id' => $d['company_id'],
                            'customer_code' => $d['customer_code'],
                            'name' => $d['name'],
                            'contact_person' => $d['contact_person'] ?? null,
                            'phone' => $d['phone'] ?? null,
                            'email' => $d['email'] ?? null,
                            'address' => $d['address'] ?? null,
                            'status' => $d['status'] ?? 'active',
                        ]);
                        $createdCount++;
                        break;

                    case 'sites':
                        Site::create([
                            'customer_id' => $d['customer_id'],
                            'site_code' => $d['site_code'],
                            'name' => $d['name'],
                            'address' => $d['address'],
                            'system_capacity' => $d['system_capacity'] ?? null,
                            'contact_person' => $d['contact_person'] ?? null,
                            'phone' => $d['phone'] ?? null,
                            'location_notes' => $d['location_notes'] ?? null,
                        ]);
                        $createdCount++;
                        break;

                    case 'installations':
                    case 'services':
                    case 'complaints':
                    case 'site_inspections':
                    case 'feedback':
                        $typeSlug = match ($type) {
                            'installations' => 'installation_structure',
                            'services' => 'service_report',
                            'complaints' => 'complaint_attending',
                            'site_inspections' => 'site_inspection',
                            'feedback' => 'customer_feedback',
                        };

                        $serviceType = ServiceType::where('report_template_slug', $typeSlug)->first();
                        $template = ReportTemplate::where('slug', $typeSlug)->first();

                        $service = Service::create([
                            'service_number' => $d['record_id'],
                            'company_id' => $d['company_id'],
                            'customer_id' => $d['customer_id'],
                            'site_id' => $d['site_id'],
                            'service_type_id' => $serviceType?->id ?? ServiceType::first()->id,
                            'assigned_user_id' => $d['engineer_id'],
                            'priority' => 'normal',
                            'scheduled_date' => $d['date'],
                            'status' => 'completed',
                            'description' => $d['description'] ?? "Migrated historical {$type} record",
                            'created_by_id' => $userId,
                        ]);

                        $report = Report::create([
                            'report_number' => 'REP-' . $d['record_id'],
                            'service_id' => $service->id,
                            'template_id' => $template?->id,
                            'company_id' => $d['company_id'],
                            'customer_id' => $d['customer_id'],
                            'site_id' => $d['site_id'],
                            'engineer_id' => $d['engineer_id'],
                            'status' => 'approved',
                            'current_step' => 5,
                            'submitted_at' => $d['date'],
                            'approved_at' => $d['date'],
                            'reviewed_by_id' => $userId,
                        ]);

                        if ($type === 'feedback') {
                            ReportData::create([
                                'report_id' => $report->id,
                                'section_key' => 'overall_recommendation',
                                'data_json' => [
                                    'overall_experience' => (int)($d['rating'] ?: 5),
                                    'recommend_services' => $d['recommend'] ?: 'YES',
                                ],
                            ]);
                            ReportData::create([
                                'report_id' => $report->id,
                                'section_key' => 'comments_suggestions',
                                'data_json' => [
                                    'other_feedback' => $d['description'] ?? 'Satisfactory work',
                                ],
                            ]);
                        }

                        $createdCount++;
                        break;

                    case 'daily_work_reports':
                        $template = ReportTemplate::where('slug', 'daily_work_report')->first();
                        $engineer = User::find($d['engineer_id']);

                        $report = Report::create([
                            'report_number' => $d['record_id'],
                            'service_id' => null,
                            'template_id' => $template?->id,
                            'company_id' => $engineer?->company_id ?? Company::first()->id,
                            'customer_id' => null,
                            'site_id' => null,
                            'engineer_id' => $d['engineer_id'],
                            'status' => 'approved',
                            'current_step' => 5,
                            'submitted_at' => $d['date'],
                            'approved_at' => $d['date'],
                            'reviewed_by_id' => $userId,
                        ]);

                        ReportData::create([
                            'report_id' => $report->id,
                            'section_key' => 'shift_details',
                            'data_json' => [
                                'employee_name' => $engineer?->name,
                                'report_date' => $d['date'],
                                'hours_worked' => $d['hours_worked'],
                                'place_of_work' => $d['location'],
                                'tasks_summary' => $d['work_done'],
                            ],
                        ]);
                        $createdCount++;
                        break;
                }
            }

            // Create DataImport record
            $dataImport = DataImport::create([
                'import_type' => $type,
                'file_name' => $fileName,
                'user_id' => $userId,
                'total_rows' => count($validatedRows),
                'imported_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'status' => 'completed',
                'summary_data' => [
                    'created_count' => $createdCount,
                    'skipped_count' => $skippedCount,
                    'credentials_count' => count($createdCredentials),
                ],
                'error_log' => $errorLog,
            ]);

            AuditLog::create([
                'auditable_type' => DataImport::class,
                'auditable_id' => $dataImport->id,
                'user_id' => $userId,
                'event' => 'data_imported',
                'description' => "Imported {$createdCount} {$type} records from {$fileName}",
                'ip_address' => request()->ip(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'import_id' => $dataImport->id,
                'total_rows' => count($validatedRows),
                'imported_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'failed_count' => $failedCount,
                'credentials' => $createdCredentials,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return [
                'success' => false,
                'error' => 'Database error during import: ' . $e->getMessage(),
                'total_rows' => count($validatedRows),
                'imported_count' => 0,
                'skipped_count' => count($validatedRows),
                'failed_count' => count($validatedRows),
                'credentials' => [],
            ];
        }
    }

    /**
     * Generate an Excel error report.
     */
    public function generateErrorReport(string $type, array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Errors');

        $headers = ['Row #', 'Status', 'Errors / Notes', 'Record Identifier'];
        $sheet->fromArray([$headers], null, 'A1');

        $errorRows = [];
        foreach ($rows as $r) {
            if ($r['status'] === 'error' || $r['status'] === 'duplicate') {
                $identifier = $r['data']['employee_code'] ?? ($r['data']['customer_code'] ?? ($r['data']['site_code'] ?? ($r['data']['code'] ?? ($r['data']['record_id'] ?? '—'))));
                $errorRows[] = [
                    $r['row_number'],
                    strtoupper($r['status']),
                    $r['message'],
                    $identifier,
                ];
            }
        }

        $sheet->fromArray($errorRows, null, 'A2');

        // Style header
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E11D48'], // Rose-600
            ],
        ]);

        for ($i = 1; $i <= 4; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $filePath = storage_path("app/{$type}_Import_Errors_" . date('Y-m-d_His') . ".xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Generate employee credentials Excel sheet.
     */
    public function generateCredentialSheet(array $credentials, int $adminId): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Credentials');

        $headers = [
            'Employee Name', 'Employee Code', 'User ID', 'Temporary Password', 'Email', 'Mobile', 'Login Instructions'
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $rows = [];
        foreach ($credentials as $c) {
            $rows[] = [
                $c['name'],
                $c['employee_code'],
                $c['username'],
                $c['temporary_password'],
                $c['email'],
                $c['phone'],
                'Change password on first login at https://myworks.sbs/solar/login',
            ];
        }

        $sheet->fromArray($rows, null, 'A2');

        // Styling
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F172A'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        for ($i = 1; $i <= 7; $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $filePath = storage_path("app/SolarOps_Employee_Credentials_" . date('Y-m-d') . ".xlsx");
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        AuditLog::create([
            'auditable_type' => User::class,
            'auditable_id' => $adminId,
            'user_id' => $adminId,
            'event' => 'EMPLOYEE_CREDENTIAL_SHEET_DOWNLOADED',
            'description' => "Admin downloaded employee credentials sheet with " . count($credentials) . " temporary passwords",
            'ip_address' => request()->ip(),
        ]);

        return $filePath;
    }
}
