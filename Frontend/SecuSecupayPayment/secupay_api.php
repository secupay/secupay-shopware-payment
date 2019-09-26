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
 * Helper classes that creates HTTP requests to Secupay API
 *
 */

define('SECUPAY_HOST', 'api.secupay.ag');
/**
 *
 */
define('SECUPAY_URL', 'https://'.SECUPAY_HOST.'/payment/');
/**
 *
 */
define('SECUPAY_PATH', '/payment/');
/**
 *
 */
define('SECUPAY_PORT', 443);
/**
 *
 */
define('SECUPAY_HOST_PUSH', 'connect.secupay.ag');


if (!class_exists("secupay_log")) {

    /**
     * logging class
     */
    class secupay_log
    {
        /**
         * @var string
         */
        public static $logfile = "var/log/splog.php";

        /**
         * logging function
         *
         * @param bool log - if false, the log will not be done
         */
        public static function log($log)
        {
            if (!$log) {
                return;
            }
            
            //prevent access to logfile
            if (!file_exists(self::$logfile)) {
                file_put_contents(self::$logfile, "<?php die('Nothing to see here.'); ?>\n", FILE_APPEND);
            }
            
            $date = date("r");
            $x = 0;
            foreach (func_get_args() as $val) {
                $x++;
                if ($x == 1) {
                    continue;
                }
                if (is_string($val) || is_numeric($val)) {
                    file_put_contents(self::$logfile, "[{$date}] {$val}\n", FILE_APPEND);
                    Shopware()->PluginLogger()->info($val);
                } else {
                    file_put_contents(self::$logfile, "[{$date}] " . print_r($val, true) . "\n", FILE_APPEND);
                    Shopware()->PluginLogger()->info(print_r($val, true));
                }
            }
        }
    }
}

if (!class_exists("secupay_api")) {

    /**
     * Class that creates request for API
     */
    class secupay_api
    {
        /**
         * @var string
         */
        public $req_format;
        /**
         * @var array
         */
        public $data;
        /**
         * @var string
         */
        public $req_function;
        /**
         * @var
         */
        public $sent_req;
        /**
         * @var
         */
        public $error;
        /**
         * @var bool
         */
        public $sp_log;
        /**
         * @var string
         */
        public $language;

        /**
         * Contructor
         */
        public function __construct($params, $req_function = 'init', $format = 'application/json', $sp_log = false, $language = 'de_de')
        {
            $this->req_function = $req_function;
            $this->req_format = $format;
            $this->sp_log = $sp_log;
            $this->language = $language;
            $this->data = array(
                'data' => $params
            );
        }

        /**
         * Public class that returns answer from API
         *
         * @returns object type secupay_api_response
         */
        public function request()
        {
            $rc = null;
            if (function_exists("curl_init")) {
                $rc = $this->request_by_curl();
            } else {
                $rc = $this->request_by_socketstream();
            }

            return $rc;
        }

        /**
         * Function that creates Curl request
         */
        public function request_by_curl()
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            $_data = json_encode($this->data);

            $http_header = array(
                'Accept: '.$this->req_format,
                'Content-Type: application/json',
                'Accept-Language: '.$this->language,
                'User-Agent: Shopware 4 client 1.1',
                'Content-Length: ' . strlen($_data)
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, SECUPAY_URL . $this->req_function);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);

            secupay_log::log($log, 'CURL request for '. SECUPAY_URL . $this->req_function.' in format : '.$this->req_format .' language: '.$this->language);
            secupay_log::log($log, $_data);

            $rcvd = curl_exec($ch);
            secupay_log::log($log, 'Response: ' . $rcvd);

            $this->sent_data = $_data;
            $this->recvd_data = $rcvd;

            curl_close($ch);
            return $this->parse_answer($this->recvd_data);
        }

        /**
         * @param $ret
         * @return secupay_api_response
         */
        public function parse_answer($ret)
        {
            switch (strtolower($this->req_format)) {
                case "application/json":
                    $answer = json_decode($ret);
                    break;
                case "text/xml":
                    $answer = simplexml_load_string($ret);
                    break;
            }
            #return $answer;
            $api_response = new secupay_api_response($answer);
            return $api_response;
        }

        /**
         * Function that request by socketstream (when CURL library is not available)
         */
        public function request_by_socketstream()
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            $_data = json_encode($this->data);

            $rcvd = "";
            $rcv_buffer = "";
            $fp = fsockopen('ssl://' . SECUPAY_HOST, SECUPAY_PORT, $errstr, $errno);

            if (!$fp) {
                $this->error = "can't connect to secupay api";
                return false;
            }
            $req = "POST ".SECUPAY_PATH . $this->req_function." HTTP/1.1\r\n";
            $req.= "Host: ".SECUPAY_HOST."\r\n";
            $req.= "Content-type: application/json; Charset:UTF8\r\n";
            $req.= "Accept: ".$this->req_format."\r\n";
            $req.= "User-Agent: Shopware 4 client 1.1\r\n";
            $req.= "Accept-Language: ".$this->language."\r\n";
            $req.= "Content-Length: ". strlen($_data). "\r\n";
            $req.= "Connection: close\r\n\r\n";
            $req.= $_data;

            secupay_log::log($log, 'SOCKETSTREAM request for '. SECUPAY_URL . $this->req_function.' in format : '.$this->req_format .' language: '.$this->language);
            secupay_log::log($log, $_data);

            fputs($fp, $req);

            while (!feof($fp)) {
                $rcv_buffer = fgets($fp, 128);
                $rcvd .= $rcv_buffer;
            }
            fclose($fp);

            $pos = strpos($rcvd, "\r\n\r\n");
            $rcvd = substr($rcvd, $pos + 4);

            secupay_log::log($log, 'Response: ' . $rcvd);

            $this->sent_data = $_data;
            $this->recvd_data = $rcvd;

            return $this->parse_answer($this->recvd_data);
        }

        /**
         * @return string
         */
        public static function get_api_version()
        {
            return '2.3';
        }
    }
}

