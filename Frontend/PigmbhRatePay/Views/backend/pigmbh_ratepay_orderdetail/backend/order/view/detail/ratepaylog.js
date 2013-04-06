//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepaylog"}
Ext.define('Shopware.apps.Order.view.detail.ratepaylog', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.grid.Panel',
    autoScroll:true,

    initComponent: function() {
        var me = this;
        var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
        var id = me.record.get('id');
        me.store = logstore.load({
            params:{
                'orderId': id
            }
        });

        me.columns =  {
            items: me.getColumns(),
            defaults: {
                flex: 1
            }
        };

        me.dockedItems = me.getToolbar();
        me.callParent(arguments);

    },

    getColumns:function(){
        return [
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
        },
        {
            header: 'Request',
            xtype:'actioncolumn',
            flex:1,
            items: [{
                iconCls:'sprite-documents-stack',
                handler: function(grid, rowIndex, colIndex) {
                    var rec = grid.getStore().getAt(rowIndex);
                    Ext.create('Ext.window.Window', {
                        title:'Request-XML',
                        height: 350,
                        width: 450,
                        layout: 'fit',
                        items: {
                            xtype: 'textareafield',
                            readOnly : true,
                            grow: false,
                            value: rec.get('request')
                        }
                    }).show();
                }
            }]
        },
        {
            header: 'Response',
            xtype:'actioncolumn',
            flex:1,
            items: [{
                iconCls:'sprite-documents-stack',
                handler: function(grid, rowIndex, colIndex) {
                    var rec = grid.getStore().getAt(rowIndex);
                    Ext.create('Ext.window.Window', {
                        title:'Response-XML',
                        height: 350,
                        width: 450,
                        layout: 'fit',
                        items: {
                            xtype: 'textareafield',
                            readOnly : true,
                            grow: false,
                            value: rec.get('response')
                        }
                    }).show();
                }
            }]
        }
        ];
    },

    getToolbar:function(){
        var me = this;
        var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
        var id = me.record.get('id');

        return [{
            xtype: 'pagingtoolbar',
            store: logstore.load({
                params:{
                    'orderId': id
                }
            }),
            dock: 'bottom',
            displayInfo: true
        }];
    }

});
//{/block}