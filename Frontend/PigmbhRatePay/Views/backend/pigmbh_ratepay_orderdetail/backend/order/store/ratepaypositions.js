/**
 * list
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.Order.store.ratepaypositions', {
    extend:'Ext.data.Store',
    autoLoad: false,
    remoteSort : false,
    model:'Shopware.apps.Order.model.ratepaypositions'
});