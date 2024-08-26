<?php

use Carbon\Carbon;

// Format date time
if (!function_exists('formatDateTime')) {
    function formatDateTime($date, $format = 'd M Y'): ?string
    {
        return $date ? Carbon::parse($date)->format($format) : null;
    }
}
