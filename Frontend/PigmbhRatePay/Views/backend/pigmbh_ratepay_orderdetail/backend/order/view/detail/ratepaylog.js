//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/RatepayLog"}
Ext.define('Shopware.apps.Order.view.detail.RatePayLog', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend:'Ext.form.Panel',
    autoScroll:true,
    initComponent: function() {
        var me = this;

        me.items = [
        me.logGrid()
        ];
        me.callParent(arguments);
    },

    logGrid: function(){
        var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
        var id = this.record.get('id');
        return Ext.create('Ext.grid.Panel',{
            store: logstore.load({
                params:{
                    'orderId': id
                }
            }),
            height: '100%',
            sortableColumns : false,
            columns:[
            {
                header: 'Datum',
                dataIndex: 'date'
            },

            {
                header: 'Version',
                dataIndex: 'version'
            },

            {
                header: 'Operation',
                dataIndex: 'operation'
            },

            {
                header: 'Suboperation',
                dataIndex: 'suboperation'
            },

            {
                header: 'Transaction-ID',
                dataIndex: 'transactionId'
            },

            {
                header: 'FirstName',
                dataIndex: 'firstname'
            },

            {
                header: 'LastName',
                dataIndex: 'lastname'
            },

            {
                header: 'Request',
                dataIndex: 'request'
            },

            {
                header: 'Response',
                dataIndex: 'response'
            },
            ]
        });
    }

});
//{/block}