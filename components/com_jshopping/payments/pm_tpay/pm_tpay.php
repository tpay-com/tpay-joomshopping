<?php

use tpayLibs\src\_class_tpay\Utilities\TException;
use tpayLibs\src\_class_tpay\Utilities\Util;

require_once 'loader.php';
require_once 'TpayPaymentForms.php';
require_once 'TransactionNotification.php';

class pm_tpay extends PaymentRoot
{
    //function call in admin
    public function showAdminFormParams($params)
    {
        $array_params = array(
            'seller_id',
            'seller_secret',
            'transaction_end_status',
            'transaction_pending_status',
            'transaction_failed_status',
            'proxy'
        );
        foreach ($array_params as $key) {
            if (!isset($params[$key])) {
                $params[$key] = '';
            }
        }
        $orders = JSFactory::getModel('orders', 'JshoppingModel'); //admin model
        include_once(dirname(__FILE__) . "/AdminForm.phtml");
    }

    public function checkTransaction($params, $order, $act)
    {
        $jshopConfig = JSFactory::getConfig();
        if ($act === 'notify') {
            $NotificationHandler = new TransactionNotification($params['seller_secret'], $params['seller_id']);
            if ((bool)$params['proxy']) {
                $NotificationHandler->enableForwardedIPValidation();
            }
            try {
                $res = $NotificationHandler->checkPayment();
                if (number_format($order->order_total, 2) !== number_format($res['tr_paid'], 2)) {
                    throw new TException('Invalid amount paid ' . $res['tr_paid']);
                }
                if ($order->currency_code_iso !== '985') {
                    throw new TException('Invalid currency for this order ' . $order->currency_code_iso);
                }
                if ($res['tr_status'] === 'TRUE' && $res['tr_error'] === 'none') {
                    return [1, 'Received tpay payment ' . $res['tr_id'], $res['tr_id'], $res];
                }
            } catch (TException $e) {
                return [0, $e->getMessage()];
            }
        }

    }

    public function showEndForm($params, $order)
    {
        if ($order->currency_code_iso !== '985') {
            saveToLog("payment.log", "Tpay payments are not available for this currency " . $order->currency_code_iso);
            echo(_JSHOP_ERROR_PAYMENT . ': Tpay payments are not available for this currency.');
            return 0;
        }
        $pmMethod = $this->getPmMethod();
        $uri = JURI::getInstance();
        $currentHost = $uri->toString(array("scheme", 'host', 'port'));
        $notifyUrl = $currentHost . SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=" . $pmMethod->payment_class . "&no_lang=1");
        $return = $currentHost . SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=" . $pmMethod->payment_class);
        $cancel_return = $currentHost . SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=" . $pmMethod->payment_class);
        $_country = JSFactory::getTable('country', 'jshop');
        $_country->load($order->d_country);
        $country = $_country->country_code_2;

        $config = [
            'kwota'        => $order->order_total,
            'opis'         => 'Zamówienie nr ' . $order->order_id,
            'crc'          => $order->order_id,
            'wyn_url'      => $notifyUrl,
            'pow_url'      => $return,
            'pow_url_blad' => $cancel_return,
            'email'        => $order->email,
            'imie'         => $order->d_f_name,
            'nazwisko'     => $order->d_l_name,
            'adres'        => $order->d_street,
            'miasto'       => $order->d_city,
            'kod'          => $order->d_zip,
            'kraj'         => $country,
        ];
        $FormFactory = new PaymentForm($params['seller_secret'], $params['seller_id']);
        echo $FormFactory->getTransactionForm($config, true);
    }

    public function getUrlParams($pmconfigs)
    {
        $params = array();
        $params['order_id'] = isset($_POST['tr_crc']) ?
            Util::post('tr_crc', 'int') : JFactory::getSession()->get("jshop_end_order_id");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;
        $this->setOrderPending($params['order_id']);
        return $params;

    }

    private function setOrderPending($orderId)
    {
        $order = JSFactory::getTable('order', 'jshop');
        $order->load($orderId);
        if ($order->order_created === '0') {
            $order->order_created = 1;
            $order->store();
        }
    }

}
