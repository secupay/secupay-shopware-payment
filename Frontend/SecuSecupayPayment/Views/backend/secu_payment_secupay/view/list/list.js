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

Ext.define('Shopware.apps.SecuPaymentSecupay.view.list.List', {
    extend:'Ext.grid.Panel',
    border: false,
    alias:'widget.payment_secupay-list-list',
    region:'center',
    autoScroll:true,
 
    /**
     * Initialize the Shopware.apps.Customer.view.main.List and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;
 
        me.store = me.listStore;
 
        me.registerEvents();
 
        me.columns = me.getColumns();
        me.pagingbar = me.getPagingBar();
        me.dockedItems = [me.pagingbar ];
 
        me.callParent(arguments);
    },
    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return void
     */
    registerEvents:function () {
        this.addEvents(
                /**
                 *
                 * @event openInvoiceCapture
                 * @param [object] View - Associated Ext.view.Table
                 * @param [integer] rowIndex - Row index
                 * @param [integer] colIndex - Column index
                 * @param [object] item - Associated HTML DOM node
                 */
                'openInvoiceCapture',
                'updateStatus'
        );
 
        return true;
    },
    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;
 
        var columnsData = [
            {
                header:'Datum',
                dataIndex:'Date',
                renderer: me.transDateColumn,
                /*renderer: Ext.util.Format.dateRenderer('d.m.Y'),*/
                flex:2
            },
            {
                header:'Bestellnummer',
                dataIndex:'Ordernr',
                flex:1
            }, 
			{
                header:'Shop',
                dataIndex:'Shop',
                flex:1
            },			
            {
                header:'Transaktion',
                dataIndex:'Hash',
                flex:1
            }, {
                header:'TACode',
                dataIndex:'Transaction_id',
                flex:1
            },
            {
                header:'Zahlart',
                dataIndex:'PaymentMethod',
                flex:1
            },            
            {
                header:'Betrag',
                dataIndex:'AmountCurrency',
                flex:1
            },
            {
                header:'Status',
                dataIndex:'Payment_status',
                flex:1
            },            
            {
                header:'Info',
                dataIndex:'Message',
                flex:3
            },
            {
                header:'Aktionen',
                xtype:'actioncolumn',
                width:130,
                items:me.getActionColumnItems()
            }
        ];
        return columnsData;
    },

 
    /**
     * @return Ext.toolbar.Paging The paging toolbar
     */
    getPagingBar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Paging', {
            store:me.store,
            dock:'bottom',
            displayInfo:true
        });
 
    },
    /**
     * Creates the items of the action column
     *
     * @return [array] action column itesm
     */
    getActionColumnItems: function () {
        var me = this,
            actionColumnData = [];

        actionColumnData.push({
            iconCls:'x-action-col-icon sprite-arrow-circle-135',
            cls:'duplicateColumn',
            tooltip:'Aktualisieren',
            getClass: function(value, metadata, record) {
                if (!record.get("Hash") ) {
                    return 'x-hidden';
                }
            },
            handler:function (grid, rowIndex, colIndex, metaData, event, record) {
                me.fireEvent('updateStatus', record);
            }
 
        });

        actionColumnData.push({
            iconCls:'x-action-col-icon sprite-envelope--arrow',
            cls:'duplicateColumn',
            tooltip:'secupay.Rechnungskauf als fällig markieren',
            getClass: function(value, metadata, record) {
                if (!record.get("PaymentMethod") || record.get("PaymentMethod") != 'invoice' || !record.get('Ordernr') ) {
                    return 'x-hidden';
                }
            },
            handler:function (view, rowIndex, colIndex, item) {
                me.fireEvent('openInvoiceCapture', view, rowIndex, colIndex, item);
            }
 
        }); 
        
        return actionColumnData;
    },
    
    /**
     * Formats the order time column
     * @param value
     */
    transDateColumn:function (value) {
        if ( typeof value === Ext.undefined ) {
            return value;
        }
        return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, 'H:i:s');
    }    
});