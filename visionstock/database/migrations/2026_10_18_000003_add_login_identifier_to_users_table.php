<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('login_identifier')->nullable()->unique();
            $table->string('email')->nullable()->change();
        });

        DB::table('users')
            ->whereNull('login_identifier')
            ->update(['login_identifier' => DB::raw('email')]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('login_identifier')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNull('email')
            ->update(['email' => DB::raw('login_identifier')]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['login_identifier']);
            $table->dropColumn(['username', 'login_identifier']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
