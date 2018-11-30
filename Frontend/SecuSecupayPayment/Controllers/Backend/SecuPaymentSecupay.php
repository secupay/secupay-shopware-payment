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

require dirname(__FILE__) . '/../../secupay_api.php';

/**
 * Class Shopware_Controllers_Backend_SecuPaymentSecupay
 */
class Shopware_Controllers_Backend_SecuPaymentSecupay extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * this function is called initially and extends the standard template directory
     * @return void
     */
    public function init()
    {
        $this->View()->addTemplateDir(dirname(__FILE__) . "/Views/");
        parent::init();
    }
 
    /**
     * index action is called if no other action is triggered
     * @return void
     */
    public function indexAction()
    {
        $this->View()->loadTemplate("backend/secu_payment_secupay/app.js");
    }

    /**
     *
     */
    public function getSecuPaymentSecupayListAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getSecuPaymentSecupayListAction');
        /*$sql = "SELECT ta.id, ta.payment_method AS PaymentMethod, ta.hash AS Hash,
                ta.trans_id AS Transaction_id, amount/100 AS Amount, ordernr AS Ordernr,
                msg AS Message, status AS Status_id, created AS Date, payment_status AS Payment_status
                FROM secupay_transactions AS ta
                ORDER BY created DESC";*/
        $sql = "SELECT
				ta.id,
				ta.payment_method AS PaymentMethod,
				ta.`hash` AS `Hash`,
				ta.trans_id AS Transaction_id,
				amount/100 AS Amount,
				ta.ordernr AS Ordernr,
				ta.msg AS Message,
				ta.`status` AS Status_id,
				ta.created AS Date,
				ta.payment_status AS Payment_status,
				s_core_shops.`name` AS Shop
				FROM
				secupay_transactions AS ta
				INNER JOIN s_order ON s_order.ordernumber = ta.ordernr
				INNER JOIN s_core_shops ON s_core_shops.id = s_order.subshopID
				ORDER BY created DESC";
        $data = Shopware()->Db()->fetchAll($sql);
 
        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' => count($data)));
    }

    /**
     *
     */
    public function getSecuPaymentSecupayConfigAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'getSecuPaymentSecupayConfigAction');
        
        $data = array();
        $record = new stdClass();
        $record->apikey = $pluginConfig->sSECUPAY_APIKEY;
        $record->secupay_url = SECUPAY_URL;
        $data[] = $record;
        
        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' => count($data)));
    }

    /**
     *
     */
    public function updateTransStatusAction()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'updateTransStatusAction');
        $this->View()->assign(
            $this->updateTransStatus(
                $this->Request()->getParam('id')
            )
        );
    }

    /**
     * @param $secupay_row_id
     * @return array
     * @throws Zend_Db_Adapter_Exception
     */
    public function updateTransStatus($secupay_row_id)
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        secupay_log::log($log, 'updateTransStatus');
        if (empty($secupay_row_id)) {
            return array('success' => false, 'error' => 'No id passed');
        }
        
        $sql = "SELECT ta.hash AS Hash FROM secupay_transactions AS ta WHERE ta.id = ?";
        $sqlapi="SELECT
                    secupay_transactions.req_data
                    FROM
                    secupay_transactions
                    where secupay_transactions.hash = ?
                    ";
        $hash = Shopware()->Db()->fetchOne($sql, array( $secupay_row_id ));
        
        if (empty($hash)) {
            return array('success' => false, 'error' => 'No transaction found');
        }
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        //$apikey = $pluginConfig->sSECUPAY_APIKEY;
        $apikey = json_decode(stripcslashes(Shopware()->Db()->fetchone($sqlapi, array($hash))))->apikey;
        $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
        
        if (empty($apikey)) {
            return array('success' => false, 'error' => 'No apikey found');
        }
        
        $data = array();
        $data['apikey'] = $apikey;
        $data['hash'] = $hash;
        
        $sp_api = new secupay_api($data, 'status');
        $api_return = $sp_api->request();
        
        if (empty($api_return)) {
            return array('success' => false, 'error' => 'No response');
        }
                
        if (!$api_return->check_response($log)) {
            $error_message = $api_return->get_error_message_user();
            return array('success' => false, 'error' => 'Error: ' . $error_message);
        }
        
        //status of hash
        $status = $api_return->data->status;
        //simplified status of transaction
        $payment_status = $api_return->data->payment_status;
        //status of transaction
        $status_desc = $api_return->data->status_description;
        $trans_id = $api_return->data->trans_id;
        
        if (empty($status)) {
            return array('success' => false, 'error' => 'No status');
        }
        
        if (!empty($trans_id)) {
            //set TACode if missing
            $update_sql = "UPDATE secupay_transactions SET trans_id = ? WHERE hash = ? AND ISNULL(trans_id);";
            Shopware()->Db()->query($update_sql, array($trans_id, $hash));
        }
        
        if (!empty($payment_status)) {
            $update_sql = "UPDATE secupay_transactions SET payment_status = ? WHERE hash = ?;";
            Shopware()->Db()->query($update_sql, array($payment_status, $hash));
        } else {
            if ($status != 'accepted' && $status != 'authorized') {
                $update_sql = "UPDATE secupay_transactions SET payment_status = ?, msg = '' WHERE hash = ?;";
                Shopware()->Db()->query($update_sql, array($status, $hash));
            }
        }
        
        if (!empty($status_desc)) {
            $update_sql = "UPDATE secupay_transactions SET msg = ? WHERE hash = ?;";
            Shopware()->Db()->query($update_sql, array($status_desc, $hash));
        }
        
        return array('success' => true);
    }
}
