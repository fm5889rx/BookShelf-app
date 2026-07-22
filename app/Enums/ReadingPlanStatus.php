<?php

namespace App\Enums;

enum ReadingPlanStatus: string
{
    case NOPLAN   = '未計画';
    case INACTIVE = '未読書';
    case ACTIVE   = '読書中';
    case COMPLETE = '読了';
    case PAUSE    = '一時停止';
}
