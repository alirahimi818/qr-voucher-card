<?php

add_action('admin_menu', 'add_qr_voucher_menu_to_admin');

function add_qr_voucher_menu_to_admin()
{
    add_menu_page(__('QR-Code Vouchers', 'qrvc'), __('Voucher Card', 'qrvc'), 'manage_options', 'qr-voucher-card', 'qr_voucher_admin_page', 'dashicons-money-alt', 44);
}

function qrvc_get_voucher_balance($voucher_id, $voucher_price)
{
    global $wpdb;
    $voucher_log_table_name = $wpdb->prefix . "qr_voucher_logs";

    $spent = $wpdb->get_var("SELECT SUM(price) FROM $voucher_log_table_name WHERE voucher_id = '{$voucher_id}'");
    $results = $wpdb->get_results("SELECT * FROM $voucher_log_table_name WHERE voucher_id = '{$voucher_id}'");
    return ['logs' => $results, 'used' => number_format($spent, 2, '.', ''), 'left_over' => number_format($voucher_price - $spent, 2, '.', '')];
}

function qr_voucher_admin_page()
{
    wp_enqueue_script('new_script', plugins_url('/assets/admin.js', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
    wp_enqueue_style('new_style', plugins_url('/assets/admin.css', QRVC_PLUGIN_FILE_URL), false, '1.0', 'all');
    wp_enqueue_script('jquery-ui-datepicker');

    $date_format = get_option('qr_voucher_date_format');
    $price_symbol = get_option('qr_voucher_currency_symbol');

    global $wpdb;
    $voucher_table_name = $wpdb->prefix . "qr_vouchers";

    $num = 20;
    $from = 0;
    $pagination = 1;
    if (@$_GET['pagination']) {
        $pagination = (int)$_GET['pagination'];
        $from = ($pagination - 1) * $num;
    }

    $query = "FROM {$voucher_table_name} ";
    if (@$_GET['s']) {
        $query .= "WHERE code LIKE '%{$_GET['s']}%' ";
    }

    $to_date = wp_date('d.m.Y', strtotime("last day of this month"));
    $from_date = wp_date('d.m.Y', strtotime("first day of this month"));
    if (@$_GET['from_date'] and @$_GET['to_date']) {
        $to_date = $_GET['to_date'];
        $from_date = $_GET['from_date'];
    }
    $query = qrvc_where_between_date_query($query, "created_at", $from_date, $to_date);

    $count_query = "SELECT COUNT(*) " . $query;
    $query = "SELECT * " . $query;

    $items_count = $wpdb->get_var("{$count_query}");
    $items = $wpdb->get_results("{$query} ORDER BY id DESC LIMIT {$from},{$num}");
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('QR-Code Vouchers', 'qrvc') ?></h1>
        <a href="<?php echo site_url('/qr-voucher-generate/') ?>" target="_blank"
           class="page-title-action"><?php _e('Add New', 'qrvc') ?></a>
        <form action="" method="GET" class="qr-search-form">
            <input type="hidden" name="page" value="qr-voucher-card">
            <p class="search-box" style="margin-bottom: 10px;">
                <input type="text" id="search-input" name="s" value="" placeholder="<?php _e('Search', 'qrvc') ?>...">
                <input type="submit" id="search-submit" class="button" value="<?php _e('Search', 'qrvc') ?>"></p>
            <p class="search-box" style="margin: 0 20px 10px;">
                <input type="text" id="from-date-input" name="from_date" value="<?php echo @$_GET['from_date'] ?>"
                       placeholder="<?php _e('from: ', 'qrvc') ?>DD.MM.YYYY">
                <input type="text" id="to-date-input" name="to_date" value="<?php echo @$_GET['to_date'] ?>"
                       placeholder="<?php _e('to: ', 'qrvc') ?>DD.MM.YYYY">
                <input type="submit" id="date-submit" class="button" value="<?php _e('Search by date', 'qrvc') ?>"></p>
        </form>
        <div><?php echo @$_GET['from_date'] || @$_GET['to_date'] ? __('items count') . ': ' . $items_count : '' ?></div>
        <div class="print-block"><?php echo __('from: ', 'qrvc') . $from_date . ' - ' . __('to: ', 'qrvc') . $to_date ?></div>
        <table class="wp-list-table widefat striped table-view-list pagination-table">
            <thead>
            <tr>
                <th>ID</th>
                <th><?php _e('code', 'qrvc') ?></th>
                <th><?php _e('price', 'qrvc') ?></th>
                <th><?php _e('used', 'qrvc') ?></th>
                <th><?php _e('left over', 'qrvc') ?></th>
                <th><?php _e('status', 'qrvc') ?></th>
                <th><?php _e('date', 'qrvc') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($items)) {
                foreach ($items as $item) {
                    $logs = qrvc_get_voucher_balance($item->id, $item->price);
                    ?>
                    <tr>
                        <td><?php echo $item->id ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=qr-voucher-logs&s=' . $item->code) ?>"><?php echo $item->code ?></a>
                        </td>
                        <td><?php echo $price_symbol . $item->price ?></td>
                        <td class="red-color"><?php echo $price_symbol . $logs['used'] ?></td>
                        <td class="green-color"><?php echo $price_symbol . $logs['left_over'] ?></td>
                        <td><?php $item->status == 1 ? _e('active', 'qrvc') : _e('inactive', 'qrvc') ?></td>
                        <td><?php echo wp_date($date_format, strtotime($item->created_at)) ?></td>
                    </tr>
                <?php }
            } else {
                ?>
                <tr>
                    <td class="text-center red" colspan="5"><?php _e('Not found!', 'qrvc') ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="alignleft actions bulkactions">
            <button type="button" id="export-button"
                    class="button print-none qr-export-btn"><?php _e('export', 'qrvc') ?></button>
        </div>
    </div>
    <script>
        setTimeout(function () {
            table_pagination(<?php echo $items_count; ?>, <?php echo $num; ?>, <?php echo $pagination; ?>, "<?php _e('pages', 'qrvc'); ?>");
        }, 500)
        jQuery('#search-input').val('<?php echo @$_GET['s']; ?>');
        jQuery(function ($) {
            $('#from-date-input, #to-date-input').datepicker({
                dateFormat: "dd.mm.yy"
            });
        });
        jQuery('#export-button').click(function () {
            window.location.href = window.location.href + '&export=1'
        })
        <?php echo @$_GET['export'] ? 'print();' : '' ?>
    </script>
    <?php
}