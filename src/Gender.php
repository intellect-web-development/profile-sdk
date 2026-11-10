<?php

declare(strict_types=1);

namespace IWD\ProfileSdk;

enum Gender: string
{
    case Secret = 'secret';
    case Male = 'male';
    case Female = 'female';

    /** @return list<string> */
    public static function states(): array
    {
        return array_column(self::cases(), 'value');
    }
}
