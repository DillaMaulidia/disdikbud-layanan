<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaduan_lampirans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengaduan_id')->constrained('pengaduans')->cascadeOnDelete();
            $table->string('sumber', 20);
            $table->string('path');
            $table->string('nama_asli');
            $table->string('mime_type', 100);
            $table->timestamps();
        });

        foreach (DB::table('pengaduans')->whereNotNull('bukti_pendukung')->get(['id', 'bukti_pendukung']) as $pengaduan) {
            DB::table('pengaduan_lampirans')->insert([
                'pengaduan_id' => $pengaduan->id,
                'sumber' => 'pelapor',
                'path' => $pengaduan->bukti_pendukung,
                'nama_asli' => basename($pengaduan->bukti_pendukung),
                'mime_type' => 'application/octet-stream',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('pengaduans')->whereNotNull('bukti_operator')->get(['id', 'bukti_operator']) as $pengaduan) {
            DB::table('pengaduan_lampirans')->insert([
                'pengaduan_id' => $pengaduan->id,
                'sumber' => 'operator',
                'path' => $pengaduan->bukti_operator,
                'nama_asli' => basename($pengaduan->bukti_operator),
                'mime_type' => 'application/octet-stream',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan_lampirans');
    }
};
