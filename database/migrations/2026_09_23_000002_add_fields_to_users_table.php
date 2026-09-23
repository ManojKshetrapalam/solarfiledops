<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('engineer'); // 'admin', 'engineer'
            $table->string('employee_code')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('designation')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('status')->default('active'); // 'active', 'inactive'
            $table->string('profile_photo_path')->nullable();
            $table->date('joining_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'role',
                'employee_code',
                'phone',
                'designation',
                'company_id',
                'status',
                'profile_photo_path',
                'joining_date',
            ]);
        });
    }
};
