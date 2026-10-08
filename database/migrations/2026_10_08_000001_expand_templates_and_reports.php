<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('report_template_slug')->nullable()->after('code');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->change();
            $table->foreignId('customer_id')->nullable()->change();
            $table->foreignId('site_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('report_template_slug');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable(false)->change();
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreignId('site_id')->nullable(false)->change();
        });
    }
};
