let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/site");
App.Component.trigger('Module', modules);

// chỉ hiện quản lý site khi là quản trị hệ thống
if (App.siteID == 'master' && App.getUser().hasPrivilege('fullcontrol')) {
    Lang.load('companyui', 'site').then(() => {
        let navs = App.Component.getEventState('PageNavigator') || [];
        navs.push({
            'name': Lang.t("site.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/site",
            'navs': [
                {'id': 'sites', 'name': Lang.t("site.nav.site"), 'href':App.url('/:siteID/sites',{siteID: App.siteID})}
            ]
        });
        App.Component.trigger('PageNavigator', navs);
    })
}

