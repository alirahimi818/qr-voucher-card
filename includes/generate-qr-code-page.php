<?php
include(plugin_dir_path(QRVC_PLUGIN_FILE_URL) . '/includes/phpqrcode/qrlib.php');
$url = site_url('/qr-voucher-show/?code=' . $_GET['string']);
return QRcode::png($url);
?>