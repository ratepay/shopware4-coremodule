//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/RatepayLog"}
Ext.define('Shopware.apps.Order.view.detail.RatePayLog', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',
    autoScroll:true,
    initComponent: function() {
        var me = this;

        me.items = [
        me.logGrid()
        ];
        me.callParent(arguments);
    },

    logGrid: function(){
        var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
        var id = this.record.get('id');
        return Ext.create('Ext.grid.Panel',{
            store: logstore.load({
                params:{
                    'orderId': id
                }
            }),
            height: '100%',
            sortableColumns : false,
            columns:[
            {
                header: 'Datum',
                dataIndex: 'date',
                width:125
            },

            {
                header: 'Version',
                dataIndex: 'version',
                width:50
            },

            {
                header: 'Operation',
                dataIndex: 'operation',
                width:125
            },

            {
                header: 'Suboperation',
                dataIndex: 'suboperation',
                width:125
            },

            {
                header: 'Transaction-ID',
                dataIndex: 'transactionId',
                width:150
            },

            {
                header: 'FirstName',
                dataIndex: 'firstname',
                width:100
            },

            {
                header: 'LastName',
                dataIndex: 'lastname',
                width:100
            },
            {
                header: 'Request',
                xtype:'actioncolumn',
                width:50,
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
                width:60,
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
                                grow: false,
                                value: rec.get('response')
                            }
                        }).show();
                    }
                }]
            },
            {
                width:100
            }
            ]
        });
    }

});
//{/block}