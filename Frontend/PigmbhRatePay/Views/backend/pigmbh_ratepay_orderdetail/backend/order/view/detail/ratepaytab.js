//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/RatepayTab"}
Ext.define('Shopware.apps.Order.view.detail.RatepayTab', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',
    autoScroll:true,
    initComponent: function() {
        this.items = [
            // TODO: Panels, desin etc hinzufügen!
        ];
        this.callParent(arguments);
    }
});
//{/block}