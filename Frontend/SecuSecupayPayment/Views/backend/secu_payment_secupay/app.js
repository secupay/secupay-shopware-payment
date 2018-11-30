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

Ext.define('Shopware.apps.SecuPaymentSecupay', {
    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',
 
    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name:'Shopware.apps.SecuPaymentSecupay',
 
    bulkLoad: true,
    //loadPath: '{url action=load}',
    loadPath:'{url controller="SecuPaymentSecupay" action="load"}',
 
    /**
     * Required controllers for sub-application
     * @array
     */
    controllers: ['Main', 'List'],
 
    /**
     * Requires models for sub-application
     * @array
     */
    models: ['Main', 'Config'],
 
    /**
     * Required views for this sub-application
     * @array
     */
    views: [ 'main.Window', 'list.List', 'iframe.Window' ],
 
    /**
     * Required stores for sub-application
     * @array
     */
    stores: [ 'List', 'Config'],
 
 
    /**
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');
 
        return mainController.mainWindow;
    }
});
 