/**
 * app
 *
 * @category   PayIntelligent
 * @package    PigmbhRatepay
 * @copyright  Copyright (c) 2013 PayIntelligent GmbH (http://payintelligent.de)
 */
Ext.define('Shopware.apps.PigmbhRatepayLogging', {
    extend:'Enlight.app.SubApplication',
    name:'Shopware.apps.PigmbhRatepayLogging',
    bulkLoad: true,
    loadPath: '{url action=load}',
    controllers: ['Main'],
    models: ['Main'],
    views: ['main.Window'],
    store:['List'],
    launch: function() {
        var me = this;
        mainController = me.getController('Main');
        return mainController.mainWindow;
    }
});