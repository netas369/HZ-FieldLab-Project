<?php

namespace App\Enums;

enum TurbineStatus: int
{
    case Normal = 100;
    case Idle = 200;
    case Maintenance = 300;
    case Error = 400;
    case GridFault = 500;

    public function label(): string
    {
        return match($this) {
            self::Normal => 'Normal Operation',
            self::Idle => 'Idle (low/high wind)',
            self::Maintenance => 'Maintenance Mode',
            self::Error => 'Fault/Error',
            self::GridFault => 'Grid Fault (external issue)',
        };
    }
}
