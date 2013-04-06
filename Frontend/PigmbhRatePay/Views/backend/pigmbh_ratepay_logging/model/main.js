/**
 * main
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.PigmbhRatepayLogging.model.Main', {
    extend : 'Ext.data.Model',
    fields: [ 'date', 'version', 'operation', 'suboperation', 'transactionId', 'firstname', 'lastname','request','response'],
    proxy : {
        type : 'ajax',
        api:{
            read:   '{url action=loadStore}'
        },
        reader : {
            type : 'json',
            root : 'data',
            totalProperty: 'total'
        }
    }
});