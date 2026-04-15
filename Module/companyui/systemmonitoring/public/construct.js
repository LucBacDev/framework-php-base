let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/systemmonitoring");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'systemmonitoring').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    if (App.isFullControl && App.siteID === "master")
        navs.push({
            'name': Lang.t("systemmonitoring.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/systemmonitoring",
            'navs': [
                {'id': 'SystemMonitoring', 'name': Lang.t("systemmonitoring.nav.monitoring"), 'href': App.url('/master/systemmonitoring', {siteID: App.siteID})},
            ]
        });


    App.Component.trigger('PageNavigator', navs);

})
