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

Ext.define('Shopware.apps.SecuPaymentSecupay.model.Main', {
 
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
        { name : 'id',      type : 'int' },
        { name : 'PaymentMethod', type : 'string' },
        { name : 'Hash', type : 'string' },
        { name : 'Transaction_id', type : 'int' },
        { name : 'Amount', type : 'float' },
        { 
          name : 'AmountCurrency',
          type : 'float',
          convert: function(value, record) {
              return Ext.util.Format.currency(record.get('Amount'));
          }
        },
        { name : 'Ordernr', type : 'int' },
        { name : 'Message', type : 'string' },
        { name : 'Status_id', type : 'int' },
        { name : 'Date', type: 'date', dateFormat: 'Y-m-d H:i:s'  },
        { name : 'Payment_status', type: 'string'  },
        { name : 'Shop', type : 'string' }        
        
    ],
  
    /**
     * Configure the data communication
     * @object
     */
    proxy : {
        type : 'ajax',
 
        api:{
            read:   '{url controller="SecuPaymentSecupay" action="getSecuPaymentSecupayList"}',
            update: '{url controller="SecuPaymentSecupay" action="updateTransStatus"}'
        },
 
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'totalCount'
        }
    }
});