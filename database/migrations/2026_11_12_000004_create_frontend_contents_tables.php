<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add can_manage_content permission flag to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'can_manage_content')) {
                $table->boolean('can_manage_content')->default(false)->after('role');
            }
        });

        // 2. Create frontend_contents table
        Schema::create('frontend_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->string('category')->index();
            $table->text('default_value');
            $table->text('custom_value')->nullable();
            $table->text('published_value')->nullable();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'company_id']);
        });

        // SQLite & standard partial unique indexes for handling NULL company_id uniqueness
        $connection = DB::connection()->getDriverName();
        if ($connection === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_frontend_contents_global ON frontend_contents("key") WHERE company_id IS NULL;');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS uq_frontend_contents_company ON frontend_contents("key", company_id) WHERE company_id IS NOT NULL;');
        } else {
            Schema::table('frontend_contents', function (Blueprint $table) {
                $table->unique(['key', 'company_id'], 'uq_frontend_contents_key_company');
            });
        }

        // 3. Create frontend_content_histories table
        Schema::create('frontend_content_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frontend_content_id')->constrained('frontend_contents')->cascadeOnDelete();
            $table->string('action'); // 'draft_saved', 'published', 'reset_to_default'
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['frontend_content_id', 'created_at']);
        });
    }

    public function down(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Dropping CMS content tables in production is prohibited to protect client customization data.');
        }

        Schema::dropIfExists('frontend_content_histories');
        Schema::dropIfExists('frontend_contents');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'can_manage_content')) {
                $table->dropColumn('can_manage_content');
            }
        });
    }
};
