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
require dirname(__FILE__) . '/secupay_api.php';
require_once __DIR__ . '/Components/CSRFWhitelistAware.php';

/**
 * Class Shopware_Plugins_Frontend_SecuSecupayPayment_Bootstrap
 */
class Shopware_Plugins_Frontend_SecuSecupayPayment_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * constant for logging to sp_log file
     */
    const log = true;

    /**
     * Payment Types array
     * @var Array
     */

    private $payment_types = array(
        'secupay_creditcard' => array(
                                    'name' => 'secupay_creditcard',
                                    'description' => 'Kreditkarte',
                                    'action' => 'secu_payment_secupay',
                                    'active' => 0,
                                    'position' => 1,
                                    'template' => 'secupay_payment.tpl',
                                    'additionalDescription' => '
<img src="https://www.secupay.com/sites/default/files/media/Icons/de_de/secupay_creditcard.png"/>
<div id="payment_desc">
    Sie zahlen einfach und sicher mit Ihrer Kreditkarte.
</div>
'),
        'secupay_debit' => array(
                                'name' => 'secupay_debit',
                                'description' => 'Lastschrift',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 2,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.com/sites/default/files/media/Icons/de_de/secupay_debit.png"/>
<div id="payment_desc">
    Sie zahlen bequem per Bankeinzug.
</div>
'),
        'secupay_sofort' => array(
                                'name' => 'secupay_sofort',
                                'description' => 'SOFORT Überweisung',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 4,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://cdn.klarna.com/1.0/shared/image/generic/badge/de_de/pay_now/standard/pink.svg"/>
<div id="payment_desc">
    Einfach und direkt bezahlen per Online Banking.
</div>
'),
        'secupay_invoice' => array(
                                'name' => 'secupay_invoice',
                                'description' => 'Rechnung',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 3,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.com/sites/default/files/media/Icons/de_de/secupay_invoice.png"/>
<div id="payment_desc">
    Sie überweisen den Rechnungsbetrag nach Erhalt und Prüfung der Ware.
</div>
'),
        'secupay_prepay' => array(
                                'name' => 'secupay_prepay',
                                'description' => 'Vorkasse',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 5,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.com/sites/default/files/media/Icons/de_de/secupay_prepay.png"/>
<div id="payment_desc">
    Sie zahlen vorab und erhalten nach Zahlungseingang Ihre bestellte Ware.
</div>
')
        );
    private $payment_types_en = array(
        'secupay_creditcard' => array(
                                    'name' => 'secupay_creditcard',
                                    'description' => 'Creditcard',
                                    'action' => 'secu_payment_secupay',
                                    'active' => 0,
                                    'position' => 1,
                                    'template' => 'secupay_payment.tpl',
                                    'additionalDescription' => '
<img src="https://www.secupay.ag/sites/default/files/media/Icons/en_GB/secupay_creditcard.png"/>
<div id="payment_desc">
    Pay easily and securely with your credit card.
</div>
'),
        'secupay_debit' => array(
                                'name' => 'secupay_debit',
                                'description' => 'Debit',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 2,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.ag/sites/default/files/media/Icons/en_GB/secupay_debit.png"/>
<div id="payment_desc">
    You pay comfortably by debit.
</div>
'),
        'secupay_sofort' => array(
                                'name' => 'secupay_sofort',
                                'description' => 'SOFORT Banking',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 4,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://cdn.klarna.com/1.0/shared/image/generic/badge/en_GB/pay_now/standard/pink.svg"/>
<div id="payment_desc">
    You pay easily and directly with online banking.
</div>
'),
        'secupay_invoice' => array(
                                'name' => 'secupay_invoice',
                                'description' => 'Invoice',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 3,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.ag/sites/default/files/media/Icons/en_GB/secupay_invoice.png"/>
<div id="payment_desc">
    Pay the amount upon receipt and examination of goods.
</div>
'),
        'secupay_prepay' => array(
                                'name' => 'secupay_prepay',
                                'description' => 'Prepay',
                                'action' => 'secu_payment_secupay',
                                'active' => 0,
                                'position' => 5,
                                'template' => 'secupay_payment.tpl',
                                'additionalDescription' =>  '
<img src="https://www.secupay.ag/sites/default/files/media/Icons/en_GB/secupay_prepay.png"/>
<div id="payment_desc">
     You pay in advance and get your ordered goods after money is received.
</div>
')
        );
    /**
     * @var array
     */
    private $label = array(
        'de_DE' => 'secupay Online Payment',
        'en_GB' => 'secupay Online Payment'
    );
    /**
     * @var array
     */
    private $description = array(
        'de_DE' => 'secupay - einfach.sicher.zahlen',
        'en_GB' => 'secupay - einfach.sicher.zahlen'
    );
    /**
     * @var array
     */
    private $longdescription = array(
        'de_DE' => 'Mit unserem Shopmodul und den integrierten Zahlarten Lastschrift, Kreditkarte, Rechnungskauf, Vorkasse und SOFORT-&Uuml;berweisung bieten Sie einen Payment-Mix an, der bei Ihren Kunden keine Wünsche offen lässt.',
        'en_GB' => 'With our shop module and the integrated Payment Methods debit, credit card , purchase on invoice, Prepay , you’re offering a payment mix that leaves no wishes unfulfilled.'
    );
    /**
     * Method that returns plugin version
     *
     * @return string current version of plugin
     */
    public function getVersion()
    {
        return '2.1.22';
    }

    /**
     * @return mixed|string
     */
    public function getLabel()
    {
        return array_key_exists(Shopware()->Instance()->locale()->toString(), $this->label)?$this->label[Shopware()->Instance()->locale()->toString()]:$this->label['en_GB'];
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return array_key_exists(Shopware()->Instance()->locale()->toString(), $this->label)?$this->label[Shopware()->Instance()->locale()->toString()]:$this->description['en_GB'];
    }

    /**
     * @return mixed
     */
    public function getLongDescription()
    {
        return array_key_exists(Shopware()->Instance()->locale()->toString(), $this->label)?$this->label[Shopware()->Instance()->locale()->toString()]:$this->longdescription['en_GB'];
    }

    /**
     * Method that returns information array about plugin
     *
     * @return associative array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'autor' => 'secupay AG',
            'copyright' => 'Copyright © 2019, Secupay AG',
            // 'license' => '',
            'description' => $this->getDescription(),
            'description_long' => $this->getLongDescription(),
            'support' => 'shopsupport@secupay.ag',
            'link' => 'http://www.secupay.com/'
        );
    }
    
    /**
     * Method that returns capabilities array for plugin
     *
     * @return associative array
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'update' => true,
            'enable' => true
        );
    }

    /**
     * Function that installs the plugin
     *
     * @return array with success field
     */
    public function install()
    {
        try {
            $this->createEvents();
            $this->registerCronJobs();
            $this->createDatabase();
            $this->createForm();
            //$this->createTranslations();
            $availablePaymentTypes = $this->getAvailablePaymentTypes();
            $this->createPaymentTypes($availablePaymentTypes);
            $this->createMenu();
            //$this->installPrepayMail();
            return array('success' => true, 'invalidateCache' => array('config', 'templates'));
        } catch (Exception $e) {
            secupay_log::log(self::log, 'install - Exception '.$e->getMessage());
            secupay_log::log(self::log, 'install - StackTrace '.$e->getTraceAsString());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Function that uninstalls the plugin
     *
     * @return array with success field
     */
    public function uninstall()
    {
        try {
            $this->disable();
            //$this->removePaymentTypes();
            //$this->removeDatabase(); //?

            //return array('success' => true, 'invalidateCache' => array('config'));
            return true;
        } catch (Exception $e) {
            secupay_log::log(self::log, 'uninstall - Exception '.$e->getMessage());
            secupay_log::log(self::log, 'uninstall - StackTrace '.$e->getTraceAsString());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }
    
    /**
     * Function that updates the plugin
     *
     * @return array with success field
     */
    public function update($version)
    {
        //TODO check behaviour with new plugin Name
        secupay_log::log(self::log, 'update from Version '.$version);
        switch ($version) {
            case '2.0.2':
            case '2.0.3':
            case '2.0.4':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                    $this->createEvents();
                    $this->createForm();
                    $this->createMenu();
                    // $this->registerCronJobs();
                    $sql = "ALTER TABLE secupay_transactions ADD COLUMN `payment_status` varchar(255) collate latin1_general_ci default NULL AFTER `trans_id`;";
                    Shopware()->Db()->query($sql);
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.0.4 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.0.4 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
            case '2.0.4.2':
                try {
                    //remove old hooks and events
                    $sql = "DELETE FROM `s_core_subscribes` WHERE `listener` LIKE '%SecupayPayment%'";
                    Shopware()->Db()->query($sql);
                
                    $this->createEvents();
                    $this->registerCronJobs();
                    //remove old configuration setting
                    $repository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
                    $status_goods_sent = $repository->findOneBy(array('name' => 'sSECUPAY_STATUS_GOODS_SENT'));
                    if (!empty($status_goods_sent)) {
                        Shopware()->Models()->remove($status_goods_sent);
                        Shopware()->Models()->flush();
                    }
                    
                    $this->createForm();
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.0.4.2 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.0.4.2 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
            case '2.0.4.3':
                try {
                    $this->registerCronJobs();
                    //add template for payment types
                    foreach ($this->payment_types as $payment_name => $payment_type_def) {
                        $payment = $this->Payments()->findOneBy(array('name' => $payment_name));
                        if (isset($payment_type_def['template'])) {
                            secupay_log::log(self::log, 'update 2.0.4.3: ' . $payment_name . ' - ' . $payment_type_def['template']);
                            $payment->setTemplate($payment_type_def['template']);
                        }
                    }
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.0.4.3 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.0.4.3 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.0':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                    foreach ($this->payment_types as $payment_name => $payment_type_def) {
                        $payment = $this->Payments()->findOneBy(array('name' => $payment_name));
                        
                        if (isset($payment_type_def['additionalDescription'])) {
                            secupay_log::log(self::log, 'update 2.1.1: ' . $payment_name . ' - ' . $payment_type_def['additionalDescription']);
                            $payment->setAdditionalDescription($payment_type_def['additionalDescription']);
                        }
                    }
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.0 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.0 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.2':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.1 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.1 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.3':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.2 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.2 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.4':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                    $this->registerCronJobs();
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.4 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.4 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.15':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                    $sql = "ALTER TABLE secupay_transactions CHANGE `ordernr` `ordernr` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
                    Shopware()->Db()->query($sql);
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.15 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.15 - StackTrace '.$e->getTraceAsString());
                    return false;
                }
                case '2.1.18':
                try {
                    secupay_log::log(self::log, 'update from Version '.$version);
                    $em = $this->get('models');
                    $form = $this->Form();
                    $secupaySendINV = $form->getElement('sSECUPAY_ALL_SWITCH_SEND_INV_AUTO');
                    if ($secupaySendINV !== null) {
                        $em->remove($secupaySendINV);
                    }
                    $secupaySendTRA = $form->getElement('sSECUPAY_ALL_SWITCH_SEND_TRA_AUTO');
                    if ($secupaySendTRA !== null) {
                        $em->remove($secupaySendTRA);
                    }

                    $secupaySwitchWawi = $form->getElement('sSECUPAY_ALL_SWITCH_WAWI');
                    if ($secupaySwitchWawi !== null) {
                        $em->setElement('boolean', 'sSECUPAY_ALL_SWITCH_WAWI', array(
                            'label' => 'externe WAWI',
                            'description' => 'Diese Option Deaktivieren wenn Sie keine externe WAWI verwenden',
                            'value' => 0
                        ));
                    }

                    $secupaySwitchLogo = $form->getElement('sSECUPAY_ALL_SWITCH_LOGO');
                    if ($secupaySwitchLogo !== null) {
                        $em->setElement('boolean', 'sSECUPAY_ALL_SWITCH_LOGO', array(
                            'label' => 'Checkout Logo',
                            'description' => 'das Logo wird auf der Checkout Seite angezeigt',
                            'value' => 1
                        ));
                    }
                    $em->flush();
                    foreach ($this->payment_types as $payment_name => $payment_type_def) {
                        $payment = $this->Payments()->findOneBy(array('name' => $payment_name));

                        if (isset($payment_type_def['additionalDescription']) && $payment_name !='secupay_sofort') {
                            secupay_log::log(self::log, 'update 2.1.20: ' . $payment_name . ' - ' . $payment_type_def['additionalDescription']);
                            $payment->setAdditionalDescription($payment_type_def['additionalDescription']);
                        }
                    }
                } catch (Exception $e) {
                    secupay_log::log(self::log, 'update 2.1.20 - Exception '.$e->getMessage());
                    secupay_log::log(self::log, 'update 2.1.20 - StackTrace '.$e->getTraceAsString());
                    return false;
                }

            default:
        }
        $availablePaymentTypes = $this->getAvailablePaymentTypes();
        $this->createPaymentTypes($availablePaymentTypes);
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'v_send'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `v_send` int(10)default NULL;";
            Shopware()->Db()->query($sql);
        }
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'v_status'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `v_status` int(10)default NULL;";
            Shopware()->Db()->query($sql);
        }
        
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'tracking_code'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `tracking_code` varchar(255) default NULL AFTER `v_send`;";
            Shopware()->Db()->query($sql);
        }
        
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'searchcode'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `searchcode` varchar(255) default NULL AFTER `v_send`;";
            Shopware()->Db()->query($sql);
        }
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'carrier_code'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `carrier_code` varchar(255) default NULL AFTER `v_send`;";
            Shopware()->Db()->query($sql);
        }
        $result = Shopware()->Db()->fetchAll("SHOW COLUMNS FROM secupay_transactions LIKE 'subscription_id'");
        if (!isset($result['0']['Field'])) {
            $sql = "ALTER TABLE secupay_transactions ADD COLUMN `subscription_id` varchar(255) default NULL AFTER `v_send`;";
            Shopware()->Db()->query($sql);
        }
        return true;
    }
    

    /**
     * Function that enables the plugin
     * This function checks if paymentType is available for apikey
     * If new payment Type is available, then it is created and set to active,
     * If payment type is no longer available, then it is deactivated
     *
     * @return array with success field
     */
    public function enable()
    {
        $activePaymentNames = array();
        $availablePaymentTypes = $this->getAvailablePaymentTypes();
        // this should create new payment type if it was added
        $this->createPaymentTypes($availablePaymentTypes);
        $success = false;

        secupay_log::log(self::log, 'Activating available payment types');
        foreach (array_keys($this->payment_types) as $payment_name) {
            $payment = $this->Payments()->findOneBy(array('name' => $payment_name));
            if ($payment !== null) {
                $active = in_array(substr($payment_name, 8), $availablePaymentTypes); // removes 'secupay_' part of payment name and checks if it is in array
                $payment->setActive($active);
                $success = $success || $active;
                if ($active === true) {
                    $activePaymentNames[] = $payment_name;
                }
            }
        }

        if (!empty($activePaymentNames)) {
            $this->updatePayments(1, $activePaymentNames);
        }

        $this->createInvoiceDocumentBox();
        $this->createPrepayDocumentBox();
        return array('success' => $success, 'invalidateCache' => array('config'));
    }

    /**
     * Function that disables the plugin
     *
     * @return array with success field
     */
    public function disable()
    {
        $activePaymentNames = array();
        foreach (array_keys($this->payment_types) as $payment_name) {
            $payment = $this->Payments()->findOneBy(array('name' => $payment_name));
            if ($payment !== null) {
                $payment->setActive(false);
                $activePaymentNames[] = $payment_name;
            }
        }

        if (!empty($activePaymentNames)) {
            $this->updatePayments(0, $activePaymentNames);
        }

        $this->removeInvoiceDocumentBox();
        $this->removePrepayDocumentBox();
        return array('success' => true, 'invalidateCache' => array('config'));
    }

    /**
     * Function that creates Payment types for Payment module
     * PaymentTypes are created only if they are available
     * if they have already been created and they are not available anymore, then payment type is deactivated
     *
     * @param array names of available payment types
     */
    public function createPaymentTypes($availablePaymentTypes)
    {

        // if there are not any available , then disable all
        //$subShops = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->findAll();

        if (empty($availablePaymentTypes)) {
            secupay_log::log(self::log, 'Disabling all Secupay payment types - apikey not valid');
            return $this->disable();
        }

        foreach ($this->payment_types as $payment_name => $payment_type_def) {
            $payment = $this->Payments()->findOneBy(array('name' => $payment_name));
            $name = substr($payment_name, 8); // remove 'secupay_' part of payment name
            if ($payment == null && in_array($name, $availablePaymentTypes)) {
                secupay_log::log(self::log, 'creating payment type: ' . $payment_name);
                $this->createPayment($payment_type_def + array('pluginID' => $this->getId()));
            }
        }
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     */
    public static function onSaveOrderStatus(Enlight_Hook_HookArgs $args)
    {
        try {
            self::runvsend();
        } catch (Exception $e) {
            secupay_log::log(self::log, 'onSaveOrderStatus ' . $e->getMessage());
        }
    }

    /**
     * @param Enlight_Hook_HookArgs $args
     */
    public static function onBeforeRenderDocument(Enlight_Hook_HookArgs $args)
    {
        try {
            self::runvsend();
        } catch (Exception $e) {
            secupay_log::log(self::log, 'onBeforeRenderDocument ' . $e->getMessage());
        }
        
        try {
            $document = $args->getSubject();
        
            if (($document->_order->payment['name'] != 'secupay_invoice' and $document->_order->payment['name'] != 'secupay_prepay')) {
                return;
            }
         
            $view = $document->_view;
            $orderData = array();
            $orderData = $view->getTemplateVars('Order');
            $hash = $orderData['_order']['transactionID'];
        
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $invoice_data = self::secupay_get_invoice_data($hash);
       
            if ($document->_order->payment['name'] != 'secupay_invoice') {
                secupay_log::log(self::log, 'invoice PDF');
            }
            if ($document->_order->payment['name'] != 'secupay_prepay') {
                secupay_log::log(self::log, 'prepay PDF');
            }
        
            secupay_log::log(self::log, $hash);
            secupay_log::log(self::log, $invoice_data);
            secupay_log::log(self::log, print_r($invoice_data, true));
            secupay_log::log(self::log, 'hash: ' . $hash);
            $paymentInstruction['secupay_payment_link']=$invoice_data->payment_link;
            $paymentInstruction['secupay_payment_qr_image_url']=$invoice_data->payment_qr_image_url;
            $paymentInstruction['secupay_recipient_legal']=$invoice_data->recipient_legal;
            $paymentInstruction['secupay_accountowner']=$invoice_data->transfer_payment_data->accountowner;
            $paymentInstruction['secupay_invoice_account']=$invoice_data->transfer_payment_data->accountnumber;
            $paymentInstruction['secupay_invoice_bankcode']=$invoice_data->transfer_payment_data->bankcode;
            $paymentInstruction['secupay_invoice_bank']=$invoice_data->transfer_payment_data->bankname;
            $paymentInstruction['secupay_invoice_iban']=$invoice_data->transfer_payment_data->iban;
            $paymentInstruction['secupay_invoice_bic']=$invoice_data->transfer_payment_data->bic;
            $paymentInstruction['secupay_purpose']=$invoice_data->transfer_payment_data->purpose;

            if ($document->_order->payment['name'] == 'secupay_invoice') {
                if ($pluginConfig->sSECUPAY_INVOICE_SWITCH_DUE_DATE && is_numeric($pluginConfig->sSECUPAY_INVOICE_DAYS_TO_DUE_DATE) && $pluginConfig->sSECUPAY_INVOICE_DAYS_TO_DUE_DATE > 0) {
                    $days = $pluginConfig->sSECUPAY_INVOICE_DAYS_TO_DUE_DATE;
                    $date = new DateTime();
                    $date->modify('+'.$days.' day');
                    $due_date = date_format($date, "d.m.Y");

                    //$document->_template->assign('secupay_due_date_text', 'F&auml;lligkeitsdatum: '.$due_date);
                    $paymentInstruction['secupay_due_date_text']='F&auml;lligkeitsdatum: '.$due_date;
                } else {
                    $paymentInstruction['secupay_due_date_text']= '';
                }
            } else {
                $paymentInstruction['secupay_due_date_text']= 'Die Lieferung erfolgt erst nach Zahlungseingang.';
            }

            $document->_template->addTemplateDir(dirname(__FILE__) . '/Views/');
            $document->_template->assign('instruction', (array)$paymentInstruction);
            $containerData = $view->getTemplateVars('Containers');

            if ($document->_order->payment['name'] == 'secupay_invoice') {
                $containerData['Content_Info_neu'] ='<table>
            <tr>
            <td>
            '.$paymentInstruction['secupay_due_date_text'].'<br />
            <p>
            Der Rechnungsbetrag wurde an die '.$paymentInstruction['secupay_recipient_legal'].', abgetreten. <br />
                            <b>Eine Zahlung mit schuldbefreiender Wirkung ist nur auf folgendes Konto m&ouml;glich.</b><br /><br />

                            Empf&auml;nger: '. $paymentInstruction['secupay_accountowner'].'<br />
                            <b>IBAN: '.$paymentInstruction['secupay_invoice_iban'].'</b><br />
                            BIC: '.$paymentInstruction['secupay_invoice_bic'].', Bank: '.$paymentInstruction['secupay_invoice_bank'].'<br />
                            <b>Verwendungszweck: '.$paymentInstruction['secupay_purpose'].'</b><br /><br />
                            Um diese Rechnung bequem online zu zahlen, k&ouml;nnen Sie den QR-Code mit einem internetf&auml;higen Telefon einscannen <br />oder Sie nutzen diese
            URL: '.$paymentInstruction['secupay_payment_link'].'<br />
            </p>
            </td>
            <td>
            <img src="'.$paymentInstruction['secupay_payment_qr_image_url'].'" width="100" height="100" alt="" />
            </td>
            </tr>
            </table>';
            }
            if ($document->_order->payment['name'] == 'secupay_prepay') {
                $containerData['Content_Info_neu'] ='<table>
            <tr>
            <td>
            '.$paymentInstruction['secupay_due_date_text'].'<br />
            <p>
            Der Rechnungsbetrag wurde an die '.$paymentInstruction['secupay_recipient_legal'].', abgetreten. <br />
                            <b>Eine Zahlung mit schuldbefreiender Wirkung ist nur auf folgendes Konto m&ouml;glich.</b><br /><br />

                            Empf&auml;nger: '. $paymentInstruction['secupay_accountowner'].'<br />
                            <b>IBAN: '.$paymentInstruction['secupay_invoice_iban'].'</b><br />
                            BIC: '.$paymentInstruction['secupay_invoice_bic'].', Bank: '.$paymentInstruction['secupay_invoice_bank'].'<br />
                            <b>Verwendungszweck: '.$paymentInstruction['secupay_purpose'].'</b><br />
            </p>
            </td>
            <td>
           
            </td>
            </tr>
            </table>';
            }
            $containerData['Content_Info']['value'] = $document->_template->fetch('string:'.$containerData['Content_Info_neu']);
            $view->assign('Containers', $containerData);
        } catch (Exception $e) {
            secupay_log::log(self::log, 'onBeforeRenderDocument ' . $e->getMessage());
            return;
        }
    }

    /**
     *
     */
    private function registerCronJobs()
    {
        try {
            $this->createCronJob(
                'Secupay Automatische Versandmeldung',
                'Secupay_Autoversand',
                43200,
                true
            );
            $this->subscribeEvent('Shopware_CronJob_SecupayAutoversand', 'runvsend');
        } catch (Exception $e) {
        }
    }

    /**
     * Function that creates events
     */
    protected function createEvents()
    {
        $this->subscribeEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Frontend_SecuPaymentSecupay',
            'onGetControllerPathFrontend'
        );
        $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Register',
            'onPostDispatchAccount'
        );
        $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Account',
            'onPostDispatchAccount'
        );
        $this->subscribeEvent(
                'Enlight_Controller_Action_PostDispatch_Frontend_Checkout',
            'onPostDispatchAccount'
        );
        $this->subscribeEvent(
                'Shopware_Controllers_Backend_Order::saveAction::after',
            'onSaveOrderStatus'
        );
        $this->subscribeEvent(
                'Shopware_Components_Document::assignValues::after',
            'onBeforeRenderDocument'
        );
        $this->subscribeEvent(
                'Enlight_Controller_Dispatcher_ControllerPath_Backend_SecuPaymentSecupay',
            'onGetControllerPathBackend'
        );
        //** todo send Prepay Bankdata */
        /*$this->subscribeEvent(
                'Shopware_Modules_Order_SendMail_Send',
            'sendPrepayStatusMail'
        );*/
    }

    /**
     * Function that creates database table
     */
    private function createDatabase()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `secupay_transactions` (
              `id` int(10) unsigned NOT NULL auto_increment,
              `req_data` text collate latin1_general_ci,
              `ret_data` text collate latin1_general_ci,
              `payment_method` varchar(255) collate latin1_general_ci default NULL,
              `hash` varchar(255) collate latin1_general_ci default NULL,
              `unique_id` varchar(255) collate latin1_general_ci default NULL,
              `ordernr` int(11) default NULL,
              `trans_id` int(11) default NULL,
              `payment_status` varchar(255) collate latin1_general_ci default NULL,
              `msg` varchar(255) collate latin1_general_ci default NULL,
              `rank` int(10) default NULL,
              `status` int(11) default NULL,
              `v_send` int(11) default NULL,
              `v_status` int(11) default NULL,
			  `subscription_id` varchar(255) collate latin1_general_ci default NULL,
              `invoice_status_change_informed` tinyint(3) default '0',
              `amount` varchar(255) collate latin1_general_ci default NULL,
              `tracking_code` varchar(255) collate latin1_general_ci default NULL,
              `carrier_code` varchar(255) collate latin1_general_ci default NULL,
              `searchcode` varchar(255) collate latin1_general_ci default NULL,
              `action` text collate latin1_general_ci,
              `updated` datetime default NULL,
              `created` datetime default NULL,
              `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`)
            ) ;";
        $this->Application()->Db()->query($sql);
    }

    /**
     * Function that removes created paymentTypes
     */
    private function removePaymentTypes()
    {
        $sql = "DELETE FROM s_core_paymentmeans WHERE name = ?";
        foreach (array_keys($this->payment_types) as $payment_type) {
            $this->Application()->Db()->query($sql, array($payment_type));
        }
    }

    /**
     * Function that removes database tables
     */
    private function removeDatabase()
    {
        return true;
    }

    /**
     * Function that creates configuration form for Plugin
     */
    protected function createForm()
    {
        include('defaults.php');

        $form = $this->Form();

        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_TESTMODE', array(
            'label' => 'Testmodus',
            'value' => 1
        ));
        
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_DEBUGMODE', array(
            'label' => 'Debugmodus',
            'description' => 'Bitte aktivieren Sie diese Einstellung nur nach R&uuml;cksprache mit dem secupay Kundendienst. Es werden zus&auml;tzliche Log Eintr&auml;ge zur Problemanalsyse erzeugt.',
            'value' => 0
        ));

        $form->setElement('text', 'sSECUPAY_ALL_PURPOSE_SHOPNAME', array(
            'label' => 'Shopname im Verwendungszweck',
            'value' => $this->Application()->Config()->Shopname,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_SHOW_ALT_DELIVERY_WARNING', array(
            'label' => 'Warnung bei abweichender Lieferanschrift',
            'value' => 1
        ));

        $form->setElement('text', 'sSECUPAY_APIKEY', array(
            'label' => 'API Key',
            'value' => DEFAULT_APIKEY,
            'description' => 'Ihr APIKey für Secupay Zahlungen.',
            'required' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('boolean', 'sSECUPAY_EXPERIENCE', array(
            'label' => 'Bewertung',
            'description' => 'Teilen Sie uns Ihre Zahlungserfahrungen mit dem Kunden mit (setzt eine entsprechende Freischaltung für den jeweiliegen Vertrag voraus).',
            'value' => 1
        ));
        
        $form->setElement('select', 'sSECUPAY_STATUS_WAITING', array(
            'label' => 'Zahlungsstatus nach Daten&uuml;bermittlung',
            'value' => 18,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der nach der Daten&uuml;bermittlung f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        $form->setElement('select', 'sSECUPAY_STATUS_ACCEPTED_DEBIT', array(
            'label' => 'Zahlungsstatus bei erfolgreichen Transaktionen (Lastschrift)',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei erfolgreichem Abschluss der Lastschrift Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        $form->setElement('select', 'SOFORT', array(
            'label' => 'Zahlungsstatus bei erfolgreichen Transaktionen (SOFORT-&uuml;berweisung)',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei erfolgreichem Abschluss der SOFORT-&uuml;berweisungs Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        
        $form->setElement('select', 'sSECUPAY_STATUS_ACCEPTED_CREDITCARD', array(
            'label' => 'Zahlungsstatus bei erfolgreichen Transaktionen (Kreditkarte)',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei erfolgreichem Abschluss der Kreditkarten Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        
        $form->setElement('select', 'sSECUPAY_STATUS_ACCEPTED_INVOICE', array(
            'label' => 'Zahlungsstatus bei erfolgreichen Transaktionen (Rechnungskauf)',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei erfolgreichem Abschluss der Rechnungskauf Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        $form->setElement('select', 'sSECUPAY_STATUS_ACCEPTED_PREPAY', array(
            'label' => 'Zahlungsstatus bei Transaktionen (Vorkasse) nach Geldeingang',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei der Vorkasse Transaktion sobald die Zahlung eingegangen ist.',
            'required' => true
        ));
        $form->setElement('select', 'sSECUPAY_STATUS_AUTHORIZED_PREPAY', array(
            'label' => 'Zahlungsstatus bei Transaktionen (Vorkasse)',
            'value' => 18,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei der Vorkasse Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));
        
        $form->setElement('select', 'sSECUPAY_STATUS_DENIED', array(
            'label' => 'Zahlungsstatus bei abgelehnten Transaktionen',
            'value' => 35,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei Ablehnung der Transaktion f&uuml;r die Bestellung hinterlegt wird.',
            'required' => true
        ));

        $form->setElement('select', 'sSECUPAY_STATUS_ISSUE', array(
            'label' => 'Zahlungsstatus bei Zahlungsproblemen',
            'value' => 21,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei Problemen mit der Zahlung f&uuml;r die Bestellung hinterlegt wird.'
        ));

        $form->setElement('select', 'sSECUPAY_STATUS_VOID', array(
            'label' => 'Zahlungsstatus bei stornierten Transaktionen',
            'value' => 21,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei Stornierung oder Gutschrift der Transaktion f&uuml;r die Bestellung hinterlegt wird.'
        ));

        $form->setElement('select', 'sSECUPAY_STATUS_AUTHORIZED', array(
            'label' => 'Zahlungsstatus bei vorautorisierten Transaktionen',
            'value' => 21,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'description' => 'Der Zahlungsstatus der bei Vorautorisierung der Transaktion f&uuml;r die Bestellung hinterlegt wird.'
        ));
        $form->setElement('boolean', 'sSECUPAY_INVOICE_SWITCH_DUE_DATE', array(
            'label' => 'Zahlungsfristhinweis bei Rechnungsdruck anzeigen',
            'value' => 0
        ));
        
        $form->setElement('number', 'sSECUPAY_INVOICE_DAYS_TO_DUE_DATE', array(
            'label' => 'Zahlungsfrist in Tagen für Rechnungsdruck',
            'value' => 14
        ));
        
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_SEND_LAST_HASH', array(
            'label' => 'Letzte Zahlungsmitteldaten vorschlagen',
            'value' => 0
        ));
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_SEND_INV_RE', array(
            'label' => 'Rechnungsnummer anstatt Bestellnummer übermitteln',
            'description' => 'Bei deaktivierter Option wird anstelle der Rechnungsnummer die Bestellnummer bei der automatischen Versandmeldung verwendet.Bei externer WAWI wird immer die Bestellnummer übermittelt.',
            'value' => 1
        ));
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_SEND_EAN', array(
            'label' => 'EAN des Artikels mit übermitteln',
            'description' => 'Diese Option Deaktivieren wenn Sie Artikel ohne Artikel ID verwenden z.b. Bonussystem, Virtuelle Artikel',
            'value' => 0
        ));
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_WAWI', array(
            'label' => 'externe WAWI',
            'description' => 'bei Verwendung einer externen WAWI aktivieren',
            'value' => 0
        ));
        $form->setElement('boolean', 'sSECUPAY_ALL_SWITCH_LOGO', array(
            'label' => 'Checkout Logo',
            'description' => 'das Logo wird auf der Checkout Seite angezeigt',
            'value' => 1
        ));
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $form->setParent(
                $repository->findOneBy(array('name' => 'Interface'))
        );
    }

    /**
     * Function that creates translations
     * Not implemented correctly
     */
    /*
    public function createTranslations() {
        $form = $this->Form();
        $translations = array(
            'en_GB' => array(
                'sSECUPAY_APIKEY' => 'API Key',
            )
        );

        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        foreach ($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
                    ));
            foreach ($snippets as $element => $snippet) {
                if ($localeModel === null) {
                    continue;
                }
                $elementModel = $form->getElement($element);
                if ($elementModel === null) {
                    continue;
                }
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);
                $elementModel->addTranslation($translationModel);
            }
        }
    }*/

    /**
     * Function that registers our class to path
     *
     * @return string
     */
    public function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
        secupay_log::log(self::log, 'onGetControllerPathFrontend_function' .$args);
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/Frontend/SecuPaymentSecupay.php';
    }

    /**
     * just points to the path of this controller
     *
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/Backend/SecuPaymentSecupay.php';
    }
    
    /**
     *
     */
    public function onPostDispatchAccount(Enlight_Event_EventArgs $args)
    {
        $view = $args->getSubject()->View();
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        
        //check if dispatch is valid
        if (empty($view) || empty($request) || empty($response) || !$request->isDispatched()
                || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        if ($request->getControllerName() == 'account' && ($request->getActionName() == 'payment' || $request->getActionName() == 'savePayment')
                || $request->getControllerName() == 'register'
                || $request->getControllerName() == 'checkout') {
            $view->addTemplateDir($this->Path() . '/Views/');
            $view->assign($this->getTemplateVars());
        }
    }
    
    /**
    * Creates the secupay backend menu item.
    *
    * The secupay menu item opens the listing of secupay transactions.
    */
    public function createMenu()
    {
        $this->createMenuItem(array(
         'label' => 'secupay Transaktionsliste',
         'controller' => 'SecuPaymentSecupay',
         'class' => 'sprite-credit-cards',
         'action' => 'Index',
         'active' => 1,
         'parent' => $this->Menu()->findOneBy(['id' => 20])
      ));
    }

    /**
     * Function returns all available transaction statuses for shopware
     *
     * @param string groupType - Default payment or state
     * @return array
     */
    private function getPaymentStatus($groupType = 'payment')
    {
        $states = $this->Application()->Db()->fetchAll("
SELECT `id`, `description`
FROM `s_core_states`
WHERE `group` = ?
ORDER BY `position`", array($groupType));

        foreach ($states as $row) {
            $paymentState[] = array(
                $row['id'],
                $row['description']
            );
        }
        secupay_log::log(self::log, 'getPaymentStatus:'. print_r($paymentState, true));
        return $paymentState;
    }

    /**
     * @return array
     */
    private function getTemplateVars()
    {
        $pluginConfig = $this->Application()->Plugins()->Frontend()->SecuSecupayPayment()->Config();

        $template_vars = array(
            'secupay_show_alt_delivery_warning' => $pluginConfig->sSECUPAY_ALL_SWITCH_SHOW_ALT_DELIVERY_WARNING,
            'secupay_show_payment_logo' => $pluginConfig->sSECUPAY_ALL_SWITCH_LOGO,
            'secupay_delivery_address_differs' => $this->checkDeliveryDifference(),
            'secupay_payment_lang' => $this->getPaymentLang()
        );
        return $template_vars;
    }

    /**
     * @return boolean
     */
    private function checkDeliveryDifference()
    {
        $template_vars = $this->Application()->Template()->getTemplateVars();

        $shipping_address = $template_vars['sUserData']['shippingaddress'];
        $billing_address = $template_vars['sUserData']['billingaddress'];

        $address_diff = array_diff($shipping_address, $billing_address);

        return !empty($address_diff);
    }

    /**
     * @return string
     */
    private function getPaymentLang()
    {
        $shopContext = $this->get('shopware_storefront.context_service')->getShopContext();
        $lang = $shopContext->getShop()->getLocale()->getLocale();
        if ($lang == 'de_DE') {
            return 'de_de';
        }
        return 'en_us';
    }
    /**
     * Function that returns array of available paymentTypes for the apikey
     *
     * @return array of strings or null
     */
    private function getAvailablePaymentTypes()
    {
        $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
        $sql="SELECT shop_id FROM s_core_config_values GROUP BY shop_id ";
        $sql_api_shop="SELECT
            s_core_config_values.`value`
            FROM
            s_core_config_elements
            INNER JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id AND s_core_config_elements.`name` = 'sSECUPAY_APIKEY' AND s_core_config_values.shop_id = ?";
        unset($data);
        unset($apikey_array);
        unset($api_key_shop_temp);
        $data = Shopware()->Db()->fetchAll($sql);

        if (count($data)>0) {
            foreach ($data as $row => $shop_key_data) {
                unset($api_key_temp);
                $api_key_shop_temp = Shopware()->Db()->fetchone($sql_api_shop, array($shop_key_data['shop_id']));

                if (empty($api_key_shop_temp)and $shop_key_data['shop_id']==1) {
                    $api_key_shop_temp = serialize($pluginConfig->sSECUPAY_APIKEY);
                }
                $api_key_temp=$this->getApiTypes(unserialize($api_key_shop_temp));

                if (empty($apikey_array)) {
                    if (!empty($api_key_temp)) {
                        $apikey_array=$api_key_temp;
                    }
                } else {
                    if (!empty($api_key_temp)) {
                        $apikey_array=$apikey_array+$api_key_temp;
                    }
                }
            }
        } else {
            $api_key_shop_temp = $pluginConfig->sSECUPAY_APIKEY;
            $apikey_array=$this->getApiTypes($api_key_shop_temp);
        }
        return $apikey_array;
    }

    /**
     * @param $api_key_data
     * @return bool
     */
    private function getApiTypes($api_key_data)
    {
        if (empty($api_key_data)) {
            return false;
        }
        $data = array();
        $data['apikey'] = $api_key_data;
        $sp_api = new secupay_api($data, 'gettypes', 'application/json', true);
        $api_return = $sp_api->request();
        
        if (isset($api_return) && $api_return instanceof secupay_api_response && $api_return->check_response()) {
            return $api_return->data;
        } else {
            return false;
        }
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    private function createInvoiceDocumentBox()
    {
        $sql = "
          INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) 
          SELECT 
          1, 'Secupay_Invoice_Content_Info', '', CONCAT_WS(' ', `s_core_documents_box`.`value`, ?)
          FROM `s_core_documents_box`
          WHERE `s_core_documents_box`.documentID = 1 AND `s_core_documents_box`.`name` LIKE 'Content_Info';";

        $this->Application()->Db()->query($sql, array(
            '<table>
<tr>
<td>
{$secupay_due_date_text}<br />
<p>
Der Rechnungsbetrag wurde an die {$secupay_recipient_legal}, abgetreten. <br />
                <b>Eine Zahlung mit schuldbefreiender Wirkung ist nur auf folgendes Konto m&ouml;glich:</b><br /><br />

                Empf&auml;nger: {$secupay_accountowner}<br />
                Kontonummer: {$secupay_invoice_account}, BLZ: {$secupay_invoice_bankcode}, Bank: {$secupay_invoice_bank}<br />
                IBAN: {$secupay_invoice_iban}, BIC: {$secupay_invoice_bic}<br />
                <b>Verwendungszweck: {$secupay_purpose}</b><br /><br />
                Um diese Rechnung bequem online zu zahlen, k&ouml;nnen Sie den QR-Code mit einem internetf&auml;higen Telefon einscannen <br />oder Sie nutzen diese
URL: {$secupay_payment_link}<br />
</p>
</td>
<td>
<img src="{$secupay_payment_qr_image_url}" width="100" height="100" alt="" />
</td>
</tr>
</table>'
        ));
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    private function removeInvoiceDocumentBox()
    {
        $sql = "DELETE FROM s_core_documents_box WHERE s_core_documents_box.documentID = 1 AND s_core_documents_box.name LIKE 'Secupay_Invoice_Content_Info';";
        $this->Application()->Db()->query($sql);
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    private function createPrepayDocumentBox()
    {
        $sql = "
          INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) 
          SELECT 
          1, 'Secupay_Prepay_Content_Info', '', CONCAT_WS(' ', `s_core_documents_box`.`value`, ?)
          FROM `s_core_documents_box`
          WHERE `s_core_documents_box`.documentID = 1 AND `s_core_documents_box`.`name` LIKE 'Content_Info';";

        $this->Application()->Db()->query($sql, array(
            '<table>
<tr>
<td>
{$secupay_due_date_text}<br />
<p>
Der Rechnungsbetrag wurde an die {$secupay_recipient_legal}, abgetreten. <br />
                <b>Eine Zahlung mit schuldbefreiender Wirkung ist nur auf folgendes Konto m&ouml;glich:</b><br /><br />

                Empf&auml;nger: {$secupay_accountowner}<br />
                Kontonummer: {$secupay_invoice_account}, BLZ: {$secupay_invoice_bankcode}, Bank: {$secupay_invoice_bank}<br />
                IBAN: {$secupay_invoice_iban}, BIC: {$secupay_invoice_bic}<br />
                <b>Verwendungszweck: {$secupay_purpose}</b><br /><br />
                Um diese Rechnung bequem online zu zahlen, k&ouml;nnen Sie den QR-Code mit einem internetf&auml;higen Telefon einscannen <br />oder Sie nutzen diese
URL: {$secupay_payment_link}<br />
</p>
</td>
<td>
<img src="{$secupay_payment_qr_image_url}" width="100" height="100" alt="" />
</td>
</tr>
</table>'
        ));
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    private function removePrepayDocumentBox()
    {
        $sql = "DELETE FROM s_core_documents_box WHERE s_core_documents_box.documentID = 1 AND s_core_documents_box.name LIKE 'Secupay_Prepay_Content_Info';";
        $this->Application()->Db()->query($sql);
    }

    /**
     * @param $hash
     * @return bool
     */
    public static function secupay_get_invoice_data($hash)
    {
        if (!empty($hash)) {
            $request = array();
            $request['hash'] = $hash;
            $sql="SELECT
                    secupay_transactions.req_data
                    FROM
                    secupay_transactions
                    where secupay_transactions.hash = ?
                    ";
            unset($data);
            $request['apikey'] =json_decode(stripcslashes(Shopware()->Db()->fetchone($sql, array($hash))))->apikey;
            $sp_api = new secupay_api($request, 'status');
            $response = $sp_api->request();

            if ($response->check_response() && isset($response->data->opt)) {
                return $response->data->opt;
            }
        }
        return false;
    }

    /**
     * @param int $status
     * @param array $payment_names
     * @throws Zend_Db_Adapter_Exception
     */
    private function updatePayments($status = 1, array $payment_names)
    {
        $sql = "UPDATE s_core_paymentmeans SET active = ". $status . " WHERE name = ? ;";
        foreach ($payment_names as $payment_name) {
            $this->Application()->Db()->query($sql, array($payment_name));
        }
    }

    /**
     * @throws Zend_Db_Adapter_Exception
     */
    public function runvsend()
    {
        secupay_log::log(self::log, 'automatische versandmeldung:1');
        $inv_is_active =  Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config()->sSECUPAY_ALL_SWITCH_SEND_INV_RE;
        $is_wawi_active =  Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config()->sSECUPAY_ALL_SWITCH_WAWI;

        $sqlapi="SELECT
                    secupay_transactions.req_data
                    FROM
                    secupay_transactions
                    where secupay_transactions.hash = ?
                    ";
        if ($is_wawi_active =='1') {
            secupay_log::log(self::log, 'automatische versandmeldung:wawi:1');
            $sql = "SELECT
                s_order.ordernumber,
                s_order.trackingcode,
                secupay_transactions.`hash`,
                secupay_transactions.id,
                secupay_transactions.searchcode,
                secupay_transactions.v_send,
                secupay_transactions.payment_method,
                s_premium_dispatch.`name`,
                s_order.`status`
                FROM
                secupay_transactions
                INNER JOIN s_order ON secupay_transactions.ordernr = s_order.ordernumber
                LEFT JOIN s_premium_dispatch ON s_premium_dispatch.id = s_order.dispatchID
                INNER JOIN s_order_history ON s_order_history.orderID = s_order.id
                WHERE
                s_order.`status` in (7,2) AND
                secupay_transactions.v_send IS NULL AND
               (secupay_transactions.payment_status = 'accepted' OR
				secupay_transactions.payment_status = 'issue') AND
                (s_order_history.payment_status_id = 10 AND
                s_order_history.previous_payment_status_id IN (18, 21))";
        } else {
            $sql = "SELECT
                s_order.ordernumber,
                s_order_documents.docID,
                s_order_documents.type,
                s_order.trackingcode,
                secupay_transactions.`hash`,
                secupay_transactions.id,
                secupay_transactions.searchcode,
                secupay_transactions.v_send,
                secupay_transactions.payment_method,
                s_premium_dispatch.`name`,
                s_order.`status`,
                s_order_documents.date
                FROM
                secupay_transactions
                INNER JOIN s_order ON secupay_transactions.ordernr = s_order.ordernumber
                INNER JOIN s_order_documents ON s_order_documents.orderID = s_order.id
                LEFT JOIN s_premium_dispatch ON s_premium_dispatch.id = s_order.dispatchID
                INNER JOIN s_order_history ON s_order_history.orderID = s_order.id
                WHERE
                s_order.`status` in (7,2) AND
                secupay_transactions.v_send IS NULL AND
                s_order_documents.type = '1' AND
				(secupay_transactions.payment_status = 'accepted' OR
				secupay_transactions.payment_status = 'issue') AND
                (s_order_history.payment_status_id = 10 AND
                s_order_history.previous_payment_status_id IN (18, 21))";
        }
        $update_vsends = Shopware()->Db()->fetchAll($sql);
        $beginn = time()-3600;
        foreach ($update_vsends as $update_vsend) {
             if ($update_vsend['payment_method']=='invoice' || $update_vsend['payment_method']=='prepay' || $update_vsend['payment_method']=='debit' || $update_vsend['payment_method']=='creditcard' || $update_vsend['payment_method']=='sofort') {
                if ($inv_is_active == '1' && $is_wawi_active == '0') {
                    $searchcode = $update_vsend['docID'];
                } else {
                    $searchcode = $update_vsend['ordernumber'];
                }
                $sqlSearchCode = "UPDATE secupay_transactions SET searchcode = '". $searchcode . "' WHERE id = '".$update_vsend['id']."' ;";
                Shopware()->Db()->query($sqlSearchCode);
                $ende = strtotime($update_vsend['date']);
                $trackingcode='k999';
                if (empty($update_vsend['trackingcode']) and $ende<$beginn) {
                    $trackingcode='keiner Uebergeben';
                } elseif (!empty($update_vsend['trackingcode'])) {
                    $trackingcode=$update_vsend['trackingcode'];
                }
                if ($trackingcode!='k999') {
                    $update_vsend['name']=self::getCarrier($update_vsend['trackingcode'], $update_vsend['name']);
                    $data['hash']=$update_vsend['hash'];
                    $data['apikey'] =json_decode(stripcslashes(Shopware()->Db()->fetchone($sqlapi, array($data['hash']))))->apikey;
                    $data['invoice_number']=$searchcode;
                    $data['tracking']['provider']=$update_vsend['name'];
                    $data['tracking']['number']=$trackingcode;
                    $sp_api = new secupay_api($data, self::getPayment($update_vsend['payment_method']), 'application/json', true);
                    $api_return = $sp_api->request();
                    if ($api_return->status=='ok' or utf8_decode($api_return->errors[0]->message)=='Zahlung konnte nicht abgeschlossen werden') {
                        $vtrack = $api_return->data-trans_id;
                        $sql = "UPDATE secupay_transactions SET v_status='".$vtrack."',tracking_code='".$update_vsend['trackingcode']."',carrier_code='".$update_vsend['name']."', v_send = '1' WHERE id = '".$update_vsend['id']."' ;";
                        Shopware()->Db()->query($sql);
                    }
                    secupay_log::log(self::log, '$api_return:'.print_r($api_return, true));
                }
            }
        }
    }

    /**
     * @param $trackingnumber
     * @param $provider
     * @return string
     */
    public function getCarrier($trackingnumber, $provider)
    {
        if (
            preg_match("/^1Z\s?[0-9A-Z]{3}\s?[0-9A-Z]{3}\s?[0-9A-Z]{2}\s?[0-9A-Z]{4}\s?[0-9A-Z]{3}\s?[0-9A-Z]$/i", $trackingnumber)) {
            $resprovider = "UPS";
        } elseif (
            preg_match("/^\d{14}$/", $trackingnumber)) {
            $resprovider = "HLG";
        } elseif (
            preg_match("/^\d{11}$/", $trackingnumber)) {
            $resprovider = "GLS";
        } elseif (
            preg_match("/[A-Z]{3}\d{2}\.?\d{2}\.?(\d{3}\s?){3}/", $trackingnumber) ||
            preg_match("/[A-Z]{3}\d{2}\.?\d{2}\.?\d{3}/", $trackingnumber) ||
            preg_match("/(\d{12}|\d{16}|\d{20})/", $trackingnumber)) {
            $resprovider = "DHL";
        } elseif (
            preg_match("/RR\s?\d{4}\s?\d{5}\s?\d(?=DE)/", $trackingnumber) ||
            preg_match("/NN\s?\d{2}\s?\d{3}\s?\d{3}\s?\d(?=DE(\s)?\d{3})/", $trackingnumber) ||
            preg_match("/RA\d{9}(?=DE)/", $trackingnumber) || preg_match("/LX\d{9}(?=DE)/", $trackingnumber) ||
            preg_match("/LX\s?\d{4}\s?\d{4}\s?\d(?=DE)/", $trackingnumber) ||
            preg_match("/LX\s?\d{4}\s?\d{4}\s?\d(?=DE)/", $trackingnumber) ||
            preg_match("/XX\s?\d{2}\s?\d{3}\s?\d{3}\s?\d(?=DE)/", $trackingnumber) ||
            preg_match("/RG\s?\d{2}\s?\d{3}\s?\d{3}\s?\d(?=DE)/", $trackingnumber)) {
            $resprovider = "DPAG";
        } else {
            $resprovider = $provider;
        }
        return $resprovider;
    }

    /**
     * @param $payment
     * @return string
     */
    public function getPayment($payment)
    {
        if ($payment=='invoice') {
            return 'capture' ;
        } else {
            return 'adddata';
        }
    }

    /**
     * @return void|Zend_Db_Statement_Pdo
     */
    private function installPrepayMail()
    {
        try {
            $sql = '
            INSERT INTO `s_core_config_mails` (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE frommail= ?, fromname= ?, subject= ?, content= ?, contentHTML= ?, ishtml= ?, attachment= ?, mailtype= ?, context= ?';
            
            $prms_id                     = null;
            $prms_stateId             = null;
            $prms_name                 = 'SecupayPrepayMail';
            $prms_frommail         = '{config name=mail}';
            $prms_fromname         = '{config name=shopName}';
            $prms_subject             = 'Vorkassedaten zu Ihrer Bestellung {$ordernumber} bei {config name=shopName}';
            $prms_content             = 'Sehr geehrter Kunde,

vielen Dank fuer Ihre Bestellung in userem Shop.

Bitte nutzen Sie zum Zahlen Ihrer Bestellung folgende Bezahldaten.

Betrag: {$AMOUNT} {$CURRENCY}
Kontoinhaber: {$ACCOUNTOWNER}
Konto-Nr.: {$ACCOUNTNUMBER}
Bankleitzahl: {$BANKCODE}
IBAN: {$IBAN}
BIC: {$BIC}

Um eine schnelle Bearbeitung gewaehrleisten zu koennen, geben Sie bitte als Verwendungszweck nur diese Nummer an.
Verwendungszweck: {$PURPOSE}


Vielen Dank

Mit freundlichen Gruessen

{config name=shopName}
{config name=address}';
            $prms_contentHTML    = 'Sehr geehrter Kunde,<br/><br/>vielen Dank f&uuml;r Ihre Bestellung in userem Shop.<br><br/>Bitte nutzen Sie zum Zahlen Ihrer Bestellung folgende Bezahldaten.<br/><br/>Betrag: {$AMOUNT} {$CURRENCY}<br/>Kontoinhaber: {$ACCOUNTOWNER}<br/>Konto-Nr.: {$ACCOUNTNUMBER}<br/>Bankleitzahl: {$BANKCODE}<br/>IBAN: {$IBAN}<br/>BIC: {$BIC}<br/><br/>Um eine schnelle Bearbeitung gew&auml;hrleisten zu k&ouml;nnen, geben Sie bitte als Verwendungszweck nur diese Nummer an.<br/>Verwendungszweck: {$PURPOSE}<br/><br/><br/>Vielen Dank<br/><br/>Mit freundlichen Gr&uuml;&szlig;en<br/><br/>{config name=shopName}<br/>{config name=address}';
            $prms_ishtml                 = 1;
            $prms_attachment     = '';
            $prms_mailtype             = 1;
            $prms_context             = 'a:10:{s:6:"AMOUNT";s:5:"73.98";s:8:"CURRENCY";s:3:"EUR";s:25:"COUNTRY";s:3:"DE\n";s:24:"ACCOUNTOWNER";s:25:"secupay AG\n";s:24:"ACCOUNTNUMBER";s:11:"0123456789\n";s:22:"BANKCODE";s:9:"01234567\n";s:22:"IBAN";s:23:"DE00012345678912345678\n";s:21:"BIC";s:12:"COBADEFF103\n";s:22:"PURPOSE";s:18:"\n\n2311.5548.6334\n\n";s:11:"ordernumber";s:5:"20028";}';
            
            $params = array($prms_id, $prms_stateId, $prms_name, $prms_frommail, $prms_fromname, $prms_subject, $prms_content, $prms_contentHTML, $prms_ishtml, $prms_attachment, $prms_mailtype, $prms_context, $prms_frommail, $prms_fromname, $prms_subject, $prms_content, $prms_contentHTML, $prms_ishtml, $prms_attachment, $prms_mailtype, $prms_context);

            return Shopware()->Db()->query($sql, $params);
        } catch (Exception $e) {
            $this->Logging('installPrepayMail | '.$e->getMessage());
            return;
        }
    }
}
