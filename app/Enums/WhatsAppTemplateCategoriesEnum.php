<?php

namespace App\Enums;

enum WhatsAppTemplateCategoriesEnum: string
{
    case AUTHENTICATION = 'AUTHENTICATION';
    case UTILITY = 'UTILITY';
    case MARKETING = 'MARKETING';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
