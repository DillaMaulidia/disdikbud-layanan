<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'nama_lengkap',
        'nomor_telepon',
        'email',
        'sasaran_pengaduan',
        'hal_diadukan',
        'bukti_pendukung',
        'status',
    ];
}
