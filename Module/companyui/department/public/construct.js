let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/department");
App.Component.trigger('Module', modules);

// Clear old lang cache để load key mới
(function() {
    var _lang = localStorage.getItem("lang") || 'vi';
    var _cacheKey = App.siteUrl + '/modules/companyui/department/langs/' + _lang;
    localStorage.removeItem(_cacheKey);
})();

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
