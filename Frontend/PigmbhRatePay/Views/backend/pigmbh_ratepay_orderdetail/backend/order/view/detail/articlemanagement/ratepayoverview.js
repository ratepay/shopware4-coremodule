//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/articlemanagement/ratepayoverview"}
Ext.define('Shopware.apps.Order.view.detail.ratepayoverview', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.grid.Panel',
    autoScroll:true,
    initComponent: function() {
        var me = this;
        me.columns =  {
            items: me.getColumns(),
            defaults: {
                flex: 1
            }
        }
        me.dockedItems = [{
            xtype: 'toolbar',
            dock: 'top',
            items: me.getToolbar()
        }];

        me.callParent(arguments);
    },

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        return [
        {
            header: 'Anz.'
        },
        {
            header: 'ArticleName'
        },
        {
            header: 'ArticleNummer'
        },
        {
            header: 'Bestellt'
        },
        {
            header: 'Versand'
        },
        {
            header: 'Storniert'
        },
        {
            header: 'Retourniert'
        },
        ];
    },

    getToolbar:function(){
        return [
        {
            iconCls:'sprite-plus-circle-frame',
            text: 'Item hinzufügen'
        },
        {
            iconCls:'sprite-minus-circle-frame',
            text: 'Item entfernen'
        },
        {
            iconCls:'sprite-plus-circle-frame',
            text: 'Gutschein hinzufügen'
        },
        {
            iconCls:'sprite-minus-circle-frame',
            text: 'Gutschein entfernen'
        }
        ];
    }

});
//{/block}