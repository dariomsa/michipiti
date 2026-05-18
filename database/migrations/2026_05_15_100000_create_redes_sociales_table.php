<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redes_sociales', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 80);
            $table->string('slug', 80)->unique();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        $now = now();

        $redes = [
            'Instagram',
            'Facebook',
            'TikTok',
            'X',
            'YouTube',
            'LinkedIn',
            'WhatsApp',
        ];

        DB::table('redes_sociales')->upsert(
            collect($redes)->map(fn (string $nombre) => [
                'nombre' => $nombre,
                'slug' => Str::slug($nombre),
                'activa' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all(),
            ['slug'],
            ['nombre', 'activa', 'updated_at'],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('redes_sociales');
    }
};
