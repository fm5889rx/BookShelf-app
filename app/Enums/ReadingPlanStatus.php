<?php

namespace App\Enums;

/**
 * Advanced:
 * 読書計画のステータスのenum宣言
 */
enum ReadingPlanStatus: string
{
    case NoPlan = '未計画';
    case Inactive = '未読書';
    case Active = '読書中';
    case Completed = '読了';
    case Expired = '期限超過';

    public function badgeClass(): string
    {
        return match ($this) {
            self::NoPlan => 'bg-red-600',
            self::Inactive => 'bg-gray-600',
            self::Active => 'bg-green-600',
            self::Completed => 'bg-gray-600',
            self::Expired => 'bg-yellow-600',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NoPlan => '未計画',
            self::Inactive => '未読書',
            self::Active => '読書中',
            self::Completed => '読了',
            self::Expired => '期限超過',
        };
    }
}
