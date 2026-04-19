let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/department");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'department').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    if (App.isFullControl) {
        navs.push({
            'name': Lang.t("dep.nav.name"), 'icon': 'ti ti-home',
            'module': "companyui/department",
            'navs': [
                {'id': 'phongban', 'name': Lang.t("dep.nav.list"), 'href': App.url('/:siteID/phongban', {siteID: App.siteID})}
            ]
        });
    }

    App.Component.trigger('PageNavigator', navs);
});
