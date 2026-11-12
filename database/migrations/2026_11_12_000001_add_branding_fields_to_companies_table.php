<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('secondary_logo_path')->nullable()->after('logo_path');
            $table->string('website')->nullable()->after('email');
            $table->string('registration_details')->nullable()->after('website');
            $table->string('certification_info')->nullable()->after('registration_details');
            $table->text('report_footer_info')->nullable()->after('report_header_info');
            $table->string('brand_color')->nullable()->after('report_footer_info');
        });

        // Seed initial branding profile for default/primary company if exists
        $primary = DB::table('companies')->orderBy('id')->first();
        if ($primary) {
            DB::table('companies')->where('id', $primary->id)->update([
                'legal_name' => 'Sun on Earth Solar Solutions Pvt Ltd',
                'certification_info' => 'ISO 9001:2015 Certified',
                'website' => 'www.sunonearth.com',
                'phone' => '+91-9141930009',
                'email' => 'service@sunonearthsolar.com',
                'report_header_info' => 'ISO 9001:2015 Certified | www.sunonearth.com',
            ]);
        }
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Dropping branding columns from companies in production is prohibited to prevent data loss.');
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'secondary_logo_path',
                'website',
                'registration_details',
                'certification_info',
                'report_footer_info',
                'brand_color',
            ]);
        });
    }
};
