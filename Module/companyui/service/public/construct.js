let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/service");
App.Component.trigger('Module', modules);

if (App.isFullControl && App.siteID === "master") {
    Lang.load('companyui', 'service').then(() => {

        let navs = App.Component.getEventState('PageNavigator') || [];
        navs.push({
            'name': Lang.t("service.nav.name"), 'icon': 'ti ti-list',
            'module': "companyui/service",
            'navs': [
                {'id': 'ServiceList', 'name': Lang.t("service.nav.service"), 'href': App.url('/:siteID/services', {siteID: App.siteID})}
            ]
        });
        App.Component.trigger('PageNavigator', navs);

    })
}
