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

Ext.define('Shopware.apps.SecuPaymentSecupay.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: 'secupay Transaktionsliste',
    alias: 'widget.payment_secupay-main-window',
    border: false,
    autoShow: true,
    height: 650,
    width: 925,
    maximizable:true,
    minimizable:true,

    layout: {
       type: 'hbox',
       align: 'stretch'
    },
 
    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = [{
            xtype: 'payment_secupay-list-list',
            listStore: me.listStore,
            flex: 1
        }];
 
        me.callParent(arguments);
    }
});
