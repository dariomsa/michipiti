<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'email_slack')) {
                $table->string('email_slack')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'slack_user_id')) {
                $table->string('slack_user_id', 32)->nullable()->unique()->after('email_slack');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'slack_user_id')) {
                $table->dropColumn('slack_user_id');
            }

            if (Schema::hasColumn('users', 'email_slack')) {
                $table->dropColumn('email_slack');
            }
        });
    }
};
