<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kerberos')) {
                $table->string('kerberos')->nullable()->unique()->after('email');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'kerberos')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['kerberos']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kerberos');
        });
    }
};
