<?php

namespace App\Services;

use App\Models\Programme;
use Illuminate\Support\Facades\Config;

class EffectiveFeeResolver
{
    /**
     * Resolve the effective application fee for a programme.
     *
     * Rule:
     * - If programme.application_fee is set (non-null), use it.
     * - Otherwise, fall back to global admissions.application_fee.
     *
     * All values are stored in kobo (integer).
     *
     * @param Programme $programme
     * @return int|null Fee in kobo, or null if not configured
     */
    public function resolve(Programme $programme): ?int
    {
        if ($programme->application_fee !== null) {
            return (int) $programme->application_fee;
        }

        $globalFee = Config::get('admissions.application_fee');

        return $globalFee !== null ? (int) $globalFee : null;
    }

    /**
     * Resolve fee in naira (numeric).
     *
     * @param Programme $programme
     * @return float|null Fee in naira, or null if not configured
     */
    public function resolveInNaira(Programme $programme): ?float
    {
        $fee = $this->resolve($programme);
        return $fee !== null ? $fee / 100 : null;
    }

    /**
     * Resolve fee formatted for display.
     *
     * @param Programme $programme
     * @return string Fee formatted as ₦xx,xxx.xx or "—" if not configured
     */
    public function resolveFormatted(Programme $programme): string
    {
        $fee = $this->resolve($programme);
        return $fee !== null
            ? '₦' . number_format($fee / 100, 2)
            : '—';
    }
}
