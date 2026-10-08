<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->boolean('password_change_required')->default(false)->after('password');
            $table->boolean('first_login_completed')->default(false)->after('password_change_required');
            $table->timestamp('password_changed_at')->nullable()->after('first_login_completed');
            $table->timestamp('last_login_at')->nullable()->after('password_changed_at');
            $table->text('temporary_password_encrypted')->nullable()->after('last_login_at');
            $table->timestamp('temporary_password_created_at')->nullable()->after('temporary_password_encrypted');
            $table->timestamp('temporary_password_expires_at')->nullable()->after('temporary_password_created_at');
            $table->timestamp('temporary_password_consumed_at')->nullable()->after('temporary_password_expires_at');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_code')->nullable()->index()->after('company_id');
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->string('site_code')->nullable()->index()->after('customer_id');
            $table->string('system_capacity')->nullable()->after('address');
        });

        Schema::create('data_imports', function (Blueprint $table) {
            $table->id();
            $table->string('import_type'); // companies, employees, customers, sites, installations, services, complaints, daily_work_reports, site_inspections, feedback
            $table->string('file_name');
            $table->string('file_path')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('status')->default('completed'); // 'pending', 'completed', 'failed'
            $table->json('summary_data')->nullable();
            $table->json('error_log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_imports');

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['site_code', 'system_capacity']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'password_change_required',
                'first_login_completed',
                'password_changed_at',
                'last_login_at',
                'temporary_password_encrypted',
                'temporary_password_created_at',
                'temporary_password_expires_at',
                'temporary_password_consumed_at',
            ]);
        });
    }
};
