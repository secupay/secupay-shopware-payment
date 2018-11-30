<?php
/**
 * secupay Payment Module
 *
 *  @category  Payment
 *  @author    secupay AG
 *  @copyright 2018, secupay AG
 *  @link      https://www.secupay.ag/de/online-commerce/shopmodule
 *  @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License Version 2.0
 *
 *  Description:
 *
 *  Shopware module for integration of secupay AG payment services
 */

/**
 * Secupay Payment Plugin Class
 *
 */

require dirname(__FILE__) . '/../../secupay_api.php';
use Shopware\Components\CSRFWhitelistAware;

/**
 * Secupay Payment Plugin Class
 *
 * @package Secupay
 * @copyright 2016 Secucard Projekt KG
 */
class Shopware_Controllers_Frontend_SecuPaymentSecupay extends Shopware_Controllers_Frontend_Payment implements CSRFWhitelistAware
{
    /**
     *
     */
    public function preDispatch()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'preDispatch');
        if (in_array($this->Request()->getActionName(), array('recurring', 'notify'))) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }
    /**
     * indexAction is called first
     */
    public function indexAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'indexAction'.$this->getPaymentShortName());
        switch ($this->getPaymentShortName()) {
            case 'secupay_debit':
                return $this->redirect(array( 'action' => 'gateway', 'forceSecure' => true ));
            case 'secupay_creditcard':
                return $this->redirect(array( 'action' => 'gateway', 'forceSecure' => true ));
            case 'secupay_invoice':
                return $this->redirect(array( 'action' => 'gateway', 'forceSecure' => true ));
            case 'secupay_prepay':
                return $this->redirect(array( 'action' => 'gateway', 'forceSecure' => true ));
            case 'secupay_sofort':
                return $this->redirect(array( 'action' => 'gateway', 'forceSecure' => true ));

            default:
                return $this->redirect(array( 'controller' => 'checkout' ));
        }
    }

    /**
     * Method used to create payment request to Secupay API
     * Forwards user to error page or displays IFrame
     */
    public function gatewayAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'gatewayAction');
        $experience = $pluginConfig->sSECUPAY_EXPERIENCE;
        $payment_type = $this->getPaymentType();

        if (empty($payment_type)) {
            return $this->forward('error', null, null, array('secupay_error_msg' => 'payment type not available'));
        }

        $router = $this->Front()->Router();
        $userData = $this->getUser();
        $positive=0;
        $negative=0;
        if ($experience) {
            $sqlpositiv="SELECT
            Count(*) AS positiv
            FROM
            s_order
            WHERE
            s_order.userID = '".$userData["billingaddress"]["userID"]."' AND
            s_order.`status` IN (2, 7, 6, 5, 3)";
            $sqlnegativ="SELECT
            Count(*) AS negativ
            FROM
            s_order
            WHERE
            s_order.userID = '".$userData["billingaddress"]["userID"]."'";
            $respositiv = Shopware()->Db()->fetchAll($sqlpositiv);
            $resnegativ = Shopware()->Db()->fetchAll($sqlnegativ);
            $positive=$respositiv[0]['positiv'];
            $negative=$resnegativ[0]['negativ']-$positive;
        }
        $data = array();
        $data['apikey'] = $pluginConfig->sSECUPAY_APIKEY;
        $data['payment_type'] = $payment_type;
        $amount = intval(strval($this->getAmount() * 100));
        $data['amount'] = $amount;
        $data['experience']['positive']=$positive;
        $data['experience']['negative']=$negative;
        // uniqueId will be used by us to find the created secupay_transactions record
        $uniqueId = $this->createPaymentUniqueId();
        $data['shopware_unique_id'] = $uniqueId;

        //$data['subscription']['purpose'] = "ABO Monatlich bei ".$pluginConfig->sSECUPAY_ALL_PURPOSE_SHOPNAME;

        $data['purpose'] = $this->getPurpose();
        $basketcontents = $this->getBasket();
        if (!empty($basketcontents['sCurrencyName'])) {
            $data['currency'] = $basketcontents['sCurrencyName'];
        }
        $data['order_number'] = $this->getOrderNumber();

        $locale = Shopware()->Shop()->getLocale()->getLocale();

        if (isset($locale)) {
            if ($locale == 'en_GB') {
                $locale = 'en_US';
            }
            $data['language'] = $locale;
        }

        $data['title'] = $userData["billingaddress"]["salutation"];

        $data['firstname'] = $userData["billingaddress"]["firstname"];
        $data['lastname'] = $userData["billingaddress"]["lastname"];
        $data['street'] = $userData["billingaddress"]["street"];
        //since shopware 5 housenumber is part of street
        //if ($this->assertMinimumVersion('5')) { //does not work
        if (empty($userData["billingaddress"]["streetnumber"])) {
            $data['street'] = $userData["billingaddress"]["street"];
        } else {
            $data['street'] = $userData["billingaddress"]["street"];
            $data['housenumber'] = $userData["billingaddress"]["streetnumber"];
        }
        $data['zip'] = $userData["billingaddress"]["zipcode"];
        $data['city'] = $userData["billingaddress"]["city"];

        $data['email'] = $userData["additional"]["user"]["email"];
        $data['ip'] = $_SERVER['REMOTE_ADDR'];

        $data['apiversion'] = secupay_api::get_api_version();
        $info = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->getInfo();
        $data['modulversion'] = $info['version'];

        $data['shop'] = "Shopware";
        $data['shopversion'] = Shopware()->Config()->Version;

        if ($pluginConfig->sSECUPAY_ALL_SWITCH_TESTMODE) {
            $data['demo'] = 1;
        }
        if ($payment_type == 'sofort') {
            $data['demo'] = 0;
        }
        $data['company'] = $userData["billingaddress"]["company"];
        $data['dob'] = $userData["billingaddress"]["birthday"];
        $data['telephone'] = $userData["billingaddress"]["phone"];
        $data['fax'] = $userData["billingaddress"]["fax"];
        $data['country'] = $userData['additional']['countryBilling']['countryiso'];
        if (empty($data['country'])) {
            $data['country'] = $userData['additional']['country']['countryiso'];
        }


        $delivery_address = new stdClass();
        $delivery_address->firstname = $userData["shippingaddress"]["firstname"];
        $delivery_address->lastname = $userData["shippingaddress"]["lastname"];
        //since shopware 5 housenumber is part of street
        //if ($this->assertMinimumVersion('5')) {   //does not work
        if (empty($userData["shippingaddress"]["streetnumber"])) {
            $delivery_address->street = $userData["shippingaddress"]["street"];
        } else {
            $delivery_address->street = $userData["shippingaddress"]["street"];
            $delivery_address->housenumber = $userData["shippingaddress"]["streetnumber"];
        }
        $delivery_address->zip = $userData["shippingaddress"]["zipcode"];
        $delivery_address->city = $userData["shippingaddress"]["city"];
        $delivery_address->country = $userData['additional']['countryShipping']['countryiso'];
        $delivery_address->company = $userData["shippingaddress"]["company"];

        if ($pluginConfig->sSECUPAY_ALL_SWITCH_SEND_LAST_HASH) {
            $last_hash = $this->getLastTransactionHash();
            if (!empty($last_hash)) {
                $data['hash_ref_payment_data'] = $last_hash;
            }
        }

        $data['delivery_address'] = $delivery_address;

        $data['basket'] = json_encode($this->getBasketInformation());

        $data['url_push'] = $router->assemble(array('action' => 'notify', 'forceSecure' => true));
        $data['url_push'] .= '?id=' . $uniqueId;
        $data['url_success'] = $router->assemble(array('action' => 'return', 'forceSecure' => true));
        $data['url_success'] .= '?id=' . $uniqueId;
        $data['url_failure'] = $router->assemble(array('action' => 'failure', 'forceSecure' => true));
        $data['url_failure'] .= '?id=' . $uniqueId;

        $sp_api = new secupay_api($data);
        $api_return = $sp_api->request();

        $_req_data = addslashes(json_encode($data));
        $_ret_data = addslashes(json_encode($api_return));

        $status = $api_return->get_status();
        $api_hash = $api_return->get_hash();

        $msg = utf8_decode(addslashes($api_return->get_error_message()));
        if (empty($api_return)) {
            $msg = "Verbindungs-Fehler";
        }
        $subscription_id=null;
        if ($api_return->get_subscription_id()) {
            $subscription_id=$api_return->get_subscription_id();
        }
        $sql = "INSERT INTO secupay_transactions (
            req_data,
            ret_data,
            payment_method,
            `hash`,
            unique_id,
            ordernr,
            status,
            amount,
            subscription_id,
            msg,
            rank,
            created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,99, NOW() )";

        Shopware()->Db()->query($sql, array(
            $_req_data,
            $_ret_data,
            $payment_type,
            $api_hash,
            $uniqueId,
            $this->getOrderNumber(),
            $status,
            $amount,
            $subscription_id,
            $msg
        ));

        secupay_log::log($log, 'gatewayAction: '.$api_return->get_iframe_url());
        if (isset($api_return) && $api_return instanceof secupay_api_response && $api_return->check_response()) {
            if ($payment_type=='prepay') {
                secupay_log::log($log, 'prepay');
                $payment_data = array(
                        'AMOUNT'                => $this->formatNumber($this->getAmount()),
                        'CURRENCY'        => $this->getCurrencyShortName(),
                        'COUNTRY'    => 'DE',
                        'ACCOUNTOWNER'        => $api_return->data->payment_data->accountowner,
                        'ACCOUNTNUMBER'        => $api_return->data->payment_data->accountnumber,
                        'BANKCODE'            => $api_return->data->payment_data->bankcode,
                        'IBAN'                => $api_return->data->payment_data->iban,
                        'BIC'                => $api_return->data->payment_data->bic,
                        'PURPOSE'                        => $api_return->data->purpose,
                    );
                $this->forward('return', null, null, array('id' => $uniqueId, 'paymentdata'=> $payment_data));
            } elseif ($payment_type=='sofort') {
                secupay_log::log($log, 'redirect sofort');
                sleep(2);
                $this->redirect($api_return->get_iframe_url(), array( "forceSecure" => 1 ));
            } else {
                secupay_log::log($log, 'INV,CC,Debit');
                $this->View()->gatewayUrl = $api_return->get_iframe_url();
            }
        } else {
            secupay_log::log($log, "secupay_log::log");
            secupay_log::log($log, $api_return->get_error_message());
            return $this->forward('error', null, null, array('secupay_error_msg' => $api_return->get_error_message_user()));
        }
    }

    /**
     * Method that is called after iFrame is completed
     * If the payment is accepted, then the order is created in shopware
     */
    public function returnAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'returnAction');
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (empty($basket)) {
            //Bestellung bereits abgeschlossen
            return $this->redirect(array('controller' => 'index'));
        }

        $payment_type = $this->getPaymentType();
        if (empty($payment_type)) {
            secupay_log::log($log, 'returnAction - error: payment type is not set');
            return $this->forward('error', null, null, array('secupay_error_msg' => 'payment type is not set'));
        }
       
        $amount_sent = intval(strval($this->getAmount() * 100));
        $uniqueId = $this->Request()->getParam('id');
        
        $sql = 'SELECT hash FROM secupay_transactions WHERE unique_id = ? AND status!=-1 ORDER BY created DESC LIMIT 1';
        secupay_log::log($log, 'returnAction' . $sql);
        $hash = Shopware()->Db()->fetchOne($sql, array($uniqueId));
        secupay_log::log($log, 'returnAction' . $hash);
        // check if request is valid
        if (empty($hash)) {
            return $this->forward('error', null, null, array('secupay_error_msg' => 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Validierungs-Fehler)'));
        }
        $response = $this->getTransactionStatusResponse($hash);
        if ($payment_type=='prepay') {
            if (!$response || empty($response->status) || (($payment_type=='prepay' && $response->status != 'proceed'))) {
                secupay_log::log($log, 'returnAction - error: hash status not accepted Paymenttype: '.$payment_type);
                secupay_log::log($log, 'returnAction - ' . $response->status);
                return $this->forward('error', null, null, array('secupay_error_msg' => 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Status Validierung-Fehler)'));
            }
        } else {
            if (!$response || empty($response->status) || ($response->status != 'accepted' && $response->status != 'authorized')) {
                secupay_log::log($log, 'returnAction - error: hash status not accepted');
                return $this->forward('error', null, null, array('secupay_error_msg' => 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Status Validierung-Fehler)'));
            }
        }

        $response_amount = (int)$response->amount;

        // check if amount matches
        if ($amount_sent != $response_amount) {
            secupay_log::log($log, 'returnAction - error: amount does not match');
            secupay_log::log($log, 'returnAction - amount sent: '.$amount_sent);
            secupay_log::log($log, 'returnAction - amount received: '.$response_amount);
            return $this->forward('error', null, null, array('secupay_error_msg' => 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Betrag Validierung-Fehler)'));
        }

        // everything is ok
        // set payment status to waiting (waiting for push action)
        $paymentStatus = $pluginConfig->sSECUPAY_STATUS_WAITING;
        // hash will be used as transactionId and uniqueId will be used as paymentUniqueId
        $order_number = $this->getOrderNumber();
        if (empty($order_number)) {
            $order_number = $this->saveOrder($hash, $uniqueId);
        }
        $this->savePaymentStatus($hash, $uniqueId, $paymentStatus);

        if (!empty($response->trans_id)) {
            $secupay_transaction_id = $response->trans_id;
        } else {
            $secupay_transaction_id = 0;
        }
        $sql_update = "UPDATE secupay_transactions SET status = ?, ordernr = ?, trans_id = ? WHERE unique_id = ? AND hash = ?";
        Shopware()->Db()->query($sql_update, array($paymentStatus, $order_number, $secupay_transaction_id, $uniqueId, $hash));
        
        //$this->prepayMail($order_number , $user['additional']['user']['email'], $this->Request()->getParam('paymentdata'));
        return $this->forward('finish', 'checkout', null, array('sUniqueID' => $uniqueId));
    }

    /**
     * This function is called when secupay denies the transaction
     */
    public function failureAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'failureAction');
        return $this->forward('error', null, null, array('secupay_error_msg' => 'Zahlung abgelehnt oder abgebrochen'));
    }

    /**
     * This action displays error template - template that shows user-friendly error message
     */
    public function errorAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'errorAction');
        $view = $this->View();
        $view->errorMsg = $this->Request()->getParam('secupay_error_msg', 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Verbindungs-Fehler)');
        $view->sErrorMessages = array('Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Verbindungs-Fehler)');
        //**todo direkte Rückleitung zur Zahlungsauswahl, errormessage muss noch angepasst werden

        /*$this->redirect(
            array(
                "controller"   => "account",
                "action"       => "payment",
                "sTarget"      => "checkout",
                "errorMessage" => true,
                "forceSecure"  => true
            )
        );*/
    }

    /**
     * Action to change payment status from push API
     *
     */
    public function notifyAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'notifyAction');

        $low_ip = '91.195.150.1';
        $high_ip = '91.195.151.255';
        // debug log
        $post_data = http_build_query($this->Request()->getPost());
        secupay_log::log($log, 'Notify action with parameters: '. print_r(http_build_query($post_data), true));
        secupay_log::log($log, 'Client IP : '. print_r($this->Request()->getClientIp(false), true));

        // accept notifyAction requests only from secupay

        $client_ip = ip2long($this->Request()->getClientIp(false));
        if ($client_ip > ip2long($high_ip) || ip2long($low_ip) > $client_ip) {
            die('ack=Disapproved&error=request+invalid&' . $post_data);
        }

        $uniqueId = $this->Request()->getParam('id');
        if (empty($uniqueId)) {
            die('ack=Disapproved&error=no+matching+order+found+for+id&' . $post_data);
        }

        $hash_sent = $this->Request()->getParam('hash');
        $apikey_sent = $this->Request()->getParam('apikey');
        $payment_status = $this->Request()->getParam('payment_status');
        $status_desc = $this->Request()->getParam('status_description');
        $sql="SELECT
            s_core_config_values.`value`
            FROM
            s_core_config_elements
            INNER JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id AND s_core_config_elements.`name` = 'sSECUPAY_APIKEY' AND s_core_config_values.`value` = ?
            ";
        unset($data);
        
        $data = Shopware()->Db()->fetchOne($sql, array( serialize($apikey_sent) ));
        if (!empty($data)) {
            $apikey=(unserialize($data));
        } else {
            $apikey = $pluginConfig->sSECUPAY_APIKEY;
        }
        if (empty($apikey_sent) || $apikey_sent !== $apikey) {
            die('ack=Disapproved&error=apikey+invalid&' . $post_data);
        }

        $row = Shopware()->Db()->fetchAll("SELECT status, ordernr, payment_method FROM secupay_transactions WHERE unique_id = ? AND hash = ? ORDER BY created DESC LIMIT 1", array($uniqueId, $hash_sent));
        if (empty($row) || !isset($row[0]['ordernr'])) {
            //matching transaction, but without order
            //save push to DB for transaction list
            if (!empty($row) && !empty($payment_status) && !empty($status_desc)) {
                $sql = "UPDATE secupay_transactions SET payment_status = ?, msg = ?, updated = NOW() WHERE unique_id = ? AND hash = ?";
                Shopware()->Db()->query($sql, array($payment_status, $status_desc, $uniqueId, $hash_sent));
            }

            die('ack=Disapproved&error=no+matching+order+found+for+hash&' . $post_data);
        }
        //$row_status = $row[0]['status'];
        //check if order still exists
        $sql = 'SELECT ordernumber,invoice_amount FROM s_order WHERE ordernumber=?';
        $orderres = Shopware()->Db()->fetchAll($sql, array(
                $row[0]['ordernr']
        ));
        $orderNumber = $orderres[0]['ordernumber'];
        $original_order_total = number_format($orderres[0]['invoice_amount'], 2, '.', '')*100;
        if (!isset($orderNumber) || empty($orderNumber)) {
            secupay_log::log($log, "Notify action - order {$orderNumber} not found");
            die('ack=Disapproved&error=no+matching+order+found+for+transaction&' . $post_data);
        }
        $response = $this->getTransactionStatusResponse($hash_sent);
        $secupay_orders_total = $response->amount;
        if ($original_order_total != $secupay_orders_total) {
            $payment_status = 'issue';
        }
        // if the status is other, than save it
        $new_status = $this->getShopwareStatus($payment_status, $row[0]['payment_method']);
        if (!$new_status) {
            die('ack=Disapproved&error=payment_status+not+supported&' . $post_data);
        }
        if (($row[0]['payment_method']=='creditcard' && $payment_status=='accepted') || ($row[0]['payment_method']=='prepay' && $payment_status=='accepted')) {
            $cleared = date("Y-m-d H:i:s");
            $sql = 'UPDATE `s_order` SET `cleareddate` = ? WHERE `transactionID` = ?';
            Shopware()->Db()->query($sql, array($cleared, $hash_sent));
        }
        secupay_log::log($log, "Notify action successful for ".$hash_sent." with status: ".$payment_status." and shopware status: ".$new_status."");
        // everything ok
        // update status in DB
        $sql = "UPDATE secupay_transactions SET status = ? WHERE unique_id = ? AND hash = ?";
        Shopware()->Db()->query($sql, array($new_status, $uniqueId, $hash_sent));
        try {
            $this->saveOrder($hash_sent, $uniqueId);
        } catch (Exception $e) {
            secupay_log::log($log, "Notify action - Exception on saveOrder for " . $hash_sent);
            secupay_log::log($log, "Notify action - Exception: " . $e->getMessage());
        }
        $this->savePaymentStatus($hash_sent, $uniqueId, $new_status);

        if (!empty($payment_status) && !empty($status_desc)) {
            $sql = "UPDATE secupay_transactions SET payment_status = ?, msg = ?, updated = NOW() WHERE unique_id = ? AND hash = ?";
            Shopware()->Db()->query($sql, array($payment_status, $status_desc, $uniqueId, $hash_sent));
        }

        die('ack=Approved&' . $post_data);
    }

    /**
     * Function to get transaction status from Secupay
     *
     * @return status response array or false as failed
     */
    private function getTransactionStatusResponse($hash)
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getTransactionStatusResponse');
        if (empty($hash)) {
            return false;
        }
        $data = array();
        $data['apikey'] = $pluginConfig->sSECUPAY_APIKEY;
        $data['hash'] = $hash;
        $sp_api = new secupay_api($data, 'status');
        $api_return = $sp_api->request();

        if (isset($api_return) && $api_return instanceof secupay_api_response && $api_return->check_response()) {
            return $api_return->data;
        } else {
            return false;
        }
    }

    /**
     * Returns shopware_status_id for status identified by string
     *
     * @param string status
     * @param string payment_method
     * @return int status_id or 0
     */
    private function getShopwareStatus($status, $payment_method)
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getShopwareStatus');
        switch ($status) {
            case 'denied':
                return $pluginConfig->sSECUPAY_STATUS_DENIED;
            case 'accepted':
                switch ($payment_method) {
                    case 'creditcard':
                        return $pluginConfig->sSECUPAY_STATUS_ACCEPTED_CREDITCARD;
                    case 'debit':
                        return $pluginConfig->sSECUPAY_STATUS_ACCEPTED_DEBIT;
                    case 'invoice':
                        return $pluginConfig->sSECUPAY_STATUS_ACCEPTED_INVOICE;
                    case 'prepay':
                        return $pluginConfig->sSECUPAY_STATUS_ACCEPTED_PREPAY;
                    case 'sofort':
                        return $pluginConfig->sSECUPAY_STATUS_ACCEPTED_SOFORT;
                }
                //return $pluginConfig->sSECUPAY_STATUS_ACCEPTED;
                // no break
            case 'authorized':
                switch ($payment_method) {
                    case 'prepay':
                        return $pluginConfig->sSECUPAY_STATUS_AUTHORIZED_PREPAY;
                    default:
                        return $pluginConfig->sSECUPAY_STATUS_AUTHORIZED;
                    }
                    // no break
            case 'void':
                return $pluginConfig->sSECUPAY_STATUS_VOID;
            case 'issue':
                return $pluginConfig->sSECUPAY_STATUS_ISSUE;
        }

        return 0;
    }

    /**
     * Function that returns purpose of the payment
     *
     * @return string
     */
    private function getPurpose()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getPurpose');
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $_shop = $pluginConfig->sSECUPAY_ALL_PURPOSE_SHOPNAME;
        if (empty($_shop)) {
            $_shop = Shopware()->Config()->Shopname;
        }
        return $_shop . "|Bestellung vom " . date("d.m.Y") . "|Bei Fragen TEL 035955755055";
    }

    /**
     * Function that returns payment type for Secupay
     *
     * @return string paymentType
     */
    private function getPaymentType()
    {
        switch ($this->getPaymentShortName()) {
            case 'secupay_debit':
                return 'debit';
            case 'secupay_creditcard':
                return 'creditcard';
            case 'secupay_invoice':
                return 'invoice';
            case 'secupay_prepay':
                return 'prepay';
            case 'secupay_sofort':
                return 'sofort';
        }
        return '';
    }

    /**
     * Function that returns array of available paymentTypes
     *
     * @return array of strings
     */
    private function checkPaymentTypeAvailability($payment_type)
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'checkPaymentTypeAvailability');
        $data = array();
        $data['apikey'] = $pluginConfig->sSECUPAY_APIKEY;
        $sp_api = new secupay_api($data, 'gettypes');
        $api_return = $sp_api->request();
        if (isset($api_return) && $api_return instanceof secupay_api_response && $api_return->check_response()) {
            return in_array($payment_type, $api_return->data);
        } else {
            return false;
        }
    }

    /**
     * Function that returns basket information for the API
     *
     * @return array
     */
    private function getBasketInformation()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getBasketInformation');
        $basketcontents = $this->getBasket();
        $pluginAEN=$pluginConfig->sSECUPAY_ALL_SWITCH_SEND_EAN;
        foreach ($basketcontents['content'] as $item) {
            $product = new stdClass();
            unset($article);
            if ($pluginAEN==1) {
                if (!empty($item['articleID'])) {
                    $article = Shopware()->Modules()->Articles()->sGetArticleById($item['articleID']);
                }
            }
            $product->article_number = $item['ordernumber'];
            $product->name = $item['articlename'];
            $product->model = '';
            if (isset($article) && isset($article['ean'])) {
                $product->ean = $article['ean'];
            } else {
                $product->ean = '';
            }
            $product->quantity = $item['quantity'];
            $product->price = $item['priceNumeric'] * 100;
            $product->total = $item['priceNumeric'] * $item['quantity'] * 100;
            $product->tax = $item['tax_rate'];
            $products[] = $product;
        }
        if (!empty($basketcontents['sShippingcostsWithTax'])) {
            $shipping = new stdClass();
            $shipping->item_type = 'shipping';
            $shipping->total = $basketcontents['sShippingcosts']*100;
            $shipping->tax = $basketcontents['sShippingcostsTax'];
            $products[] = $shipping;
        }

        return $products;
    }

    /**
     * Function that returns the last hash for the user with the same payment_type
     *
     * @return array
     */
    private function getLastTransactionHash()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getLastTransactionHash');
        $payment_name = $this->getPaymentShortName();
        $userData = $this->getUser();
        if (!empty($userData) && !empty($payment_name)) {
            $userID = $userData["billingaddress"]["userID"];
            if (!empty($userID)) {
                $sql_get_hash = "SELECT secupay_transactions.`hash`
                        FROM secupay_transactions
                        INNER JOIN s_order ON s_order.ordernumber = secupay_transactions.ordernr AND s_order.transactionID = secupay_transactions.`hash`
                        INNER JOIN s_core_paymentmeans ON s_core_paymentmeans.id = s_order.paymentID
                        WHERE s_order.userID = ? AND s_core_paymentmeans.`name` = ? AND secupay_transactions.trans_id > 0
                        ORDER BY secupay_transactions.created DESC
                        LIMIT 1";
                $hash = Shopware()->Db()->fetchOne($sql_get_hash, array($userID, $payment_name));
                return $hash;
            }
        }

        return false;
    }
    /**
     * Recurring payment action method.
     */
    public function recurringAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'recurringAction');
        $apikey = $pluginConfig->sSECUPAY_APIKEY;
        $_shop = $pluginConfig->sSECUPAY_ALL_PURPOSE_SHOPNAME;
        $hashId=false;
        $subscriptionId=false;
        $orderId = $this->Request()->getParam('orderId');
        $sqlHash = '
            SELECT
            secupay_transactions.`hash`
            FROM
            s_order
            INNER JOIN s_plugin_swag_abo_commerce_orders ON s_plugin_swag_abo_commerce_orders.order_id = s_order.id
            INNER JOIN secupay_transactions ON secupay_transactions.`hash` = s_order.transactionID AND secupay_transactions.ordernr = s_order.ordernumber
            WHERE
            s_order.id = ?
            ';
        $sqlSub='
            SELECT
            secupay_transactions.subscription_id
            FROM
            secupay_transactions
            WHERE
            secupay_transactions.`hash` = ?';
        $hashId = Shopware()->Db()->fetchOne($sqlHash, array($orderId));
        $subscriptionId = Shopware()->Db()->fetchOne($sqlSub, array($hashId));
        if (empty($subscriptionId) and !empty($apikey)) {
            $data = array();
            $data['apikey'] = $pluginConfig->sSECUPAY_APIKEY;
            $data['hash'] = $hashId;
            //get getSubscription
            $sp_api = new secupay_api($data, 'getSubscription');
            $api_return = $sp_api->request();
            if ($api_return->status!=='ok') {
                $message='Sie sind nicht für Abo Freigeschalten';
            } else {
                $subscriptionId = $api_return->data->subscription_id;
                $sql = "UPDATE secupay_transactions SET subscription_id = ?, updated = NOW() WHERE hash = ?";
                Shopware()->Db()->query($sql, array($subscriptionId,$hashId));
            }
        }
        if ($this->checkPaymentTypeAvailability('subscription') and !empty($hashId) and !empty($subscriptionId) and !empty($apikey)) {
            $details = array('apikey' => $apikey);
            $details['hash'] = $hashId;
            $details['subscription_id'] = $subscriptionId;
            if (empty($_shop)) {
                $_shop = Shopware()->Config()->Shopname;
            }
            $details['purpose'] = $_shop . "|ABO Bestellung vom " . date("d.m.Y") . "|Bei Fragen TEL 035955755055";
            $response = $this->finishCheckout($details);
            $msg=$response;
        } else {
            $msg = array(
                    'success' => false,
                    'data' => array(
                        array(
                            'message' => $message,
                        )
                    )
                );
        }
        echo Zend_Json::encode($msg);
    }

    /**
     * @param $details
     * @return array|string
     * @throws Zend_Db_Adapter_Exception
     */
    protected function finishCheckout($details)
    {
        $router = $this->Front()->Router();
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'finishCheckout');
        $uniqueId=$this->createPaymentUniqueId();
        $details['shopware_unique_id'] = $uniqueId;
        $details['amount'] = intval(strval($this->getAmount() * 100));
        $details['basket'] = json_encode($this->getBasketInformation());
        $details['url_push'] = $router->assemble(array('action' => 'notify', 'forceSecure' => true));
        $details['url_push'] .= '?id=' . $uniqueId;
        $payment_type = $this->getPaymentType();
        $sp_api = new secupay_api($details, 'subscription');
        $api_return = $sp_api->request();
        if ($api_return->status!=='ok') {
            $msg = array(
                    'success' => false,
                    'data' => array(
                        array(
                            'message' => 'Sie sind nicht für Abo Freigeschalten',
                        )
                    )
                );
            return $msg;
        } else {
            $_req_data = addslashes(json_encode($details));
            $_ret_data = addslashes(json_encode($api_return));
            $status = $api_return->get_status();
            $api_hash = $api_return->get_hash();
            $msg = utf8_decode(addslashes($api_return->get_error_message()));
            $sql = "INSERT INTO secupay_transactions (
                req_data,
                ret_data,
                payment_method,
                `hash`,
                unique_id,
                ordernr,
                status,
                amount,
                msg,
                rank,
                created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 99, NOW() )";

            Shopware()->Db()->query($sql, array(
                $_req_data,
                $_ret_data,
                $payment_type,
                $api_hash,
                $uniqueId,
                $this->getOrderNumber(),
                $status,
                $details['amount'],
                $msg
            ));
            if (empty($api_return)) {
                $response_status = 'Sie sind nicht für Abo Freigeschalten';
                $msg = array(
                    'success' => false,
                    'data' => array(
                        array(
                            'message' => $response_status,
                        )
                    )
                );
                $sql_update = "UPDATE secupay_transactions SET status = ? WHERE unique_id = ? AND hash = ?";
                Shopware()->Db()->query($sql_update, array($response_status, $uniqueId, $api_hash));
                return $msg;
            }
            $response = $this->getTransactionStatusResponse($api_hash);
            if (!empty($response->trans_id)) {
                $secupay_transaction_id = $response->trans_id;
            } else {
                $secupay_transaction_id = 0;
            }
            if (!$response || empty($response->status) || ($response->status != 'accepted' && $response->status != 'authorized')) {
                secupay_log::log($log, 'returnAction - error: hash status not accepted');
                if (empty($response->status)) {
                    $response_status = 'Zahlung derzeit nicht möglich, not accepted';
                } else {
                    $response_status = $response->status;
                }
                $msg = array(
                    'success' => false,
                    'data' => array(
                        array(
                            'message' => $response_status,
                        )
                    )
                );
                $sql_update = "UPDATE secupay_transactions SET info = ?, trans_id = ? WHERE unique_id = ? AND hash = ?";
                Shopware()->Db()->query($sql_update, array($response_status, $secupay_transaction_id, $uniqueId, $api_hash));
                return $msg;
            }
            $response_amount = (int)$response->amount;
            // check if amount matches
            if ($details['amount'] != $response_amount) {
                secupay_log::log($log, 'returnAction - error: amount does not match');
                secupay_log::log($log, 'returnAction - amount sent: '. $details['amount']);
                secupay_log::log($log, 'returnAction - amount received: '.$response_amount);
                $response_status = 'Zahlung derzeit nicht möglich, bitte wenden Sie sich an den Shopbetreiber (Status Validierung-Fehler)';
                $msg = array(
                    'success' => false,
                    'data' => array(
                        array(
                            'message' => $response_status,
                        )
                    )
                );
                $sql_update = "UPDATE secupay_transactions SET info = ?, trans_id = ? WHERE unique_id = ? AND hash = ?";
                Shopware()->Db()->query($sql_update, array($response_status, $secupay_transaction_id, $uniqueId, $api_hash));
                return $msg;
            }
            // everything is ok
            // set payment status to waiting (waiting for push action)
            $paymentStatus = $pluginConfig->sSECUPAY_STATUS_WAITING;
            // hash will be used as transactionId and uniqueId will be used as paymentUniqueId
            $order_number = $this->getOrderNumber();
            if (empty($order_number)) {
                $order_number = $this->saveOrder($api_hash, $uniqueId);
            }
            $this->savePaymentStatus($api_hash, $uniqueId, $paymentStatus);

            $sql_update = "UPDATE secupay_transactions SET status = ?, ordernr = ?, trans_id = ? WHERE unique_id = ? AND hash = ?";
            Shopware()->Db()->query($sql_update, array($paymentStatus, $order_number, $secupay_transaction_id, $uniqueId, $api_hash));
            $msg = array(
                    'success' => true,
                    'message' => "erfolgreich",
                    'data' => array(
                        array(
                            'orderNumber' => $order_number,
                        )
                    )
                );
        }
        return $msg;
    }

    /**
     * @param $order
     * @param $customer
     * @param $prepaymentData
     * @param string $template
     */
    public function prepayMail($order, $customer, $prepaymentData, $template = 'SecupayPrepayMail')
    {
        try {
            $prepaymentData['ordernumber'] = $order;
            $mail = Shopware()->TemplateMail()->createMail($template, $prepaymentData);
            $mail->addTo($customer);
            $mail->send();
        } catch (Exception $e) {
            $this->hgw()->Logging('SecupayPrepayMail | '.$e->getMessage());
            return;
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function formatNumber($value)
    {
        return sprintf('%1.2f', $value);
    }

    /**
     * @return array|string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
             'notify'
        ];
    }
}
