<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class VentovaResource extends Resource
{
    protected static function isOwner(Model $record): bool
    {
        return $record->user_id === Auth::id() || Auth::user()?->isAdmin();
    }

    public static function canView(Model $record): bool
    {
        return static::isOwner($record);
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::isOwner($record);
    }

    public static function canDeleteAny(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::isOwner($record);
    }

    public static function canForceDeleteAny(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::isOwner($record);
    }

    public static function canRestore(Model $record): bool
    {
        return static::isOwner($record);
    }

    public static function canRestoreAny(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function canReplicate(Model $record): bool
    {
        return static::isOwner($record);
    }
}
