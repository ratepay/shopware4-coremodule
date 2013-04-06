//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepayarticlemanagement"}
Ext.define('Shopware.apps.Order.view.detail.ratepayarticlemanagement', {

    /**
     * Define that the additional information is an Ext.tab.Panel extension
     * @string
     */
    extend:'Ext.tab.Panel',

    autoScroll:true,
    layout:'fit',
    initComponent: function() {
        var me = this;
        me.items =  [
        {
            title: 'Versand/Stornierung',
            layout:'fit',
            items:[
            Ext.create('Shopware.apps.Order.view.detail.ratepaydelivery',{
                id:'deliver',
                record: me.record
            })
            ],
            listeners: {
                activate: function(tab){
                    var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
                    var deliveryGrid = Ext.ComponentManager.get('deliver');
                    var store = positionStore.load({
                        params:{
                            'orderId': me.record.get('id')
                        }
                    });
                    deliveryGrid.reconfigure(store);
                }
            }
        },
        {
            title: 'Retoure',
            layout:'fit',
            items:[
            Ext.create('Shopware.apps.Order.view.detail.ratepayretoure',{
                id:'return',
                record: me.record
            })
            ],
            listeners: {
                activate: function(tab){
                    var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
                    var deliveryGrid = Ext.ComponentManager.get('return');
                    var store = positionStore.load({
                        params:{
                            'orderId': me.record.get('id')
                        }
                    });
                    deliveryGrid.reconfigure(store);
                }
            }
        }
        ];
        this.callParent(arguments);
    }
});
//{/block}