/**
 * list
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.PigmbhRatepayLogging.store.List', {
    extend:'Ext.data.Store',
    autoLoad: false,
    pageSize:10,
    model:'Shopware.apps.PigmbhRatepayLogging.model.Main'
});