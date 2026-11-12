<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role_in_company'); // 'admin_operations', 'engineer'
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        // Upgrade legacy 'admin' role to 'super_admin'
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);

        // Backfill existing engineers who have an assigned company_id
        $users = DB::table('users')->whereNotNull('company_id')->get();
        foreach ($users as $user) {
            $companyExists = DB::table('companies')->where('id', $user->company_id)->exists();
            if ($companyExists) {
                $roleInCompany = $user->role === 'admin_operations' ? 'admin_operations' : 'engineer';
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $user->company_id, 'user_id' => $user->id],
                    [
                        'role_in_company' => $roleInCompany,
                        'is_primary' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Dropping company_user pivot in production is prohibited to prevent data loss.');
        }

        Schema::dropIfExists('company_user');
    }
};
