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

Ext.define('Shopware.apps.SecuPaymentSecupay.model.Config', {
 
    /**
     * Extends the standard ExtJS 4
     * @string
     */
    extend : 'Ext.data.Model',
 
    /**
     * The fields used for this model
     * @array
     */
    fields : [
        { name : 'apikey', type : 'string' },
        { name : 'secupay_url', type : 'string' },
    ],
  
    /**
     * Configure the data communication
     * @object
     */
    proxy : {
        type : 'ajax',
 
        api:{
            read:   '{url action=getSecuPaymentSecupayConfig}'
        },
 
        reader : {
            type : 'json',
            root : 'data'
        }
    }
});