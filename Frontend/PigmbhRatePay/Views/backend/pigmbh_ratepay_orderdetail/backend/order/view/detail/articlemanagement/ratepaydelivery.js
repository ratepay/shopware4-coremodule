//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/articlemanagement/ratepaydelivery"}
Ext.define('Shopware.apps.Order.view.detail.ratepaydelivery', {

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
        var id = this.record.get('id');

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
            header: 'Anz.',
            dataIndex: 'quantityDeliver',
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
            text: 'Auswahl versenden',
            handler: function(){
                me.toolbarDeliver();
            }
        },
        {
            iconCls:'sprite-minus-circle-frame',
            text: 'Auswahl stornieren',
            handler: function(){
                me.toolbarCancel();
            }
        }
        ];
    },

    toolbarDeliver:function(){
        var me = this;
        var items = new Array();
        var id = me.record.get('id');
        var error = false;
        for(i=0;i< me.store.data.items.length;i++){
            var row = me.store.data.items[i].data;
            var item = new Object();
            if(row.quantityDeliver >(row.quantity - row.delivered)){
                error = true;
            }
            item['id'] = row.articleID;
            item['articlenumber'] = row.articleordernumber;
            item['name'] =row.name;
            item['price'] =row.price;
            item['taxRate'] =row.tax_rate;
            item['quantity'] = row.quantityDeliver;
            item['delivered'] = row.delivered;
            item['returned'] = row.returned;
            item['cancelled'] = row.cancelled;
            item['deliveredItems'] = row.quantityDeliver;
            items.push(item);
        }

        if(error == true){
            Ext.Msg.alert('Delivery fail', 'Anz. must be smaller than quantity!');
            return false;
        }else{
            Ext.Ajax.request({
                url: '{url controller=PigmbhRatepayOrderDetail action=deliverItems}',
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

    },
    toolbarCancel:function(){
        var me = this;
        var items = new Array();
        var id = me.record.get('id');
        var error = false;
        for(i=0;i< me.store.data.items.length;i++){
            var row = me.store.data.items[i].data;
            var item = new Object();
            if(row.quantityDeliver > (row.quantity - row.cancelled)){
                error = true;
            }
            item['id'] = row.articleID;
            item['articlenumber'] = row.articleordernumber;
            item['name'] =row.name;
            item['price'] =row.price;
            item['taxRate'] =row.tax_rate;
            item['quantity'] = row.quantity - row.quantityDeliver;
            item['delivered'] = row.delivered;
            item['returned'] = row.returned;
            item['cancelled'] = row.cancelled;
            item['cancelledItems'] = row.quantityDeliver;
            items.push(item);
        }

        if(error == true){
            Ext.Msg.alert('Cancellation fail', 'Anz. must be smaller than quantity!');
            return false;
        }else{
            Ext.Ajax.request({
                url: '{url controller=PigmbhRatepayOrderDetail action=cancelItems}',
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