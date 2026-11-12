<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Configure Super Admin (Password: SAdmin@123$$)
        $superAdmin = User::where('role', 'super_admin')->first();

        if ($superAdmin) {
            $superAdmin->update([
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@solar.local',
                'employee_code' => 'sadmin',
                'password' => Hash::make('SAdmin@123$$'),
                'role' => 'super_admin',
                'can_manage_content' => true,
                'first_login_completed' => true,
                'password_change_required' => false,
                'status' => 'active',
            ]);
        } else {
            $superAdmin = User::create([
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@solar.local',
                'employee_code' => 'sadmin',
                'password' => Hash::make('SAdmin@123$$'),
                'role' => 'super_admin',
                'designation' => 'Executive Director & Super Admin',
                'can_manage_content' => true,
                'first_login_completed' => true,
                'password_change_required' => false,
                'status' => 'active',
            ]);
        }

        // 2. Configure Admin Operations (Password: Admin@123$$)
        $opsAdmin = User::where('username', 'admin')->orWhere('email', 'admin@solar.local')->where('id', '!=', $superAdmin->id)->first();

        if ($opsAdmin) {
            $opsAdmin->update([
                'name' => 'Operations Admin',
                'username' => 'admin',
                'email' => 'admin@solar.local',
                'employee_code' => 'ADM-001',
                'password' => Hash::make('Admin@123$$'),
                'role' => 'admin_operations',
                'can_manage_content' => false,
                'first_login_completed' => true,
                'password_change_required' => false,
                'status' => 'active',
            ]);
        } else {
            $opsAdmin = User::create([
                'name' => 'Operations Admin',
                'username' => 'admin',
                'email' => 'admin@solar.local',
                'employee_code' => 'ADM-001',
                'password' => Hash::make('Admin@123$$'),
                'role' => 'admin_operations',
                'designation' => 'Admin Operations Director',
                'can_manage_content' => false,
                'first_login_completed' => true,
                'password_change_required' => false,
                'status' => 'active',
            ]);
        }

        // Associate both with companies in company_user
        $companies = Company::all();
        foreach ($companies as $company) {
            DB::table('company_user')->updateOrInsert(
                ['company_id' => $company->id, 'user_id' => $superAdmin->id],
                ['role_in_company' => 'super_admin', 'is_primary' => true, 'updated_at' => now(), 'created_at' => now()]
            );

            DB::table('company_user')->updateOrInsert(
                ['company_id' => $company->id, 'user_id' => $opsAdmin->id],
                ['role_in_company' => 'admin_operations', 'is_primary' => true, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Safe rollback without data loss
    }
};
