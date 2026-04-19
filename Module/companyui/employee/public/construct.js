let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/employee");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'employee').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    if (App.getUser().hasPrivilege('manageEmployee')) {
        navs.push({
            'name': Lang.t("emp.nav.name"), 'icon': 'ti ti-id-badge',
            'module': "companyui/employee",
            'navs': [
                {'id': 'nhansu', 'name': Lang.t("emp.nav.list"), 'href': App.url('/:siteID/nhansu', {siteID: App.siteID})}
            ]
        });
    }

    App.Component.trigger('PageNavigator', navs);
});
