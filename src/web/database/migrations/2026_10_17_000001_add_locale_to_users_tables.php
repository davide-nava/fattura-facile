<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->nullable()->after('email');
            }
        });

        if (Schema::hasTable('customer_portal_users')) {
            Schema::table('customer_portal_users', function (Blueprint $table) {
                if (! Schema::hasColumn('customer_portal_users', 'locale')) {
                    $table->string('locale', 10)->nullable()->after('email');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'locale')) {
                $table->dropColumn('locale');
            }
        });

        if (Schema::hasTable('customer_portal_users')) {
            Schema::table('customer_portal_users', function (Blueprint $table) {
                if (Schema::hasColumn('customer_portal_users', 'locale')) {
                    $table->dropColumn('locale');
                }
            });
        }
    }
};
