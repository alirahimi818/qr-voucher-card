<?php

function qr_voucher_card_plugin_register_settings()
{
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_currency_symbol', ['type' => 'string']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_decimal_price', ['type' => 'number']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_step_price', ['type' => 'number']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_max_price', ['type' => 'number']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_default_price', ['type' => 'number']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_default_price_buttons', ['type' => 'string']);
    register_setting('qr_voucher_card_plugin_options_group', 'qr_voucher_date_format', ['type' => 'string']);
}

add_action('admin_init', 'qr_voucher_card_plugin_register_settings');

function qr_voucher_card_plugin_setting_page()
{
    add_submenu_page('qr-voucher-card', 'QR Voucher Card Setting', 'Setting', 'manage_options', 'qr-voucher-card/setting', 'qr_voucher_card_plugin_setting_form');
}

add_action('admin_menu', 'qr_voucher_card_plugin_setting_page');

function qr_voucher_card_plugin_setting_form()
{
    ?>
    <div class="wrap">
        <h2><?php _e("QR Voucher Card Setting", "qrvc") ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('qr_voucher_card_plugin_options_group'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="qr_voucher_currency_symbol"><?php _e("Currency symbol", "qrvc") ?></label></th>
                    <td>
                        <input type='text' class="regular-text" id="qr_voucher_currency_symbol"
                               name="qr_voucher_currency_symbol"
                               value="<?php echo get_option('qr_voucher_currency_symbol'); ?>">
                        <div><?php _e("For example: $, €, £,...", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_decimal_price"><?php _e("Decimal price", "qrvc") ?></label></th>
                    <td>
                        <input type='number' class="regular-text" id="qr_voucher_decimal_price"
                               name="qr_voucher_decimal_price"
                               value="<?php echo get_option('qr_voucher_decimal_price'); ?>">
                        <div><?php _e("Decimal number for voucher price.", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_step_price"><?php _e("Step Price", "qrvc") ?></label></th>
                    <td>
                        <input type='number' step="0.1" class="regular-text" id="qr_voucher_step_price"
                               name="qr_voucher_step_price" value="<?php echo get_option('qr_voucher_step_price'); ?>">
                        <div><?php _e("Step price in slider create voucher page.", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_max_price"><?php _e("Max Price", "qrvc") ?></label></th>
                    <td>
                        <input type='number' class="regular-text" id="qr_voucher_max_price"
                               name="qr_voucher_max_price" value="<?php echo get_option('qr_voucher_max_price'); ?>">
                        <div><?php _e("Max price in create voucher page.", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_default_price"><?php _e("Default Price", "qrvc") ?></label></th>
                    <td>
                        <input type='number' class="regular-text" id="qr_voucher_default_price"
                               name="qr_voucher_default_price"
                               value="<?php echo get_option('qr_voucher_default_price'); ?>">
                        <div><?php _e("Default price in create voucher page.", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_default_price_buttons"><?php _e("Default Buttons Price", "qrvc") ?></label></th>
                    <td>
                        <input type='text' class="regular-text" id="qr_voucher_default_price_buttons"
                               name="qr_voucher_default_price_buttons"
                               value="<?php echo get_option('qr_voucher_default_price_buttons'); ?>">
                        <div><?php _e("Default Buttons price in create voucher page.", "qrvc") ?></div>
                        <div><?php _e("Separate the amount of each button with a comma.", "qrvc") ?></div>
                    </td>
                </tr>
                <tr>
                    <th><label for="qr_voucher_date_format"><?php _e("Date Format", "qrvc") ?></label></th>
                    <td>
                        <input type='text' class="regular-text" id="qr_voucher_date_format"
                               name="qr_voucher_date_format"
                               value="<?php echo get_option('qr_voucher_date_format'); ?>">
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>

    </div>
<?php } ?>