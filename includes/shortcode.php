<?php

add_action('wp', 'rqvc_redirect_private_page_to_login');
function rqvc_redirect_private_page_to_login()
{
    $queried_object = get_queried_object();
    if (isset($queried_object->post_status) && 'private' === $queried_object->post_status && !current_user_can('manage_options')) {
        wp_redirect(wp_login_url(get_permalink($queried_object->ID)));
    }
}

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

function qr_voucher_generate_page_func($atts)
{
    if (current_user_can('manage_options')) {
        wp_enqueue_style('new_style', plugins_url('/assets/style.css', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
        wp_enqueue_style('jquery-ui-slider', plugins_url('/assets/jquery-ui.min.css', QRVC_PLUGIN_FILE_URL), false, '1.13.2', 'all');
        wp_enqueue_script('new_script', plugins_url('/assets/generate.js', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
        wp_enqueue_script('jquery-ui-slider');

        $price_symbol = get_option('qr_voucher_currency_symbol');
        $decimal_price = get_option('qr_voucher_decimal_price');
        $max_price = get_option('qr_voucher_max_price');
        $step_price = get_option('qr_voucher_step_price');
        $default_price = get_option('qr_voucher_default_price');
        $default_buttons_price = get_option('qr_voucher_default_price_buttons');
        $buttons = explode(',', $default_buttons_price);
        $html_buttons = "";
        if (@$buttons[0]) {
            foreach ($buttons as $price) {
                $html_buttons .= "<button type='button' class='qrvc-btn qr-default-price-btn btn-orange' data-price='{$price}'>{$price_symbol}{$price}</button>";
            }
        }

        $html = "";
        $html .= "<div class='qr-generate-page'>
                    <div class='print-white-page'></div>
                    <div class='barcode-img-area'><img width='100%' src='" . plugins_url('/assets/error-qr.png', QRVC_PLUGIN_FILE_URL) . "'><div class='barcode-img-bottom-area'><div class='barcode-area'></div><div class='barcode-date-area'></div></div></div>
                    <div class='qr-control-box'>
                        <div class='qr-control-area flex-col'>
                            <div class='qr-control-box-title'>" . __('Enter Voucher Price', 'qrvc') . "</div>
                            <input type='text' class='qr-price-amount'>
                            <div class='qr-price-slider' data-symbol='{$price_symbol}' data-decimal='{$decimal_price}' data-max='{$max_price}' data-step='{$step_price}' data-default-value='{$default_price}' ></div>
                        </div>
                        <div class='qr-control-area'>
                            {$html_buttons}
                        </div>
                        <div class='qr-control-area flex-col'>
                            <button type='button' class='qrvc-btn new-qr-btn'>" . __('Generate QR-Code', 'qrvc') . "</button>
                            <button type='button' onclick='print();' class='qrvc-btn btn-gray qr-print-btn' style='display: none'>" . __('Print QR-Code', 'qrvc') . "</button>
                        </div>
                    </div>
                  </div>
        ";
        return $html;
    }
    wp_redirect(wp_login_url(site_url('/qr-voucher-show/')));
}

add_shortcode('QR_VOUCHER_GENERATE', 'qr_voucher_generate_page_func');

function qr_voucher_generate_qr_page_func($atts)
{
    if (@$_GET['string']) {
        include(plugin_dir_path(QRVC_PLUGIN_FILE_URL) . '/includes/phpqrcode/qrlib.php');
        $url = site_url('/qr-voucher-show/?code=' . $_GET['string']);
        return QRcode::png($url);
    }
}

add_shortcode('QR_VOUCHER_GENERATE_QR', 'qr_voucher_generate_qr_page_func');


function qr_voucher_show_page_func($atts)
{
    $is_admin = current_user_can('manage_options');
    if (!@$_GET['code'] and !$is_admin) {
        wp_redirect(get_site_url());
    }

    wp_enqueue_style('new_style', plugins_url('/assets/style.css', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');

    $code = @$_GET['code'];
    $qrCodeVoucher = new QrCodevoucher($code);

    $price_symbol = get_option('qr_voucher_currency_symbol');
    $html = "";

    if ($qrCodeVoucher->voucher) {

        $logs = $qrCodeVoucher->getVoucherLogs();
        $voucher_left_over = $qrCodeVoucher->getVoucherLeftOver();
        $html_logs = "";
        if ($logs) {
            $html_logs = "<div class=''>" . __('Used History: ', 'qrvc') . "</div>";
            foreach ($logs as $log) {
                $log_date = wp_date(get_option('qr_voucher_date_format'), strtotime($log->created_at));
                $html_logs .= "<div class='qr-voucher-show-box-log-item'>{$log_date} : {$price_symbol}{$log->price}</div>";
            }
        }
        $html .= "<div class='qr-voucher-show-box " . ($voucher_left_over <= 0 ? "border-red" : "") . "'>
                            <div class='qr-voucher-show-box-code'>{$code}</div>
                            <div class='qr-voucher-show-box-area'>
                                <div class=''>" . __('Voucher Price: ', 'qrvc') . $price_symbol . "{$qrCodeVoucher->voucher->price}</div>
                                <div class=''>" . __('Voucher Balance: ', 'qrvc') . $price_symbol . "{$voucher_left_over}</div>
                                {$html_logs}
                            </div>
                      </div>";
    } else {
        if (!$is_admin) {
            wp_redirect(get_site_url());
        }
    }

    if ($is_admin) {
        wp_enqueue_style('jquery-ui-slider', plugins_url('/assets/jquery-ui.min.css', QRVC_PLUGIN_FILE_URL), false, '1.13.2', 'all');
        wp_enqueue_script('new_script', plugins_url('/assets/generate.js', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-autocomplete');

        $decimal_price = get_option('qr_voucher_decimal_price');

        if (!$qrCodeVoucher->voucher) {
            $html .= "<div class='qr-control-area flex-col'>
                        <div class='text-center failed-color'>" . __('Voucher not found!', 'qrvc') . "</div>
                        <form action='' class='qr-find-form qr-control-area flex-col position-relative'>
                            <input type='text' class='input-code' name='code' value='{$code}' placeholder='" . __('Enter the voucher code', 'qrvc') . "'>
                            <input type='text' class='input-code-hidden' value=''>
                            <button type='submit' class='qrvc-btn search-qr-btn'>" . __('Search', 'qrvc') . "</button>
                        </form>
                     </div>";

        } else {
            $html .= "<div class='qr-generate-page'>
                    <div class='qr-control-box'>
                        <div class='qr-control-area flex-col'>
                            <div class='qr-control-box-title'>" . __('Enter the amount', 'qrvc') . "</div>
                            <input type='hidden' class='qr-voucher-code' value='{$code}'>
                            <input type='text' class='qr-price-amount'>
                            <div class='qr-price-slider' data-symbol='{$price_symbol}' data-decimal='{$decimal_price}' data-max='{$voucher_left_over}' data-step='0.1' data-default-value='0' ></div>
                        <div class='qr-control-area'>
                            <button type='button' class='qrvc-btn btn-orange minus' data-symbol='{$price_symbol}'>- {$price_symbol}0.10</button>
                            <button type='button' class='qrvc-btn btn-orange plus' data-symbol='{$price_symbol}' data-max='{$voucher_left_over}'>+ {$price_symbol}0.10</button>
                        </div>
                        </div>
                        <div class='qr-control-area flex-col'>
                            <button type='button' class='qrvc-btn save-qr-btn'>" . __('Save', 'qrvc') . "</button>
                            <div class='qr-voucher-update-success-message success-color'></div>
                        </div>
                    </div>
                  </div>
        ";
        }
    }
    return $html;
}

add_shortcode('QR_VOUCHER_SHOW', 'qr_voucher_show_page_func');