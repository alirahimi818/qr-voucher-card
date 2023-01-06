<?php

add_action("wp_ajax_generate_qr_voucher_card", "generate_qr_voucher_card");
add_action("wp_ajax_nopriv_generate_qr_voucher_card", "generate_qr_voucher_card");
function generate_qr_voucher_card()
{
    if (current_user_can('manage_options')) {
        $price = @$_REQUEST['price'];
        if ($price and is_numeric($price)) {
            $qrCodeVoucher = new QrCodeVoucher();
            $code = $qrCodeVoucher->createVoucher(number_format($price, 2, '.', ''));
            echo '{"code":"' . $code . '","url":"' . site_url('/qr-voucher-generate-qr/?string=' . $code) . '","date":"' . wp_date(get_option('qr_voucher_date_format')) . '"}';
        }
    }
    wp_die();
}

add_action("wp_ajax_update_qr_voucher_card", "update_qr_voucher_card");
add_action("wp_ajax_nopriv_update_qr_voucher_card", "update_qr_voucher_card");
function update_qr_voucher_card()
{
    if (current_user_can('manage_options')) {
        $code = @$_REQUEST['code'];
        $price = @$_REQUEST['price'];
        if ($code and $price and is_numeric($price)) {
            $qrCodeVoucher = new QrCodeVoucher($code);
            if ($qrCodeVoucher->voucher) {
                $qrCodeVoucher->updateVoucher(number_format($price, 2, '.', ''));
            }
            echo '{"message":"' . __('Success Saved.', 'qrvc') . '"}';
        }
    }
    wp_die();
}

add_action("wp_ajax_find_qr_voucher_card", "find_qr_voucher_card");
add_action("wp_ajax_nopriv_find_qr_voucher_card", "find_qr_voucher_card");
function find_qr_voucher_card()
{
    if (current_user_can('manage_options')) {
        $code = @$_REQUEST['code'];
        if ($code) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qr_vouchers";
            $results = $wpdb->get_results("SELECT * FROM $table_name WHERE code LIKE '{$code}%' AND status = 1 ORDER BY id DESC");
            if ($results and @$results[0]) {
                echo json_encode($results);
            }
        }
    }
    wp_die();
}