<?php

function create_voucher_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . "qr_vouchers";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      code varchar(254) NOT NULL,
      price varchar(254) NOT NULL,
      status tinyint(1) NOT NULL,
      created_at datetime NOT NULL,
      PRIMARY KEY id (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(QRVC_PLUGIN_FILE_URL, 'create_voucher_table');

function create_voucher_log_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . "qr_voucher_logs";
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
      id bigint(20) NOT NULL AUTO_INCREMENT,
      voucher_id bigint(20) NOT NULL,
      price varchar(254) NOT NULL,
      created_at datetime NOT NULL,
      PRIMARY KEY id (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(QRVC_PLUGIN_FILE_URL, 'create_voucher_log_table');


function qrvc_where_date_query($query, $table_field, $date, $date_format = 'd.m.Y', $table_date_format = 'Y-m-d')
{
    $date = DateTime::createFromFormat($date_format, $date);
    if ($date !== false) {
        $query .= str_contains($query, 'WHERE') ? ' AND ' : 'WHERE ';
        $query .= "{$table_field} LIKE '%{$date->format($table_date_format)}%' ";
    }
    return $query;
}

function qrvc_where_between_date_query($query, $table_field, $from_date, $to_date, $date_format = 'd.m.Y', $table_date_format = 'Y-m-d')
{
    $from_date = DateTime::createFromFormat($date_format, $from_date);
    $to_date = DateTime::createFromFormat($date_format, $to_date);
    if ($from_date !== false and $to_date !== false) {
        $query .= str_contains($query, 'WHERE') ? ' AND ' : 'WHERE ';
        $query .= "{$table_field} between '{$from_date->format($table_date_format)}' and '{$to_date->modify('+1 day')->format($table_date_format)}' ";
    }
    return $query;
}