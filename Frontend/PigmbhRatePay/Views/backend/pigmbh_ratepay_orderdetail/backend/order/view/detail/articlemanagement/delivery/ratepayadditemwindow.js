//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/articlemanagement/ratepayadditemwindow"}
Ext.define('Shopware.apps.Order.view.detail.ratepayadditemwindow', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.window.Window',
    autoScroll:true,
    layout:'fit',
    width:600,
    height:350,
    title: 'Artikel hinzufügen',
    initComponent: function() {
        var me = this;
        me.articleStore = Ext.create('Ext.data.Store', {
            fields: [ 'articleID','articleordernumber', 'name', 'quantity', 'price', 'taxRate' ]
        });
        me.items = [
        me.getEmptyGrid(),
        ]
        me.callParent(arguments);
    },

    getEmptyGrid:function(){
        var me = this;
        var id = me.record.get('id');
        return Ext.create('Ext.grid.Panel', {
            plugins: Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1
            }),
            id: 'gridNewItems',
            columns: me.getColumns(),
            autoScroll: true,
            store: me.articleStore,
            height: 300,
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'top',
                items: [
                Ext.create('Shopware.ArticleSearch',{
                    id:'searchField',
                    multiSelect: false,
                    returnValue: 'name',
                    hiddenReturnValue: 'number',
                    listeners: {
                        scope: me,
                        valueselect: me.onAddArticleToGrid
                    }
                })
                ]
            }],
            buttons:[
            {
                text: 'Auswahl zur Bestellung hinzufügen',
                handler: function(){
                    var store = Ext.getCmp('gridNewItems').getStore();
                    var articleNumber = new Array();
                    var insertedIds = new Array();
                    var message;
                    for(i=0;i < store.data.items.length;i++){
                        insertedIds.push(me.savePosition(store.data.items[i].data));
                        articleNumber.push(store.data.items[i].data.articleordernumber);
                    }
                    if(me.parent.initPositions(articleNumber)){
                       if(me.parent.paymentChange(id,'change-order', insertedIds)){
                            message = 'Artikel wurden erfolgreich zur Bestellung hinzugefügt.';
                        }else{
                            for(i=0;i < insertedIds.length;i++){
                                me.parent.deletePosition(insertedIds[i]);
                            }
                            message = 'Artikel konnten nicht korrekt an RatePAY übermittelt werden.';
                        }
                    }else{
                        message = 'Artikel konnten nicht der Bestellung hinzugefügt werden.';
                    }
                    Ext.Msg.alert('Artikel hinzufügen', message);
                    me.parent.reloadGrid();
                    me.close();
                }
            },{
                text:'Cancel',
                handler: function(){
                    me.close();
                }
            }
            ]
        });
    },

    getColumns: function() {
        var me = this;

        return [{
            dataIndex: 'articleordernumber',
            header: 'articleNumber',
            flex: 1
        },{
            dataIndex: 'name',
            header: 'name',
            flex: 2
        },{
            header: 'quantity',
            dataIndex: 'quantity',
            editor:{
                xtype: 'numberfield',
                hideTrigger : false,
                allowBlank: false,
                allowDecimals : false,
                minValue: 1,
                value:1
            },
            flex: 2
        },{
            dataIndex: 'price',
            header: 'price',
            flex: 2,
            editor:{
                xtype: 'numberfield',
                allowBlank: false,
                allowDecimals : true,
                minValue: 0.01
            }
        }, {
            xtype: 'actioncolumn',
            header: 'actions',
            width: 60,
            items: [{
                iconCls: 'sprite-minus-circle',
                action: 'delete-article',
                scope: me,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record){
                    var store = grid.getStore();
                    store.remove(record);
                }
            }, {
                iconCls:'sprite-inbox',
                action:'openArticle',
                scope: me,
                /**
                         * Add button handler to fire the openCustomer event which is handled
                         * in the list controller.
                         */
                handler:function (grid, rowIndex, colIndex, item) {
                    var store = grid.getStore(),
                    record = store.getAt(rowIndex);
                    Shopware.app.Application.addSubApplication({
                        name: 'Shopware.apps.Article',
                        action: 'detail',
                        params: {
                            articleId: record.get('id')
                        }
                    });
                }
            }
            ]
        }];
    },

    onAddArticleToGrid: function(field, returnVal, hiddenVal, record) {
        var me = this, store = me.articleStore;
        var item = me.getItem(record.get('id'));
        store.add(item);
        Ext.getCmp('searchField').searchField.setValue();
    },

    savePosition: function(item){
        var me = this;
        var id = me.record.get('id');
        var insertID;
        Ext.Ajax.request({
            url: '{url controller=Order action=savePosition}',
            method:'POST',
            async:false,
            params: {
                orderId:id,
                articleId:item.articleID,
                articleName:item.name,
                articleNumber:item.articleordernumber,
                price:item.price,
                quantity:item.quantity,
                taxRate: item.tax_rate
            },
            success: function(response){
                var response = Ext.JSON.decode(response.responseText);
                insertID = response.data.id;
            }
        });
        return insertID;
    },

    getItem: function(id){
        var model;
        Ext.Ajax.request({
            url: '{url controller=PigmbhRatepayOrderDetail action=getArticle}',
            method:'POST',
            async:false,
            params: {
                id:id
            },
            success: function(response){
                var response = Ext.JSON.decode(response.responseText);
                model = Ext.create('Shopware.apps.Order.model.ratepaypositions', {
                    articleID : id,
                    articleordernumber : response.data.mainDetail.number,
                    name: response.data.name,
                    quantity : 1,
                    price : response.data.mainPrices[0].price,
                    tax_rate : response.data.tax.tax
                });
            }
        });
        return model;
    }



});
//{/block}