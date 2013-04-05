////{block name="backend/order/view/detail/window" append}
//{namespace name=backend/order/view/main}
Ext.define('Shopware.apps.Order.view.detail.ratepaydetailorder', {

    override: 'Shopware.apps.Order.view.detail.Window',

    //Überschreiben der standard funktion welche das Tab Panel erzeugt.
    createTabPanel: function () {
        var me = this;
        var tabPanel = me.callParent(arguments);

        if(me.isRatePAYOrder()){
            tabPanel = me.createRatePAYTabPanel();
        }
        return tabPanel;
    },
    isRatePAYOrder: function(){
        var me = this;
        var paymentName = '';
        for(i=0;i< me.paymentsStore.data.items.length;i++){
            if(me.paymentsStore.data.items[i].data.id == this.record.get('paymentId')){
                paymentName = me.paymentsStore.data.items[i].data.name;
            }
        }

        if(paymentName.search(/^pigmbhratepay(invoice|rate|debit)$/) != -1){
            return true;
        }else{
            return false;
        }
    },

    /**
     * Creates the tab panel for the detail page.
     * @return Ext.tab.Panel
     */
    createRatePAYTabPanel: function() {
        var me = this;

        return Ext.create('Ext.tab.Panel', {
            name: 'main-tab',
            items: [
            Ext.create('Shopware.apps.Order.view.detail.Overview', {
                title: me.snippets.overview,
                record: me.record,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore:  me.paymentStatusStore
            }), Ext.create('Shopware.apps.Order.view.detail.Detail',{
                title: me.snippets.details,
                record: me.record,
                paymentsStore: me.paymentsStore,
                shopsStore: me.shopsStore,
                countriesStore: me.countriesStore
            }), Ext.create('Shopware.apps.Order.view.detail.Communication',{
                title: me.snippets.communication,
                record: me.record
            }), Ext.create('Shopware.apps.Order.view.detail.Document',{
                record: me.record,
                documentTypesStore: me.documentTypesStore
            }), Ext.create('Shopware.apps.Order.view.detail.OrderHistory', {
                title: me.snippets.history,
                historyStore: me.historyStore,
                record: me.record,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore:  me.paymentStatusStore
            }), Ext.create('Shopware.apps.Order.view.detail.ratepayarticlemanagement', {
                title: 'Artikelverwaltung',
                record: me.record,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore:  me.paymentStatusStore
            }), Ext.create('Shopware.apps.Order.view.detail.ratepaylog', {
                title: 'RatePAY Log',
                record: me.record
            }),Ext.create('Shopware.apps.Order.view.detail.ratepayhistory', {
                title: 'RatePAY History',
                historyStore: me.historyStore,
                record: me.record,
                orderStatusStore: me.orderStatusStore,
                paymentStatusStore:  me.paymentStatusStore
            })
            ]
        });
    }

});
//{/block}
