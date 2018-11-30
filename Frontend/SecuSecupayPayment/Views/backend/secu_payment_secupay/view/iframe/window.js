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

Ext.define('Shopware.apps.SecuPaymentSecupay.view.iframe.Window', {
    extend: 'Enlight.app.Window',
    title: 'secupay iFrame',
    alias: 'widget.payment_secupay-iframe-window',
    border: false,
    autoShow: false,
    layout: 'border',
    height: 650,
    iframe: null, 
    width: 650,
 
    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    
    initComponent: function() {
        var me = this;
        
         me.iframe = Ext.create('Ext.container.Container', {
 
           title: 'iFrame',
 
           html: ''
 
        });

        me.items = [
            me.iframe
        ];
        
        me.callParent(arguments);        

    }, 
    
    SetUrl: function(url) {
        var me = this;

         me.iframe = Ext.create('Ext.container.Container', {
 
           title: 'iFrame',
 
           html: url
 
        });

        me.add(me.iframe);

    }
    
});