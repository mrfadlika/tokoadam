<?php
/**
 * Formatting Helpers
 */

/**
 * Format number as Rupiah
 */
function formatRupiah($amount, $withPrefix = true) {
    $formatted = number_format((float)$amount, 0, ',', '.');
    return $withPrefix ? 'Rp ' . $formatted : $formatted;
}

/**
 * Format date to Indonesian format
 * Input: 2026-04-22  Output: 22 April 2026
 */
function formatDate($date) {
    if (empty($date)) return '-';
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day $month $year";
}

/**
 * Format date with Indonesian day name
 * Input: 2026-04-22  Output: Rabu / 22 April 2026
 */
function formatDateWithDay($date) {
    if (empty($date)) return '-';
    $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $timestamp = strtotime($date);
    return $days[(int)date('w', $timestamp)] . ' / ' . formatDate($date);
}

/**
 * Format datetime to short Indonesian format
 * Input: 2026-04-22 15:30:00  Output: 22 Apr 2026 15:30
 */
function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    $months = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    $timestamp = strtotime($datetime);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    $time = date('H:i', $timestamp);
    return "$day $month $year $time";
}

/**
 * Format relative time (e.g., "2 jam lalu")
 */
function timeAgo($datetime) {
    $now = time();
    $diff = $now - strtotime($datetime);
    
    if ($diff < 60) return 'Baru saja';
    if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
    
    return formatDate($datetime);
}

/**
 * Truncate text
 */
function truncate($text, $length = 50) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Format grouped period label for charts / reports
 */
function formatPeriodLabel($periodKey, $grouping = 'day', $short = false) {
    if (empty($periodKey)) return '-';

    $months = [
        1 => ['long' => 'Januari', 'short' => 'Jan'],
        2 => ['long' => 'Februari', 'short' => 'Feb'],
        3 => ['long' => 'Maret', 'short' => 'Mar'],
        4 => ['long' => 'April', 'short' => 'Apr'],
        5 => ['long' => 'Mei', 'short' => 'Mei'],
        6 => ['long' => 'Juni', 'short' => 'Jun'],
        7 => ['long' => 'Juli', 'short' => 'Jul'],
        8 => ['long' => 'Agustus', 'short' => 'Agu'],
        9 => ['long' => 'September', 'short' => 'Sep'],
        10 => ['long' => 'Oktober', 'short' => 'Okt'],
        11 => ['long' => 'November', 'short' => 'Nov'],
        12 => ['long' => 'Desember', 'short' => 'Des'],
    ];

    if ($grouping === 'year') {
        return $periodKey;
    }

    if ($grouping === 'month') {
        [$year, $month] = array_pad(explode('-', $periodKey), 2, null);
        if (empty($month)) return $periodKey;
        $monthName = $months[(int)$month][$short ? 'short' : 'long'] ?? $month;
        return $monthName . ' ' . $year;
    }

    $timestamp = strtotime($periodKey);
    if ($timestamp === false) return $periodKey;

    $day = date('j', $timestamp);
    $monthName = $months[(int)date('n', $timestamp)][$short ? 'short' : 'long'];
    $year = date('Y', $timestamp);

    if ($short) {
        return $day . ' ' . $monthName;
    }

    return $day . ' ' . $monthName . ' ' . $year;
}

/**
 * Generate random color class for badges
 */
function categoryColor($category) {
    $colors = ['badge-primary', 'badge-success', 'badge-warning', 'badge-info', 'badge-danger'];
    $index = abs(crc32($category ?? 'default')) % count($colors);
    return $colors[$index];
}
