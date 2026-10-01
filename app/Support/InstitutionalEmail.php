<?php

namespace App\Support;

use Illuminate\Support\Str;

final class InstitutionalEmail
{
    public static function forStudent(string $firstName, string $lastName, string $indexNumber): string
    {
        return implode('.', [
            self::normalize($firstName),
            self::normalize($lastName),
            self::normalize($indexNumber),
        ]).'@'.config('app.email_domain');
    }

    private static function normalize(string $value): string
    {
        $value = str_replace(['Đ', 'đ'], ['Dj', 'dj'], trim($value));

        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->toString();
    }
}
