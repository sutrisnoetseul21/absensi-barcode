<?php

if (!function_exists('tanggal_indonesia')) {
    function tanggal_indonesia($date)
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
    }
}

if (!function_exists('hari_tanggal_indonesia')) {
    function hari_tanggal_indonesia($date)
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('l, d F Y');
    }
}
