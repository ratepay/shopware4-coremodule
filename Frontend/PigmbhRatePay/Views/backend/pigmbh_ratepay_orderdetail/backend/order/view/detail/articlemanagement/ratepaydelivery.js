//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/articlemanagement/ratepaydelivery"}
Ext.define('Shopware.apps.Order.view.detail.ratepaydelivery', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.grid.Panel',
    autoScroll:true,
    initComponent: function() {
        var me = this;
        var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
        var id = this.record.get('id');

        console.log(positionStore.load({
            params:{
                'orderId': id
            }
        }));


        me.store = positionStore.load({
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
            header: 'ArticleName',
            dataIndex: 'name'
        },
        {
            header: 'ArticleNummer',
            dataIndex: 'articleordernumber'
        },
        {
            header: 'Preis',
            dataIndex: 'price'
        },
        {
            header: 'Bestellt',
            dataIndex: 'quantity'
        },
        {
            header: 'Versand',
            dataIndex: 'delivered'
        },
        {
            header: 'Storniert',
            dataIndex: 'cancelled'
        },
        {
            header: 'Retourniert',
            dataIndex: 'returned'
        },
        ];
    },

    getToolbar:function(){
        return [
        {
            iconCls:'sprite-inbox--plus',
            text: 'Item hinzufügen'
        },
        {
            iconCls:'sprite-inbox--plus',
            text: 'Gutschein hinzufügen'
        },
        {
            iconCls:'sprite-truck',
            text: 'Auswahl versenden'
        },
        {
            iconCls:'sprite-minus-circle-frame',
            text: 'Auswahl stornieren'
        }
        ];
    }

});
//{/block}