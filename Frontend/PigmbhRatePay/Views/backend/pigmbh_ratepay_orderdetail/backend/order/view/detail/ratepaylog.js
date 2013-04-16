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

        me.callParent(arguments);

    },

    getColumns:function(){
        return [
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
        },
        {
            header: '{s namespace=RatePAY name=request}Request{/s}',
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
            header: '{s namespace=RatePAY name=response}Response{/s}',
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
    }
});
//{/block}