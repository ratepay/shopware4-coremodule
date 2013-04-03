//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepayarticlemanagement"}
Ext.define('Shopware.apps.Order.view.detail.ratepayarticlemanagement', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',
    autoScroll:true,
    initComponent: function() {
        var me = this;
        this.items = [
        me.createMainPanel()
        ];
        this.callParent(arguments);
    },

    createMainPanel: function(){
        return Ext.create('Ext.tab.Panel', {
            layout:'fit',
            items: [
            {
                title: 'Artikel&uuml;bersicht'
            },
            {
                title: 'Versand/Stornierung'
            },
            {
                title: 'Retoure'
            }
            ]
        });
    }

});
//{/block}