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

Ext.define('Shopware.apps.SecuPaymentSecupay.controller.List', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',
 
    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;
 
        me.control({
            'payment_secupay-list-list':{
                openInvoiceCapture: me.onOpenInvoiceCapture,
                updateStatus: me.onUpdateStatus
            }
        });
        me.getStore('Config').load();
        
    },
 
    /**
     * @param view
     * @param rowIndex
     * @return void
     */
    onOpenInvoiceCapture:function (view, rowIndex) {
        var me = this;
        
        var record = me.subApplication.getStore('List').getAt(rowIndex);
        
        var apikey = me.getStore('Config').first().get("apikey");
        var secupay_url = me.getStore('Config').first().get("secupay_url");

        me.iframeWindow = me.getView('iframe.Window');
        var win = me.iframeWindow.create();
        win.SetUrl('<iframe src="'+secupay_url+record.get("Hash")+'/capture/'+apikey+'/">');
        win.show();
        
    },
    
    /**
     * @param view
     * @param rowIndex
     * @return void
     */
    onUpdateStatus:function (record) {
        var me = this,
            hash = '',
            message = '';
        
        if (!(record instanceof Ext.data.Model)) {
            return;
        }        
        //hash = record.get('Hash');
        
        Ext.Ajax.request ({
            url: '{url controller="SecuPaymentSecupay" action="updateTransStatus"}',
            params: {
                    id: record.get('id')
            },            
            success: function (res) {
                if (Ext.JSON.decode(res.responseText).success == false) {
                    message = ''+Ext.JSON.decode(res.responseText).error;
                    Shopware.Notification.createGrowlMessage('Fehler', message);
                } else {
                    me.getStore('List').load();
                }
            } ,
            failure: function (res) {
                message = ''+ res.status + ' - ' +res.statusText;
                Shopware.Notification.createGrowlMessage('Fehler', message);                
            }            
            
        })
        
        
    }    
    
});