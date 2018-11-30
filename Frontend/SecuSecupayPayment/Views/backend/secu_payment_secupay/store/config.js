/*
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

Ext.define('Shopware.apps.SecuPaymentSecupay.store.Config', {
    /**
     * Extend for the standard ExtJS 4
     * @string
     */
    extend:'Ext.data.Store',
    /**
     * Auto load the store after the component
     * is initialized
     * @boolean
     */
    autoLoad: false,
 
    /**
     * to upload all selected items in one request
     * @boolean
     */
    batch: true,
    /**
     * sets remote sorting true
     * @boolean
     */
    remoteSort: false,
 
    remoteFilter : true,
    /**
     * Amount of data loaded at once
     * @integer
     */
    pageSize:30,
    /**
     * Define the used model for this store
     * @string
     */
    model:'Shopware.apps.SecuPaymentSecupay.model.Config'
});