if (!class_exists("secupay_api_response")) {

    /**
     * this class should be a wrapper for secupay response
     */
    class secupay_api_response
    {
        /**
         * @var
         */
        public $status;
        /**
         * @var
         */
        public $data;
        /**
         * @var
         */
        public $errors;
        /**
         * @var
         */
        public $raw_data;

        /**
         * Contructor
         */
        public function __construct($answer)
        {
            $this->status = $answer->status;
            $this->errors = $answer->errors;
            $this->data = $answer->data;
            $this->raw_data = $answer;
        }

        /**
         * @param bool $log_error
         * @return bool
         */
        public function check_response($log_error = false)
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;

            if (strtolower($this->status) != 'ok') {
                secupay_log::log($log, "secupay_api_response status: ", $this->status);
                return false;
            };
            if (count($this->errors) > 0) {
                secupay_log::log($log, "secupay_api_response error: ", $this->errors);
                return false;
            }
            if (count($this->data) == 0) {
                secupay_log::log($log, "secupay_api_response error: no data in response");
                return false;
            }
            return true;
        }

        /**
         * @return bool
         */
        public function get_hash()
        {
            if (isset($this->data->hash)) {
                return $this->data->hash;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function get_iframe_url()
        {
            if (isset($this->data->iframe_url)) {
                return $this->data->iframe_url;
            }
            return false;
        }

        /**
         * @return bool
         */
        public function get_subscription_id()
        {
            if (isset($this->data->subscription_id)) {
                return $this->data->subscription_id;
            }
            return false;
        }

        /**
         * @param bool $log_error
         * @return bool
         */
        public function get_status($log_error = false)
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            secupay_log::log($log, "secupay_api_response get_status: " . $this->status);
            if (isset($this->status)) {
                return $this->status;
            }
            return false;
        }

        /**
         * @param bool $log_error
         * @return bool|string
         */
        public function get_error_message($log_error = false)
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            if (empty($this->errors)) {
                return false;
            }
            $message = '';
            foreach ($this->errors as $error) {
                $message .= '(' . $error->code . ') ' . $error->message . '<br>';
                if (isset($error->field)) {
                    $message .= $error->field . '<br>';
                }
            }
            secupay_log::log($log, "secupay_api_response get_error_message: " . $message);
            return $message;
        }

        /**
         * @param bool $log_error
         * @return bool|string
         */
        public function get_error_message_abo($log_error = false)
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            if (empty($this->errors)) {
                return false;
            }
            $message = '';
            foreach ($this->errors as $error) {
                $message .= '(' . $error->code . ') ' . $error->message ;
                if (isset($error->field)) {
                    $message .= $error->field ;
                }
            }
            secupay_log::log($log, "secupay_api_response get_error_message: " . $message);
            return $message;
        }

        /**
         * @param bool $log_error
         * @return bool|string
         */
        public function get_error_message_user($log_error = false)
        {
            $pluginConfig = Shopware()->Plugins()->Frontend()->SecuSecupayPayment()->Config();
            $log = $pluginConfig->sSECUPAY_ALL_SWITCH_DEBUGMODE;
            if (empty($this->errors)) {
                return false;
            }
            $message = '';
            foreach ($this->errors as $error) {
                $message .= '(' . $error->code . ')';
                if ($this->status == 'failed') {
                    $message .= ' ' . $error->message;
                }
            }
            secupay_log::log($log, "secupay_api_response get_error_message_user: " . $message);
            return $message;
        }
    }
}
