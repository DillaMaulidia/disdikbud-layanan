<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->string('bukti_pendukung')->nullable()->after('hal_diadukan');
        });
    }

    public function down(): void
    {
        Schema::table('pengaduans', function (Blueprint $table): void {
            $table->dropColumn('bukti_pendukung');
        });
    }
};
