/**
 * main
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.PigmbhRatepayLogging.controller.Main', {
    extend: 'Ext.app.Controller',
    mainWindow: null,
    init: function() {
        var me = this;
        me.mainWindow = me.getView('main.Window').create({
            listStore: me.getStore('List').load()
        });
        me.callParent(arguments);
    }
});