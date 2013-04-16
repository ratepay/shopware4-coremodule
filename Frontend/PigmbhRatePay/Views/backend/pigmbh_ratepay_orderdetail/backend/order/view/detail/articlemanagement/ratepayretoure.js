//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/articlemanagement/ratepayretoure"}
Ext.define('Shopware.apps.Order.view.detail.ratepayretoure', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.grid.Panel',
    autoScroll:true,
    layout:'fit',
    plugins: Ext.create('Ext.grid.plugin.CellEditing', {
        clicksToEdit: 1
    }),
    initComponent: function() {
        var me = this;
        var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
        var id = me.record.get('id');

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
            header: 'Anz.',
            dataIndex: 'quantityReturn',
            editor: {
                xtype: 'numberfield',
                hideTrigger : false,
                allowBlank: false,
                allowDecimals : false,
                minValue: 0
            }
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
        var me = this;
        var id = me.record.get('id');
        return [
        {
            text: 'Anzahl auf 0 setzen',
            handler: function(){
                var id = me.record.get('id');
                var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
                me.store = positionStore.load({
                    params:{
                        'orderId': id,
                        'setToZero':true
                    }
                });

                me.reconfigure(me.store);
            }
        },
        {
            iconCls:'sprite-minus-circle-frame',
            text: 'Auswahl retournieren',
            handler: function(){
                me.toolbarReturn();
            }
        }
        ];
    },
    toolbarReturn:function(){
        var me = this;
        var items = new Array();
        var id = me.record.get('id');
        var error = false;
        for(i=0;i< me.store.data.items.length;i++){
            var row = me.store.data.items[i].data;
            var item = new Object();
            if(row.quantityReturn > (row.quantity - row.returned)){
                error = true;
            }
            item['id'] = row.articleID;
            item['articlenumber'] = row.articleordernumber;
            item['name'] =row.name;
            item['price'] =row.price;
            item['taxRate'] =row.tax_rate;
            item['quantity'] = row.quantity - row.quantityReturn;
            item['delivered'] = row.delivered;
            item['returned'] = row.returned;
            item['cancelled'] = row.cancelled;
            item['returnedItems'] = row.quantityReturn;
            items.push(item);
        }

        if(error == true){
            Ext.Msg.alert('Return fail', 'Anz. must be smaller than quantity!');
            return false;
        }else{
            Ext.Ajax.request({
                url: '{url controller=PigmbhRatepayOrderDetail action=returnItems}',
                method:'POST',
                async:false,
                params: {
                    orderId:id,
                    items:Ext.encode(items)
                },
                success: function(){
                    var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
                    me.store = positionStore.load({
                        params:{
                            'orderId': id
                        }
                    });

                    me.reconfigure(me.store);
                }
            });
        }
    }

});
//{/block}