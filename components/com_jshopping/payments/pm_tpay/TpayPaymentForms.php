<?php

/**
 * Created by tpay.com.
 * Date: 16.11.2017
 * Time: 15:23
 */

use tpayLibs\src\_class_tpay\PaymentForms\PaymentBasicForms;

require_once 'loader.php';

class PaymentForm extends PaymentBasicForms
{
    public function __construct($secret, $id)
    {
        $this->merchantSecret = $secret;
        $this->merchantId = (int)$id;
        parent::__construct();
    }
}
