<?php

namespace App\Support;

class TemporaryPassword
{
    private const UPPERCASE = 'ABCDEFGHJKLMNPQRSTUVWXYZ';

    private const LOWERCASE = 'abcdefghijkmnopqrstuvwxyz';

    private const NUMBERS = '23456789';

    public static function generate(int $length = 14): string
    {
        $characters = [
            self::randomCharacter(self::UPPERCASE),
            self::randomCharacter(self::LOWERCASE),
            self::randomCharacter(self::NUMBERS),
        ];
        $availableCharacters = self::UPPERCASE.self::LOWERCASE.self::NUMBERS;

        while (count($characters) < max(8, $length)) {
            $characters[] = self::randomCharacter($availableCharacters);
        }

        for ($index = count($characters) - 1; $index > 0; $index--) {
            $replacementIndex = random_int(0, $index);
            [$characters[$index], $characters[$replacementIndex]] = [$characters[$replacementIndex], $characters[$index]];
        }

        return implode('', $characters);
    }

    private static function randomCharacter(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }
}
