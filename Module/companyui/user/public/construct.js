let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/user");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'user').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    if (App.isFullControl) {
        navs.push({
            'name': Lang.t("user.nav.name"), 'icon': 'ti ti-user',
            'module': "companyui/user",
            'navs': [
                {'id': 'users', 'name': Lang.t("user.nav.user"), 'href':  App.url('/:siteID/users',{siteID: App.siteID})},
                {'id': 'roles', 'name': Lang.t("user.nav.role"), 'href': App.url('/:siteID/roles',{siteID: App.siteID})}
            ]
        });
    }

    App.Component.trigger('PageNavigator', navs);
})
