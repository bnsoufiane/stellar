<?php

class Helpers
{
    public static function format_date_display($date)
    {
        if (($date == "0000-00-00 00:00:00" || $date == null)) {
            return null;
        } else {
            return date_format(date_create($date), 'm/d/Y');
        }

    }

    public static function format_date_DB($date)
    {
        if (($date == "0000-00-00 00:00:00" || $date == null || empty($date))) {
            return null;
        } else {
            return date('Y-m-d', strtotime(str_replace('-', '/', $date)));
        }

    }

    public static function today_date_report_name()
    {
        return date_format(date_create(), 'Y_m_d');

    }

}