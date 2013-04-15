//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepayhistory"}
Ext.define('Shopware.apps.Order.view.detail.ratepayhistory', {

    extend:'Ext.grid.Panel',
    autoScroll:true,
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
            header: 'Datum',
            dataIndex: 'date',
            flex:1
        },

        {
            header: 'Event',
            dataIndex: 'event',
            flex:2
        },

        {
            header: 'Name',
            dataIndex: 'articlename',
            flex:2
        },

        {
            header: 'Nummer',
            dataIndex: 'articlenumber',
            flex:1
        },

        {
            header: 'Anzahl',
            dataIndex: 'quantity',
            flex:1
        }
        ];
    }
});
//{/block}