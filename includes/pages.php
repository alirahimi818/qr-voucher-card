<?php

function qrvc_make_voucher_pages() {
    $pages = [
      'qr-voucher-generate' => [
        'shortcode' => '[QR_VOUCHER_GENERATE]',
        'title' => __('Voucher Page', 'qrvc'),
        'status' => 'private'
      ],
      'qr-voucher-generate-qr' => [
        'shortcode' => '[QR_VOUCHER_GENERATE_QR]',
        'title' => __('Voucher QR Generator', 'qrvc'),
        'status' => 'private'
      ],
      'qr-voucher-show' => [
        'shortcode' => '[QR_VOUCHER_SHOW]',
        'title' => __('Voucher Show', 'qrvc'),
        'status' => 'publish'
      ]
    ];
    foreach($pages as $slug => $option){
      $args = array(
        'name'   => $slug,
        'post_type'   => 'page',
        'post_status' => $option['status'],
        'posts_per_page' => 1
      );
      if( !get_posts($args) ){
            $new_post = array(
            'post_title'    => wp_strip_all_tags( $option['title'] ),
            'post_name'  => $slug,
            'post_content'  => $option['shortcode'],
            'post_status'   => $option['status'],
            'post_author'   => 1,
            'post_type'     => 'page',
            );
            wp_insert_post( $new_post );
      }
    }
}

register_activation_hook(QRVC_PLUGIN_FILE_URL, 'qrvc_make_voucher_pages');