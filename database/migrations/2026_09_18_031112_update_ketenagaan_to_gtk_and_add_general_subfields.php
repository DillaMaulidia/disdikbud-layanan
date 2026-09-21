<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('bidang', 'Bidang Umum')
            ->update(['bidang' => 'Subbagian Umum, Kepegawaian, dan Aset']);

        DB::table('users')
            ->where('bidang', 'Bidang Ketenagaan')
            ->update(['bidang' => 'Bidang GTK']);

        DB::table('pengaduans')
            ->where('sasaran_pengaduan', 'Bidang Umum')
            ->update(['sasaran_pengaduan' => 'Subbagian Umum, Kepegawaian, dan Aset']);

        DB::table('pengaduans')
            ->where('sasaran_pengaduan', 'Bidang Ketenagaan')
            ->update(['sasaran_pengaduan' => 'Bidang GTK']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('bidang', 'Subbagian Umum, Kepegawaian, dan Aset')
            ->update(['bidang' => 'Bidang Umum']);

        DB::table('users')
            ->where('bidang', 'Bidang GTK')
            ->update(['bidang' => 'Bidang Ketenagaan']);

        DB::table('pengaduans')
            ->where('sasaran_pengaduan', 'Subbagian Umum, Kepegawaian, dan Aset')
            ->update(['sasaran_pengaduan' => 'Bidang Umum']);

        DB::table('pengaduans')
            ->where('sasaran_pengaduan', 'Bidang GTK')
            ->update(['sasaran_pengaduan' => 'Bidang Ketenagaan']);
    }
};
