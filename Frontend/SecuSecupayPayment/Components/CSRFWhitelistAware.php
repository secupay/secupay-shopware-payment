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

namespace Shopware\Components;

if (!interface_exists('\Shopware\Components\CSRFWhitelistAware')) {
    /**
     * Interface CSRFWhitelistAware
     * @package Shopware\Components
     */
    interface CSRFWhitelistAware
    {
    }
}
