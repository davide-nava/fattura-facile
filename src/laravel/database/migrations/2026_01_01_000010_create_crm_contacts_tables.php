<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type')->default('general'); // customer, supplier, lead, general
            $table->string('color')->nullable(); // hex color e.g. #3B82F6 or filament color
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'company_id', 'code']);
            $table->index(['tenant_id', 'company_id', 'type']);
        });

        Schema::create('contact_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->nullableMorphs('contactable'); // customer, supplier, lead
            $table->string('first_name');
            $table->string('last_name');
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'company_id', 'is_active']);
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('contact_group_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('contact_group_id')->constrained('contact_groups')->cascadeOnDelete();
            $table->morphs('member'); // customer, supplier, lead, contact_person
            $table->timestamps();

            $table->unique(['contact_group_id', 'member_type', 'member_id'], 'group_membership_unique');
            $table->index(['tenant_id', 'member_type', 'member_id'], 'group_membership_tenant_member_idx');
        });

        Schema::create('customer_portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('contact_person_id')->nullable()->constrained('contact_persons')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('access_token', 80)->nullable()->unique();
            $table->string('status')->default('active'); // active, suspended, pending
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('converted_customer_id')->nullable()->after('assigned_to')->constrained('customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable()->after('converted_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_customer_id']);
            $table->dropColumn(['converted_customer_id', 'converted_at']);
        });

        Schema::dropIfExists('customer_portal_users');
        Schema::dropIfExists('contact_group_memberships');
        Schema::dropIfExists('contact_persons');
        Schema::dropIfExists('contact_groups');
    }
};
