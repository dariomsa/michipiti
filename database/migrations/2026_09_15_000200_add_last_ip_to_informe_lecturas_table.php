<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informe_lecturas', function (Blueprint $table): void {
            $table->string('last_ip', 45)->nullable()->after('total_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('informe_lecturas', function (Blueprint $table): void {
            $table->dropColumn('last_ip');
        });
    }
};
