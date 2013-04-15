/**
 * main
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.Order.model.ratepayhistory', {
    extend : 'Ext.data.Model',
    fields: [ 'date', 'event', 'articlename', 'articlenumber', 'quantity'],
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url controller=PigmbhRatepayOrderDetail action=loadHistoryStore}'
        },
        reader : {
            type : 'json',
            root : 'data'
        }
    }
});