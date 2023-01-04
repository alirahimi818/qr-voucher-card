<?php

class QrCodeVoucher
{
    public $code;
    public $voucher;
    public $voucher_logs;

    public function __construct($code = null)
    {
        $this->code = $code ?: $this->generateCode();
        $this->getVoucher();
    }

    public function getVoucherLogs()
    {
        if ($this->voucher) {
            global $wpdb;
            $table_name = $wpdb->prefix . "qr_voucher_logs";
            $this->voucher_logs = $wpdb->get_results("SELECT * FROM $table_name WHERE voucher_id = '{$this->voucher->id}' ORDER BY id DESC");
            return $this->voucher_logs;
        }
        return null;
    }

    public function getVoucher()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "qr_vouchers";
        $results = $wpdb->get_results("SELECT * FROM $table_name WHERE code = '{$this->code}' ORDER BY id DESC LIMIT 1");
        if ($results and @$results[0]) {
            $this->voucher = $results[0];
            return $results[0];
        } else {
            $this->voucher = null;
            return null;
        }
    }

    public function generateCode($length = 10)
    {
        $code = md5(uniqid(rand()));
        if (strlen($code) > $length) {
            $code = substr($code, 0, $length);
        }
        $this->code = $code;
        return $code;
    }

    public function createVoucher($price)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "qr_vouchers";

        if (!$this->voucher) {
            $date = current_time('mysql');
            $wpdb->insert($table_name, ['code' => $this->code, 'price' => $price, 'status' => 1, 'created_at' => $date]);
            return $this->code;
        }
        $this->generateCode();
        $this->getVoucher();
        $this->createVoucher($price);
    }

    public function updateVoucher($price)
    {
        if ($this->voucher) {
            global $wpdb;
            $voucher_log_table_name = $wpdb->prefix . "qr_voucher_logs";
            $voucher_table_name = $wpdb->prefix . "qr_vouchers";

            $voucher_price = $this->voucher->price;
            $spent = $wpdb->get_var("SELECT SUM(price) FROM $voucher_log_table_name WHERE voucher_id = '{$this->voucher->id}'");
            $left_over = number_format($spent + $price, 2, '.', '');
            $date = current_time('mysql');
            if ($price <= $voucher_price and $left_over <= $voucher_price) {
                $wpdb->insert($voucher_log_table_name, ['voucher_id' => $this->voucher->id, 'price' => $price, 'created_at' => $date]);
                if ($left_over == $voucher_price) {
                    $wpdb->update($voucher_table_name, ['status' => 0], ['id' => $this->voucher->id]);
                }

                return true;
            }
        }
        return false;
    }

    public function getVoucherLeftOver()
    {
        if ($this->voucher) {
            global $wpdb;
            $voucher_log_table_name = $wpdb->prefix . "qr_voucher_logs";

            $spent = $wpdb->get_var("SELECT SUM(price) FROM $voucher_log_table_name WHERE voucher_id = '{$this->voucher->id}'");
            return number_format($this->voucher->price - $spent, 2, '.', '');
        }
        return 0;
    }
}