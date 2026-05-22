<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->string('slack_file_id', 120)->nullable()->after('archivo_final_size');
            $table->string('slack_permalink', 600)->nullable()->after('slack_file_id');
            $table->string('slack_private_url', 600)->nullable()->after('slack_permalink');
        });
    }

    public function down(): void
    {
        Schema::table('audiovisuales', function (Blueprint $table): void {
            $table->dropColumn([
                'slack_file_id',
                'slack_permalink',
                'slack_private_url',
            ]);
        });
    }
};
