<?php

if (!function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($date)
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
    }
}
