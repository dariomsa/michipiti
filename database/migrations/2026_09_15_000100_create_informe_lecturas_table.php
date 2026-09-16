<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informe_lecturas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('informe_id')->constrained('informes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('aperturas')->default(0);
            $table->unsignedBigInteger('total_seconds')->default(0);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['informe_id', 'user_id']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informe_lecturas');
    }
};
