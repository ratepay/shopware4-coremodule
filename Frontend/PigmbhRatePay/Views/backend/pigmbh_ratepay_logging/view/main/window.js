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
                header: '{s namespace=RatePAY name=date}Datum{/s}',
                dataIndex: 'date',
                flex:2
            },

            {
                header: '{s namespace=RatePAY name=version}Version{/s}',
                dataIndex: 'version',
                flex:1
            },

            {
                header: '{s namespace=RatePAY name=operation}Operation{/s}',
                dataIndex: 'operation',
                flex:2
            },

            {
                header: '{s namespace=RatePAY name=suboperation}Suboperation{/s}',
                dataIndex: 'suboperation',
                flex:2
            },

            {
                header: '{s namespace=RatePAY name=transactionid}Transaction-ID{/s}',
                dataIndex: 'transactionId',
                flex:2
            },

            {
                header: '{s namespace=RatePAY name=firstname}FirstName{/s}',
                dataIndex: 'firstname',
                flex:1
            },

            {
                header: '{s namespace=RatePAY name=lastname}LastName{/s}',
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
                title:'{s namespace=RatePAY name=request}Request{/s}',
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
                title:'{s namespace=RatePAY name=response}Response{/s}',
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