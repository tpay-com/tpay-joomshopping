<?php

/*
 * Created by tpay.com
 */

use tpayLibs\src\_class_tpay\Notifications\BasicNotificationHandler;

include_once 'loader.php';

class TransactionNotification extends BasicNotificationHandler
{
    public function __construct($secret, $id)
    {
        $this->merchantSecret = $secret;
        $this->merchantId = (int)$id;
        parent::__construct();
    }

}
