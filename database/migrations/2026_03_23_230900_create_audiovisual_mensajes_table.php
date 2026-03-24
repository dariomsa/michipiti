<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audiovisual_mensajes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('audiovisual_id')->constrained('audiovisuales')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reply_to_id')->nullable()->constrained('audiovisual_mensajes')->nullOnDelete();
            $table->string('tipo', 30)->default('COMENTARIO');
            $table->text('mensaje');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['audiovisual_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audiovisual_mensajes');
    }
};
