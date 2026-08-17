<?php

/*
Component: Setting Model (Hardened)
File Path: app/Models/Setting.php
Company: Ygrace Tech
Author: Ibrahim Olalekan

Purpose:
Provides access to dynamic configuration values stored in the settings table.
Supports typed values, controlled groups, and flags for safe exposure/editing.

Architecture:
- Officers manage settings via Filament (edit only, no create/delete).
- Application code retrieves values using typed accessor.
- Prevents hardcoding of institutional data, fees, deadlines, branding.
- Flags ensure sensitive/system settings are not exposed.

Status: ✅ Production Ready
Version: 1.4 (added Schema guard in get(), typed settings accessor, JSON override fix, v5 compatible)
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'is_public',
        'is_editable',
        'sort_order',
    ];

    /**
     * Typed accessor helper for settings.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        // ✅ Guard against missing table during migrate:fresh or early boot
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $setting = static::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        return $setting->castValue();
    }

    /**
     * Cast the stored value based on the type column.
     *
     * @return mixed
     */
    public function castValue()
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);

            case 'integer':
                return (int) $this->value;

            case 'float':
                return (float) $this->value;

            case 'date':
                return $this->value ? Carbon::parse($this->value) : null;

            case 'json':
                // ✅ Return full decoded JSON object for overrides
                return $this->value
                    ? json_decode($this->value, true)
                    : null;

            case 'string':
            default:
                // Always coerce to string for safety
                return is_array($this->value) ? reset($this->value) : (string) $this->value;
        }
    }

    /**
     * Scope for public settings (safe to expose to portal).
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for editable settings (officers can update).
     */
    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }
}
