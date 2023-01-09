<?php
$is_admin = current_user_can('manage_options');
wp_enqueue_style('new_style', plugins_url('/assets/style.css', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');

if ($is_admin) {
    wp_enqueue_style('jquery-ui-slider', plugins_url('/assets/jquery-ui.min.css', QRVC_PLUGIN_FILE_URL), false, '1.13.2', 'all');
    wp_enqueue_script('jquery');
    wp_enqueue_script('new_script', plugins_url('/assets/generate.js', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_script('jquery-ui-autocomplete');
    wp_enqueue_script('jquery-ui-touch', plugins_url('/assets/jquery.ui.touch-punch.min.js', QRVC_PLUGIN_FILE_URL), false, '0.2.3', 'all');
}

get_header();

if (!@$_GET['code'] and !$is_admin) {
    wp_redirect(get_site_url());
}


$code = @$_GET['code'];
$qrCodeVoucher = new QrCodevoucher($code);

$price_symbol = get_option('qr_voucher_currency_symbol');

the_content('');

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

    $decimal_price = get_option('qr_voucher_decimal_price');

    if (!$qrCodeVoucher->voucher) {
        $html .= "<a href='" . site_url('/qr-voucher-generate/') . "' class='qrvc-btn w-150 m-auto'>" . __('Add New', 'qrvc') . "</a>";
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
echo $html;

get_footer();
?>