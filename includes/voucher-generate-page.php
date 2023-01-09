<?php

$favicon_url = "";
$html = "";

if (current_user_can('manage_options')) {
    $favicon_url = wp_get_attachment_image_url(get_option('qr_voucher_card_active_favicon_img'), 'full');

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

    $html .= "<a href='" . site_url('/qr-voucher-show/')."' class='qrvc-btn  w-150 m-auto'>" . __('Search', 'qrvc') . "</a>";
    $html .= "<div class='qr-generate-page'>
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
                            <button type='button' onclick='qrvc_print_qr_code();' class='qrvc-btn btn-gray qr-print-btn' style='display: none'>" . __('Print QR-Code', 'qrvc') . "</button>
                        </div>
                    </div>
                  </div>
        ";
} else {
    wp_redirect(wp_login_url(site_url('/qr-voucher-show/')));
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="msapplication-TileColor" content="#af0a1a">
    <meta name="theme-color" content="#ffffff">
    <link rel="shortcut icon" href="<?php echo $favicon_url ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicon_url ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $favicon_url ?>">
    <link rel="apple-touch-icon" href="<?php echo $favicon_url ?>">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo $favicon_url ?>">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $favicon_url ?>">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $favicon_url ?>">
    <title>Voucher Generate</title>
    <link rel="manifest" href="<?php echo plugins_url('/assets/pwa-manifest.json', QRVC_PLUGIN_FILE_URL) ?>">
    <link rel="stylesheet" href="<?php echo plugins_url('/assets/style.css', QRVC_PLUGIN_FILE_URL) ?>">
    <link rel="stylesheet" href="<?php echo plugins_url('/assets/jquery-ui.min.css', QRVC_PLUGIN_FILE_URL) ?>">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo plugins_url('/assets/pwa-sw.js', QRVC_PLUGIN_FILE_URL) ?>')
                    .then(function (register) {
                        console.log('PWA service worker ready');
                        register.update();
                    })
                    .catch(function (error) {
                        console.log('Register failed! Error:' + error);
                    });
            });
        }
    </script>
</head>
<body>
<div>
    <?php
    the_content('');
    echo $html;
    ?>
</div>
<script type="text/javascript" src="<?php echo plugins_url('/assets/jquery.min.js', QRVC_PLUGIN_FILE_URL) ?>"></script>
<script type="text/javascript" src="<?php echo plugins_url('/assets/jquery-ui.min.js', QRVC_PLUGIN_FILE_URL) ?>"></script>
<script type="text/javascript" src="<?php echo plugins_url('/assets/generate.js', QRVC_PLUGIN_FILE_URL) ?>"></script>
<script type="text/javascript" src="<?php echo plugins_url('/assets/jquery.ui.touch-punch.min.js', QRVC_PLUGIN_FILE_URL) ?>"></script>
</body>
</html>
