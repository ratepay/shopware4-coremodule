/**
 * This program is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, see <http://www.gnu.org/licenses/>.
 */
//{namespace name=backend/order/main}
//{block name="backend/order/view/detail/ratepaylog"}
Ext.define('Shopware.apps.Order.view.detail.ratepaylog', {

    /**
     * Define that the additional information is an Ext.panel.Panel extension
     * @string
     */
    extend: 'Ext.grid.Panel',
    autoScroll: true,
    listeners: {
        activate: function (tab) {
            var me = this;
            var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
            var id = me.record.get('id');
            var store = logstore.load({
                params: {
                    'orderId': id
                }
            });
            me.reconfigure(store);
        }
    },

    initComponent: function () {
        var me = this;
        var logstore = Ext.create('Shopware.apps.Order.store.RatePayLog');
        var id = me.record.get('id');
        me.store = logstore.load({
            params: {
                'orderId': id
            }
        });

        me.columns = {
            items: me.getColumns(),
            defaults: {
                flex: 1
            }
        };

        me.callParent(arguments);

    },

    getColumns: function () {
        return [
            {
                header: '{s namespace=RatePAY name=date}Datum{/s}',
                dataIndex: 'date',
                flex: 2
            },

            {
                header: '{s namespace=RatePAY name=version}Version{/s}',
                dataIndex: 'version',
                flex: 1
            },

            {
                header: '{s namespace=RatePAY name=operation}Operation{/s}',
                dataIndex: 'operation',
                flex: 2
            },

            {
                header: '{s namespace=RatePAY name=suboperation}Suboperation{/s}',
                dataIndex: 'suboperation',
                flex: 2
            },

            {
                header: '{s namespace=RatePAY name=transactionid}Transaction-ID{/s}',
                dataIndex: 'transactionId',
                flex: 2
            },

            {
                header: '{s namespace=RatePAY name=firstname}FirstName{/s}',
                dataIndex: 'firstname',
                flex: 1
            },

            {
                header: '{s namespace=RatePAY name=lastname}LastName{/s}',
                dataIndex: 'lastname',
                flex: 1
            },
            {
                header: '{s namespace=RatePAY name=request}Request{/s}',
                xtype: 'actioncolumn',
                flex: 1,
                items: [
                    {
                        iconCls: 'sprite-documents-stack',
                        handler: function (grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            Ext.create('Ext.window.Window', {
                                title: 'Request-XML',
                                height: 350,
                                width: 450,
                                layout: 'fit',
                                items: {
                                    xtype: 'textareafield',
                                    readOnly: true,
                                    grow: false,
                                    value: rec.get('request')
                                }
                            }).show();
                        }
                    }
                ]
            },
            {
                header: '{s namespace=RatePAY name=response}Response{/s}',
                xtype: 'actioncolumn',
                flex: 1,
                items: [
                    {
                        iconCls: 'sprite-documents-stack',
                        handler: function (grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            Ext.create('Ext.window.Window', {
                                title: 'Response-XML',
                                height: 350,
                                width: 450,
                                layout: 'fit',
                                items: {
                                    xtype: 'textareafield',
                                    readOnly: true,
                                    grow: false,
                                    value: rec.get('response')
                                }
                            }).show();
                        }
                    }
                ]
            }
        ];
    }
});
//{/block}