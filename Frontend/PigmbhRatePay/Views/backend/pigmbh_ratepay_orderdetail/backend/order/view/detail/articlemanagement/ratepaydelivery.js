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
        var id = this.record.get('id');
        return [
        {
            iconCls:'sprite-inbox--plus',
            text: 'Artikel hinzufügen',
            handler: function(){
                Ext.create('Shopware.apps.Order.view.detail.ratepayadditemwindow',{
                    parent: me,
                    record: me.record
                }).show();
            }
        },
        {
            iconCls:'sprite-plus-circle-frame',
            text: 'Gutschein hinzufügen',
            handler: function(){
                Ext.create('Ext.window.Window', {
                    title: 'Gutschein hinzufügen',
                    width: 200,
                    height: 100,
                    id:'creditWindow',
                    resizable: false,
                    layout:'fit',
                    items:[
                    {
                        xtype: 'numberfield',
                        id:'creditAmount',
                        allowBlank: false,
                        allowDecimals : true,
                        minValue: 0.01,
                        value:1.00
                    }
                    ],
                    buttons: [{
                        text:'Ok',
                        handler: function(){
                            var randomnumber=Math.floor(Math.random()* 10001);
                            var creditname = 'Credit' + id + '-' + randomnumber;
                            Ext.Ajax.request({
                                url: '{url controller=Order action=savePosition}',
                                method:'POST',
                                async:false,
                                params: {
                                    orderId:id,
                                    articleId:0,
                                    articleName:creditname,
                                    articleNumber:creditname,
                                    id:0,
                                    inStock:0,
                                    mode:0,
                                    price: Ext.getCmp('creditAmount').getValue() * -1,
                                    quantity:1,
                                    statusDescription:"",
                                    statusId:0,
                                    taxDescription:"",
                                    taxId:1,
                                    taxRate:0,
                                    total:0
                                },
                                success: function(response){
                                    var response = Ext.JSON.decode(response.responseText);
                                    var articleNumber = new Array();
                                    var insertedIds = new Array();
                                    var message;
                                    articleNumber.push(response.data.articleNumber);
                                    insertedIds.push(response.data.id);
                                    if(me.initPositions(articleNumber)){
                                        if(me.paymentChange(id,'credit', insertedIds)){
                                            message = 'Gutschein wurde erfolgreich zur Bestellung hinzugefügt.';
                                        }else{
                                            me.deletePosition(insertedIds);
                                            message = 'Gutschein konnte nicht korrekt an RatePAY übermittelt werden.';
                                        }
                                    }else{
                                        message = 'Gutschein konnte nicht der Bestellung hinzugefügt werden.';
                                    }
                                    Ext.getCmp('creditWindow').close();
                                    Ext.Msg.alert('Gutschein hinzufügen', message);
                                    me.reloadGrid();
                                }
                            });
                        }
                    },{
                        text:'Cancel',
                        handler: function(){
                            Ext.getCmp('creditWindow').close();
                        }
                    }]
                }).show();
            }
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
                    me.reloadGrid();
                }
            });
        }

    },
    toolbarCancel: function(){
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
                    me.reloadGrid();
                }
            });
        }
    },

    reloadGrid: function(){
        var me = this;
        var id = me.record.get('id');
        var positionStore = Ext.create('Shopware.apps.Order.store.ratepaypositions');
        me.store = positionStore.load({
            params:{
                'orderId': id
            }
        });

        me.reconfigure(me.store);
    },

    initPositions: function(articleNumber){
        var returnValue = false;
        var me = this;
        var id = me.record.get('id');
        Ext.Ajax.request({
            url: '{url controller=PigmbhRatepayOrderDetail action=initPositions}',
            method:'POST',
            async:false,
            params: {
                orderID:id,
                articleNumber:Ext.JSON.encode(articleNumber)
            },
            success: function(response){
                var response = Ext.JSON.decode(response.responseText);
                returnValue = response.success;
            }
        });
        return returnValue;
    },

    paymentChange: function(id, suboperation, insertedIds){
        var returnValue = false;
        Ext.Ajax.request({
            url: '{url controller=PigmbhRatepayOrderDetail action=add}',
            method:'POST',
            async:false,
            params: {
                orderId:id,
                suboperation: suboperation,
                insertedIds:Ext.JSON.encode(insertedIds)
            },
            success: function(response){
                var response = Ext.JSON.decode(response.responseText);
                returnValue = response.result;
            }
        });
        return returnValue;
    },
    deletePosition: function(id){
        var me = this;
        var orderid = me.record.get('id');
        var result = false;
        Ext.Ajax.request({
            url: '{url controller=Order action=deletePosition targetField=positions}',
            method:'POST',
            async:false,
            params: {
                orderID:orderid,
                id: id,
                valid: true
            },
            success: function(response){
                var response = Ext.JSON.decode(response.responseText);
                result = response.success;
            }
        });
        return result;
    }

});
//{/block}