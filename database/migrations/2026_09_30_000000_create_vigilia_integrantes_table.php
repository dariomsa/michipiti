<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vigilia_integrantes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vigilia_id')->constrained('vigilia')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['vigilia_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vigilia_integrantes');
    }
};
