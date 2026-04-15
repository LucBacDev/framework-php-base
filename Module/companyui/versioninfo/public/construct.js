let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/versionInfo");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'versionInfo').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    navs.push({
        'name': Lang.t("versionInfo.nav.name"), 'icon': 'fa fa-info-circle',
        'module': "companyui/versionInfo",
        'navs': [
            {'id': 'versionInfo', 'name': Lang.t("versionInfo.nav.versionInfo"), 'href': App.url('/:siteID/versionInfo', {siteID: App.siteID})}
        ]
    });

    App.Component.trigger('PageNavigator', navs);

})

var VersionInfo = {

};