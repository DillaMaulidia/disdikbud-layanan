<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'bidang'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const BIDANG = [
        'Bidang Umum',
        'Bidang Pembinaan PAUD dan Pendidikan Non Formal',
        'Bidang Pembinaan SD',
        'Bidang Pembinaan SMP',
        'Bidang Kebudayaan',
        'Bidang Ketenagaan',
        'UPTD Tekkomdik',
    ];

    public const BIDANG_CODES = [
        'Bidang Umum' => 'UMUM',
        'Bidang Pembinaan PAUD dan Pendidikan Non Formal' => 'PAUD',
        'Bidang Pembinaan SD' => 'SD',
        'Bidang Pembinaan SMP' => 'SMP',
        'Bidang Kebudayaan' => 'KBD',
        'Bidang Ketenagaan' => 'KTG',
        'UPTD Tekkomdik' => 'TEK',
        'Sekretariat' => 'SEK',
    ];

    public const ROLE_USER = 'user';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_OPERATOR = 'operator';

    public static function roles(): array
    {
        return [
            self::ROLE_USER,
            self::ROLE_ADMIN,
            self::ROLE_OPERATOR,
        ];
    }

    public static function bidang(): array
    {
        return self::BIDANG;
    }

    public static function validBidang(): array
    {
        return [...self::BIDANG, 'Sekretariat'];
    }

    public static function bidangCode(string $bidang): string
    {
        return self::BIDANG_CODES[$bidang];
    }

    public function pengaduans(): HasMany
    {
        return $this->hasMany(Pengaduan::class);
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
