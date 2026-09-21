<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengaduanLampiran extends Model
{
    public const SOURCE_PELAPOR = 'pelapor';

    public const SOURCE_OPERATOR = 'operator';

    protected $fillable = [
        'pengaduan_id',
        'sumber',
        'path',
        'nama_asli',
        'mime_type',
    ];

    public function pengaduan(): BelongsTo
    {
        return $this->belongsTo(Pengaduan::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/')
            || in_array(strtolower(pathinfo($this->path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
