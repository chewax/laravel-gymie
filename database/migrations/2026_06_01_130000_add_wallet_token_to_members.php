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
        Schema::table('members', function (Blueprint $table) {
            $table->string('wallet_token', 64)->nullable()->unique()->after('code');
        });

        // Backfill a random, unguessable token for existing members.
        foreach (DB::table('members')->whereNull('wallet_token')->pluck('id') as $id) {
            DB::table('members')->where('id', $id)->update(['wallet_token' => Str::random(40)]);
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['wallet_token']);
            $table->dropColumn('wallet_token');
        });
    }
};
