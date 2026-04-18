<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait GeneratesAccessCodes
{
    protected static function generateUniqueAccessCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    protected static function generateUniqueShortCode(int $length = 6): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, $length));
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    protected function ensureAccessCode(): void
    {
        if (!$this->access_code) {
            $this->access_code = static::generateUniqueAccessCode();
        }
    }

    protected static function bootGeneratesAccessCodes(): void
    {
        static::creating(function ($model) {
            $model->ensureAccessCode();
        });

        static::updating(function ($model) {
            if ($model->isDirty('access_code') && !$model->access_code) {
                $model->access_code = static::generateUniqueAccessCode();
            }
        });
    }
}
