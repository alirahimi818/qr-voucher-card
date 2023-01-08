<?php

function qrvc_make_voucher_pages()
{
    $pages = [
        'qr-voucher-generate' => [
            'title' => __('Voucher Page', 'qrvc'),
            'status' => 'private'
        ],
        'qr-voucher-generate-qr' => [
            'title' => __('Voucher QR Generator', 'qrvc'),
            'status' => 'private'
        ],
        'qr-voucher-show' => [
            'title' => __('Voucher Show', 'qrvc'),
            'status' => 'publish'
        ]
    ];
    foreach ($pages as $slug => $option) {
        $args = array(
            'name' => $slug,
            'post_type' => 'page',
            'post_status' => $option['status'],
            'posts_per_page' => 1
        );
        if (!get_posts($args)) {
            $new_post = array(
                'post_title' => wp_strip_all_tags($option['title']),
                'post_name' => $slug,
                'post_content' => '',
                'post_status' => $option['status'],
                'post_author' => 1,
                'post_type' => 'page',
            );
            wp_insert_post($new_post);
        }
    }
}

register_activation_hook(QRVC_PLUGIN_FILE_URL, 'qrvc_make_voucher_pages');

add_action('wp', 'rqvc_redirect_private_page_to_login');
function rqvc_redirect_private_page_to_login()
{
    $queried_object = get_queried_object();
    if (isset($queried_object->post_status) && 'private' === $queried_object->post_status && !current_user_can('manage_options')) {
        wp_redirect(wp_login_url(get_permalink($queried_object->ID)));
    }
}

add_filter('page_template', 'qrvc_qr_code_generate_page_template', 1);
function qrvc_qr_code_generate_page_template($page_template)
{
    if (is_page('qr-voucher-generate-qr')) {
        $page_template = QRVC_PLUGIN_BASE_URL . 'includes/generate-qr-code-page.php';
    }
    return $page_template;
}

add_filter('page_template', 'qrvc_bonus_profile_page_template', 1);
function qrvc_bonus_profile_page_template($page_template)
{
    if (is_page('qr-voucher-generate')) {
        $page_template = QRVC_PLUGIN_BASE_URL . 'includes/voucher-generate-page.php';
    }
    return $page_template;
}

add_filter('page_template', 'qrvc_bonus_generate_page_template', 1);
function qrvc_bonus_generate_page_template($page_template)
{
    if (is_page('qr-voucher-show')) {
        $page_template = QRVC_PLUGIN_BASE_URL . 'includes/voucher-show-page.php';
    }
    return $page_template;
}