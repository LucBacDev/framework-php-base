let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/setting");
App.Component.trigger('Module', modules);

if(App.getUser().hasPrivilege('accessAdmin')) {

    Lang.load('companyui', 'setting').then(() => {
        let navs = App.Component.getEventState('PageNavigator') || [];

        navs.push({
            'name': Lang.t("systemSetting.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/setting",
            'navs': [
                { 'id': 'setting', 'name': Lang.t("systemSetting.nav.setting"), 'href': App.url('/:siteID/setting', { siteID: App.siteID }) },
                // { 'id': 'setting-integrate', 'name': Lang.t("systemSetting.nav.integrate"), 'href': App.url('/:siteID/settingIntegrate', { siteID: App.siteID }) }
            ]
        });

        App.Component.trigger('PageNavigator', navs);
    })
}
var Setting = {};
var SettingIntegrate = {};