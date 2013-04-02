////{block name="backend/order/view/detail/window" append}
//{namespace name=backend/order/view/main}
Ext.define('Shopware.apps.Order.view.detail.RatePayDetailOrder', {

    override: 'Shopware.apps.Order.view.detail.Window',

    //Überschreiben der standard funktion welche das Tab Panel erzeugt.
    createTabPanel: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);

  
        if(me.isOrderValid()){
// TODO: Remove tabs, die nicht mehr zugänglich sein sollen!
//         
//        Ext.Array.each(tabPanel.items.items, function(item) {
//            console.log(item.id);
//        });
            tabPanel.add(Ext.create('Shopware.apps.Order.view.detail.RatePayTab', {
                title: 'RatePAY-Test',
                historyStore: me.historyStore,
                record: me.record,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore:  me.paymentStatusStore
            }));
        }

        return tabPanel;
    },
    isOrderValid: function(){
        // Check if order is RatePAY order!
        return true;
    }
});
//{/block}
