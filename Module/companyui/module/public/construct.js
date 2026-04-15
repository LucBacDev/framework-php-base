let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/module");
App.Component.trigger('Module', modules);

if (App.siteID == 'master' && App.getUser().hasPrivilege('fullcontrol')) {
    Lang.load("companyui", "module").then(() => {
        let navs = App.Component.getEventState('PageNavigator') || [];
        navs.push({
            'name': Lang.t("module.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/module",
            'navs': [
                { 'id': 'modules', 'name': Lang.t("module.nav.module"), 'href':App.url('/:siteID/modules',{siteID: App.siteID})}
            ]
        });

        App.Component.trigger('PageNavigator', navs);
    })
}
