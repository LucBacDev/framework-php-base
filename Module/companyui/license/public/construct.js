let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/license");
App.Component.trigger('Module', modules);

if (App.isFullControl)
    Lang.load('companyui', 'license').then(() => {
        let navs = App.Component.getEventState('PageNavigator') || [];

        navs.push({
            'name': Lang.t("license.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/license",
            'navs': [
                { 'id': 'license', 'name': Lang.t("license.nav.license"), 'href': App.url('/:siteID/license', {siteID: App.siteID}) }
            ]
        });

        App.Component.trigger('PageNavigator', navs);
    })
var License = {};

setTimeout(() => {
    var licenseModel = new License.Model;
    licenseModel.autoCheckLicense();
}, 1500);