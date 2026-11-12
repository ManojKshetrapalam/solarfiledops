<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->nullable()->after('email'); // 'domestic', 'non_domestic', 'institutions'
        });

        // Update service types and report templates with client standard names
        DB::table('service_types')->where('code', 'routine_service')->update(['name' => 'Regular service & Maintainance']);
        DB::table('service_types')->where('code', 'breakdown_maintenance')->update(['name' => 'Complaints & Maintainance']);
        DB::table('service_types')->where('code', 'daily_work_report')->update(['name' => 'Daily work report']);
        DB::table('service_types')->where('code', 'customer_feedback')->update(['name' => 'Customer feedback']);
        DB::table('service_types')->where('code', 'installation_commissioning')->update(['name' => 'Installation & Commissioning']);

        if (Schema::hasTable('report_templates')) {
            DB::table('report_templates')->where('slug', 'daily_work_report')->update(['name' => 'Daily work report']);
            DB::table('report_templates')->where('slug', 'customer_feedback')->update(['name' => 'Customer feedback']);
            DB::table('report_templates')->where('slug', 'installation_commissioning')->update(['name' => 'Installation & Commissioning']);
        }
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Dropping customer classification in production is prohibited to prevent data loss.');
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_type']);
        });
    }
};
