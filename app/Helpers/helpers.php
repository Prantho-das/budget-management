<?php

if (!function_exists('get_setting')) {
  function get_setting($key, $default = null)
  {
    return \App\Models\SystemSetting::get($key, $default);
  }
}
if (!function_exists('fiscal_years')) {
    /**
     * Generate Bangladesh fiscal years (July 1 – June 30) in format: YYYY-YY
     *
     * @param int $previous    Number of previous fiscal years to include (default: 0)
     * @param int $next        Number of future fiscal years to include (default: 0)
     * @param \Carbon\Carbon|string|null $date Base date to calculate from (default: today)
     *
     * @return array List of fiscal years as strings: ['2023-24', '2024-25', '2025-26', ...]
     */
    function fiscal_years(int $previous = 0, int $next = 0, $date = null): array
    {
        $carbon = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::today();

        $currentMonth = $carbon->month;
        $currentYear = $carbon->year;

        // Determine the current fiscal year's start year
        $currentFiscalStartYear = $currentMonth >= 7 ? $currentYear : $currentYear - 1;

        $years = [];

        // Add previous years
        for ($i = $previous; $i > 0; $i--) {
            $startYear = $currentFiscalStartYear - $i;
            $endYearShort = substr($startYear + 1, -2);
            $years[] = $startYear . '-' . $endYearShort;
        }

        // Add current fiscal year
        $endYearShort = substr($currentFiscalStartYear + 1, -2);
        $years[] = $currentFiscalStartYear . '-' . $endYearShort;

        // Add next years
        for ($i = 1; $i <= $next; $i++) {
            $startYear = $currentFiscalStartYear + $i;
            $endYearShort = substr($startYear + 1, -2);
            $years[] = $startYear . '-' . $endYearShort;
        }

        return $years;
    }
}

if (!function_exists('current_fiscal_year')) {
    /**
     * Shortcut: Get only the current fiscal year
     */
    function current_fiscal_year($date = null): string
    {
        return fiscal_years(0, 0, $date)[0] ?? '';
    }
}

if (!function_exists('get_active_fiscal_year_id')) {
    /**
     * Get the database ID of the current active fiscal year based on Bangladesh timing (July-June)
     */
    function get_active_fiscal_year_id()
    {
        $name = current_fiscal_year();
        $fy = \App\Models\FiscalYear::where('name', $name)->first();
        if (!$fy) {
            // Fallback to active status if name does not match exactly
            $fy = \App\Models\FiscalYear::where('status', true)->latest()->first();
        }
        return $fy ? $fy->id : null;
    }
}