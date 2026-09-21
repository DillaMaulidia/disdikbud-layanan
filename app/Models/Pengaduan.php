<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengaduan extends Model
{
    protected $fillable = [
        'nomor_tiket',
        'user_id',
        'operator_id',
        'nama_lengkap',
        'nomor_telepon',
        'email',
        'sasaran_pengaduan',
        'hal_diadukan',
        'tanggapan_operator',
        'bukti_pendukung',
        'bukti_operator',
        'status',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function lampirans(): HasMany
    {
        return $this->hasMany(PengaduanLampiran::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->role === User::ROLE_ADMIN) {
            return;
        }

        if ($user->role === User::ROLE_OPERATOR) {
            $query->where('sasaran_pengaduan', $user->bidang ?? '__no_bidang__');

            return;
        }

        $query->where('user_id', $user->id);
    }
}
