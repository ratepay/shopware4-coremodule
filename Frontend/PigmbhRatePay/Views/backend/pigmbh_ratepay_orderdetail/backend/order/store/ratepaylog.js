/**
 * list
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.Order.store.RatePayLog', {
    extend:'Ext.data.Store',
    autoLoad: false,
    remoteSort : false,
    pageSize:25,
    model:'Shopware.apps.Order.model.ratepaylog'
});