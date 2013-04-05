/**
 * main
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.Order.model.ratepaypositions', {
    extend : 'Ext.data.Model',
    fields: [ 'name','articleID', 'articleordernumber', 'price', 'quantity','delivered', 'cancelled', 'returned' ],
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url controller=PigmbhRatepayOrderDetail action=loadPositionStore}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'total'
        }
    }
});