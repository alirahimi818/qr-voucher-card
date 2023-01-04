<?php
/*
Plugin Name: QR-Code Voucher Card
Plugin URI: https://github.com/alirahimi818/qr-voucher-card
Description: generate QR-Code for Voucher Card.
Author: Ali Rahimi
Version: 1.0
Author URI: https://alirahimi818.ir
*/

define('QRVC_PLUGIN_FILE_URL', __FILE__);
define('QRVC_PLUGIN_BASE_URL', plugin_dir_path(__FILE__));

require_once(QRVC_PLUGIN_BASE_URL . 'includes/database.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/pages.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/QrCodeVoucher.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/shortcode.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/admin-vouchers.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/admin-voucher-logs.php');
require_once(QRVC_PLUGIN_BASE_URL . 'includes/setting.php');

function qrvc_run_default_setting()
{
    update_option('qr_voucher_currency_symbol', get_option('qr_voucher_currency_symbol') ?? '$');
    update_option('qr_voucher_decimal_price', get_option('qr_voucher_decimal_price') ?? '2');
    update_option('qr_voucher_step_price', get_option('qr_voucher_step_price') ?? '0.5');
    update_option('qr_voucher_max_price', get_option('qr_voucher_max_price') ?? '200');
    update_option('qr_voucher_default_price', get_option('qr_voucher_default_price') ?? '10');
    update_option('qr_voucher_default_price_buttons', get_option('qr_voucher_default_price_buttons') ?? '10,20,30,40,50');
    update_option('qr_voucher_date_format', get_option('qr_voucher_date_format') ?? 'D. d.m.Y H:i');
}

register_activation_hook(QRVC_PLUGIN_FILE_URL, 'qrvc_run_default_setting');

function qrvc_load_textdomain()
{
    load_textdomain('qrvc', QRVC_PLUGIN_BASE_URL . 'languages/qrvc-' . get_locale() . '.mo');
}
add_action('init', 'qrvc_load_textdomain');