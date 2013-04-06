/**
 * window
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.PigmbhRatepayLogging.view.main.Window', {
    extend: 'Enlight.app.Window',
    title: 'RatePAY Logging',
    alias: 'widget.pigmbh_ratepay_logging-main-window',
    border: false,
    autoShow: true,
    resizable: false,
    layout: {
        type:'vbox'
    },
    height: 520,
    width: 800,

    initComponent: function() {
        var me = this;
        me.store = me.listStore;
        me.items = [
        me.createOverviewGrid(me),
        me.createDetailGrid(me)
        ];
        me.callParent(arguments);
    },
    createOverviewGrid: function(me){
        return Ext.create('Ext.grid.Panel', {
            store: me.store,
            forceFit:false,
            border: false,
            height: 266,
            width:'100%',
            columns: [
            {
                header: 'Datum',
                dataIndex: 'date',
                flex:2
            },

            {
                header: 'Version',
                dataIndex: 'version',
                flex:1
            },

            {
                header: 'Operation',
                dataIndex: 'operation',
                flex:2
            },

            {
                header: 'Suboperation',
                dataIndex: 'suboperation',
                flex:2
            },

            {
                header: 'Transaction-ID',
                dataIndex: 'transactionId',
                flex:2
            },

            {
                header: 'FirstName',
                dataIndex: 'firstname',
                flex:1
            },

            {
                header: 'LastName',
                dataIndex: 'lastname',
                flex:1
            }
            ],
            dockedItems: [{
                xtype: 'pagingtoolbar',
                store: me.store,
                dock: 'bottom',
                displayInfo: true
            }],
            listeners: {
                itemclick: {
                    fn: function(self, store_record, html_element, node_index, event) {
                        Ext.ComponentManager.get('requestPanel').setValue(store_record.data.request);
                        Ext.ComponentManager.get('responsePanel').setValue(store_record.data.response);
                    }
                }
            }
        });
    },
    createDetailGrid: function(me){
        return Ext.create('Ext.panel.Panel', {
            width:'100%',
            height:215,
            border: false,
            layout:{
                type:'hbox',
                align:'strech'
            },
            items: [{
                xtype:'textareafield',
                border: false,
                layout:'fit',
                title:'Request',
                value: 'N/A',
                id:'requestPanel',
                autoScroll:true,
                readOnly : true,
                width:'50%',
                height:'100%'
            },{
                xtype:'textareafield',
                border: false,
                layout:'fit',
                title:'Response',
                value: 'N/A',
                id:'responsePanel',
                autoScroll:true,
                readOnly : true,
                width:'50%',
                height:'100%'
            }
            ]
        });
    }
});