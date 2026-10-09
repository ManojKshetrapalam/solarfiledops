<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Customer;
use App\Models\DataImport;
use App\Models\Site;
use App\Models\User;
use App\Services\DataMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DataMigrationAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_first_login_forces_password_change(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->password = Hash::make('TempAdmin@123');
        $admin->password_change_required = true;
        $admin->first_login_completed = false;
        $admin->username = 'admin';
        $admin->save();

        // 1. Admin logs in with username
        $response = $this->post(route('login.post'), [
            'login' => 'admin',
            'password' => 'TempAdmin@123',
        ]);

        $response->assertRedirect(route('auth.change-password'));
        $this->assertAuthenticatedAs($admin);

        // 2. Admin cannot view dashboard until password is changed
        $dashResponse = $this->get(route('admin.dashboard'));
        $dashResponse->assertRedirect(route('auth.change-password'));

        // 3. Attempting weak password fails validation
        $weakResponse = $this->post(route('auth.change-password.update'), [
            'password' => 'simple',
            'password_confirmation' => 'simple',
        ]);
        $weakResponse->assertSessionHasErrors(['password']);

        // 4. Submitting compliant strong password succeeds
        $strongResponse = $this->post(route('auth.change-password.update'), [
            'password' => 'SolarOps#Secure2026',
            'password_confirmation' => 'SolarOps#Secure2026',
        ]);

        $strongResponse->assertRedirect(route('admin.dashboard'));
        $admin->refresh();

        $this->assertFalse((bool) $admin->password_change_required);
        $this->assertTrue((bool) $admin->first_login_completed);
        $this->assertNotNull($admin->password_changed_at);
        $this->assertTrue(Hash::check('SolarOps#Secure2026', $admin->password));

        // 5. Subsequent dashboard access is permitted
        $this->get(route('admin.dashboard'))->assertStatus(200);
    }

    public function test_employee_onboarding_credential_lifecycle(): void
    {
        $company = Company::first();

        // Create an employee with temporary credentials
        $employee = new User([
            'name' => 'Ravi Kumar',
            'email' => 'ravi.test@solar.local',
            'username' => 'ravi',
            'password' => Hash::make('ravi123'),
            'employee_code' => 'ENG-901',
            'phone' => '+91 98888 11111',
            'designation' => 'Field Technician',
            'role' => 'engineer',
            'company_id' => $company->id,
            'status' => 'active',
        ]);
        $employee->setTemporaryPassword('ravi123', 7);
        $employee->password_change_required = true;
        $employee->first_login_completed = false;
        $employee->save();

        // 1. Employee logs in using username
        $loginResponse = $this->post(route('login.post'), [
            'login' => 'ravi',
            'password' => 'ravi123',
        ]);
        $loginResponse->assertRedirect(route('auth.change-password'));

        // 2. Employee cannot access engineer dashboard
        $this->get(route('engineer.dashboard'))->assertRedirect(route('auth.change-password'));

        // 3. Employee changes password
        $changeResponse = $this->post(route('auth.change-password.update'), [
            'password' => 'FieldTech@Secure2026',
            'password_confirmation' => 'FieldTech@Secure2026',
        ]);

        $changeResponse->assertRedirect(route('engineer.dashboard'));
        $employee->refresh();

        $this->assertFalse((bool) $employee->password_change_required);
        $this->assertTrue((bool) $employee->first_login_completed);
        $this->assertNull($employee->temporary_password_encrypted);
        $this->assertNull($employee->getDecryptedTemporaryPassword());
        $this->assertNotNull($employee->temporary_password_consumed_at);

        // 4. Dashboard is now accessible
        $this->get(route('engineer.dashboard'))->assertStatus(200);
    }

    public function test_expired_temporary_credentials_are_rejected(): void
    {
        $company = Company::first();

        $employee = new User([
            'name' => 'Expired User',
            'email' => 'expired@solar.local',
            'username' => 'expireduser',
            'password' => Hash::make('expired123'),
            'employee_code' => 'ENG-902',
            'phone' => '+91 98888 22222',
            'designation' => 'Field Technician',
            'role' => 'engineer',
            'company_id' => $company->id,
            'status' => 'active',
        ]);
        $employee->setTemporaryPassword('expired123', -1); // expired 1 day ago
        $employee->password_change_required = true;
        $employee->save();

        $loginResponse = $this->post(route('login.post'), [
            'login' => 'expireduser',
            'password' => 'expired123',
        ]);

        $loginResponse->assertSessionHasErrors(['login']);
        $this->assertGuest();
    }

    public function test_security_boundaries_prevent_non_admins_from_accessing_migration(): void
    {
        $engineer = User::where('role', 'engineer')->first();
        $this->actingAs($engineer);

        $response = $this->get(route('admin.data-management.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_download_migration_templates_and_zip(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->password_change_required = false;
        $admin->save();

        $this->actingAs($admin);

        // 1. Migration Center dashboard
        $this->get(route('admin.data-management.index'))->assertStatus(200);

        // 2. Templates view
        $this->get(route('admin.data-management.templates'))->assertStatus(200);

        // 3. Download single template
        $templateResponse = $this->get(route('admin.data-management.download-template', 'employees'));
        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // 4. Download all templates ZIP
        $zipResponse = $this->get(route('admin.data-management.download-all-templates'));
        $zipResponse->assertStatus(200);
        $this->assertStringContainsString('application/zip', $zipResponse->headers->get('Content-Type'));
    }

    public function test_employee_excel_import_preview_and_confirm_workflow(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->password_change_required = false;
        $admin->save();

        $this->actingAs($admin);

        // 1. Build a valid test Excel spreadsheet for Employees
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $headers = [
            'Employee Code', 'First Name', 'Last Name', 'Mobile', 'Email',
            'Designation', 'Company Code', 'Joining Date', 'Role', 'Status'
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $rows = [
            ['ENG-801', 'Karthik', 'Murthy', '+91 91111 22222', 'karthik@test.solar', 'Field Tech', 'SOE', '2025-01-10', 'engineer', 'Active'],
            ['ENG-802', 'Sunil', 'Gowda', '+91 91111 33333', 'sunil@test.solar', 'Field Tech', 'SOE', '2025-01-15', 'engineer', 'Active'],
        ];
        $sheet->fromArray($rows, null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_emp_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'Employees_Test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // 2. Post to Preview
        $previewResponse = $this->post(route('admin.data-management.preview'), [
            'import_type' => 'employees',
            'excel_file' => $uploadedFile,
        ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('admin.data_management.preview');
        $previewResponse->assertSee('Karthik Murthy');
        $previewResponse->assertSee('karthik'); // auto-generated username preview

        $previewData = $previewResponse->viewData('previewData');
        $this->assertEquals(2, $previewData['valid_count']);
        $this->assertEquals(0, $previewData['error_count']);
        $this->assertEquals('valid', $previewData['rows'][0]['status']);
        $this->assertEquals('ENG-801', $previewData['rows'][0]['raw']['Employee Code']);

        $previewKey = $previewResponse->viewData('previewKey');
        $this->assertNotEmpty($previewKey);

        // 3. Confirm Import
        $confirmResponse = $this->post(route('admin.data-management.confirm'), [
            'preview_key' => $previewKey,
        ]);

        $confirmResponse->assertStatus(200);
        $confirmResponse->assertViewIs('admin.data_management.completed');
        $confirmResponse->assertSee('Import Completed Successfully');
        $confirmResponse->assertSee('karthik');
        $confirmResponse->assertSee('karthik123'); // temporary password handover preview

        // 4. Verify employees in database
        $karthik = User::where('employee_code', 'ENG-801')->firstOrFail();
        $this->assertEquals('karthik@test.solar', $karthik->email);
        $this->assertEquals('karthik', $karthik->username);
        $this->assertTrue((bool) $karthik->password_change_required);
        $this->assertEquals('karthik123', $karthik->getDecryptedTemporaryPassword());

        // 5. Download Credential Sheet
        $credSheetResponse = $this->get(route('admin.data-management.download-credential-sheet'));
        $credSheetResponse->assertStatus(200);
        $credSheetResponse->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Verify audit log created for credential sheet download
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'EMPLOYEE_CREDENTIAL_SHEET_DOWNLOADED',
            'user_id' => $admin->id,
        ]);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_customer_and_site_migration_resolves_relations(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->password_change_required = false;
        $admin->save();

        $this->actingAs($admin);

        // 1. Create a spreadsheet for Customers
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $headers = [
            'Customer Code', 'Company Code', 'Customer Name', 'Contact Person',
            'Mobile', 'Email', 'Address', 'Status'
        ];
        $sheet->fromArray([$headers], null, 'A1');

        $rows = [
            ['CUST-MIG-1', 'SOE', 'Migrated Solar Enterprise', 'Rajan Patel', '+91 93333 44444', 'rajan@migsolar.com', 'Outer Ring Road, Bangalore', 'Active'],
        ];
        $sheet->fromArray($rows, null, 'A2');

        $tempPath = tempnam(sys_get_temp_dir(), 'test_cust_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $uploadedFile = new UploadedFile($tempPath, 'Customers_Test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        // Preview & Confirm
        $previewResponse = $this->post(route('admin.data-management.preview'), [
            'import_type' => 'customers',
            'excel_file' => $uploadedFile,
        ]);
        $previewKey = $previewResponse->viewData('previewKey');

        $confirmResponse = $this->post(route('admin.data-management.confirm'), [
            'preview_key' => $previewKey,
        ]);
        $confirmResponse->assertStatus(200);

        // Check customer exists
        $customer = Customer::where('customer_code', 'CUST-MIG-1')->firstOrFail();
        $this->assertEquals('Migrated Solar Enterprise', $customer->name);
        $this->assertEquals('SOE', $customer->company->code);

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    public function test_admin_can_reveal_and_regenerate_temporary_credentials(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->password_change_required = false;
        $admin->save();

        $this->actingAs($admin);

        $company = Company::first();
        $employee = new User([
            'name' => 'Vijay Prakash',
            'email' => 'vijay@solar.local',
            'username' => 'vijay',
            'password' => Hash::make('vijay123'),
            'employee_code' => 'ENG-905',
            'phone' => '+91 97777 66666',
            'designation' => 'Solar Rooftop Specialist',
            'role' => 'engineer',
            'company_id' => $company->id,
            'status' => 'active',
        ]);
        $employee->setTemporaryPassword('vijay123', 7);
        $employee->password_change_required = true;
        $employee->save();

        // 1. Reveal credential via endpoint
        $revealResponse = $this->get(route('admin.data-management.reveal-credential', $employee->id));
        $revealResponse->assertStatus(200);
        $revealResponse->assertJson([
            'success' => true,
            'password' => 'vijay123',
            'is_consumed' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'EMPLOYEE_CREDENTIAL_VIEWED',
            'auditable_id' => $employee->id,
        ]);

        // 2. Audit copy credential
        $copyResponse = $this->post(route('admin.data-management.audit-copy-credential'), [
            'employee_id' => $employee->id,
        ]);
        $copyResponse->assertStatus(200);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'EMPLOYEE_CREDENTIAL_COPIED',
            'auditable_id' => $employee->id,
        ]);

        // 3. Regenerate temporary password
        $regenResponse = $this->post(route('admin.employees.regenerate-temp-password', $employee->id));
        $regenResponse->assertSessionHas('revealed_temp_password', 'vijay123');

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'EMPLOYEE_CREDENTIAL_REGENERATED',
            'auditable_id' => $employee->id,
        ]);
    }
}
