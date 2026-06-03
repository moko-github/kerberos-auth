<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete()->after('email');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role_id')) {
            $hasForeignKey = collect(Schema::getForeignKeys('users'))
                ->contains(fn (array $fk) => in_array('role_id', $fk['columns'], true));

            if ($hasForeignKey) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropForeign(['role_id']);
                });
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }

        Schema::dropIfExists('roles');
    }
};
