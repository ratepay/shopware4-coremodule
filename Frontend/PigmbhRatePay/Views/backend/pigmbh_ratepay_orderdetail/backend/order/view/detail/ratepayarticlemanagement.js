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
            items:[
            Ext.create('Shopware.apps.Order.view.detail.ratepaydelivery',{
                record: me.record
            })
            ]
        },
        {
            title: 'Retoure',
            items:[
            Ext.create('Shopware.apps.Order.view.detail.ratepayretoure')
            ]
        }
        ];
        this.callParent(arguments);
    }
});
//{/block}