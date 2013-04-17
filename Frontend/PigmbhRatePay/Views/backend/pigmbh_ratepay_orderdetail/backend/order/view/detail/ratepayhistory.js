//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepayhistory"}
Ext.define('Shopware.apps.Order.view.detail.ratepayhistory', {

    extend:'Ext.grid.Panel',
    autoScroll:true,
    listeners: {
        activate: function(tab){
            var me = this;
            var historystore = Ext.create('Shopware.apps.Order.store.ratepayhistory');
            var id = me.record.get('id');
            var store = historystore.load({
                params:{
                'orderId': id
            }
            });
            me.reconfigure(store);
        }
    },

    initComponent: function() {
        var me = this;
        var historystore = Ext.create('Shopware.apps.Order.store.ratepayhistory');
        var id = me.record.get('id');
        me.store = historystore.load({
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
            flex:1
        },

        {
            header: '{s namespace=RatePAY name=event}Event{/s}',
            dataIndex: 'event',
            flex:2
        },

        {
            header: '{s namespace=RatePAY name=name}Name{/s}',
            dataIndex: 'articlename',
            flex:2
        },

        {
            header: '{s namespace=RatePAY name=number}Nummer{/s}',
            dataIndex: 'articlenumber',
            flex:1
        },

        {
            header: '{s namespace=RatePAY name=quantity}Anzahl{/s}',
            dataIndex: 'quantity',
            flex:1
        }
        ];
    }
});
//{/block}