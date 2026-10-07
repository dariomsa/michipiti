<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fin_semana')) {
            Schema::table('fin_semana', function (Blueprint $table): void {
                if (! Schema::hasColumn('fin_semana', 'deportes_user_id')) {
                    $table->foreignId('deportes_user_id')->nullable()->after('grupo')->constrained('users')->nullOnDelete();
                }
            });

            if (Schema::hasColumn('fin_semana', 'deportes')) {
                DB::statement("
                    UPDATE fin_semana fs
                    INNER JOIN users u ON u.name = fs.deportes AND u.activo = 1
                    SET fs.deportes_user_id = u.id
                    WHERE fs.deportes IS NOT NULL
                      AND fs.deportes <> ''
                      AND fs.deportes_user_id IS NULL
                ");

                Schema::table('fin_semana', function (Blueprint $table): void {
                    $table->dropColumn('deportes');
                });
            }
        }

        if (Schema::hasTable('calendario_especial')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                if (! Schema::hasColumn('calendario_especial', 'deportes_user_id')) {
                    $table->foreignId('deportes_user_id')->nullable()->after('grupo')->constrained('users')->nullOnDelete();
                }
            });

            if (Schema::hasColumn('calendario_especial', 'deportes')) {
                DB::statement("
                    UPDATE calendario_especial ce
                    INNER JOIN users u ON u.name = ce.deportes AND u.activo = 1
                    SET ce.deportes_user_id = u.id
                    WHERE ce.deportes IS NOT NULL
                      AND ce.deportes <> ''
                      AND ce.deportes_user_id IS NULL
                ");

                Schema::table('calendario_especial', function (Blueprint $table): void {
                    $table->dropColumn('deportes');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fin_semana')) {
            Schema::table('fin_semana', function (Blueprint $table): void {
                if (Schema::hasColumn('fin_semana', 'deportes_user_id')) {
                    $table->dropConstrainedForeignId('deportes_user_id');
                }

                if (! Schema::hasColumn('fin_semana', 'deportes')) {
                    $table->string('deportes', 150)->nullable()->after('grupo');
                }
            });
        }

        if (Schema::hasTable('calendario_especial')) {
            Schema::table('calendario_especial', function (Blueprint $table): void {
                if (Schema::hasColumn('calendario_especial', 'deportes_user_id')) {
                    $table->dropConstrainedForeignId('deportes_user_id');
                }

                if (! Schema::hasColumn('calendario_especial', 'deportes')) {
                    $table->string('deportes', 150)->nullable()->after('grupo');
                }
            });
        }
    }
};
