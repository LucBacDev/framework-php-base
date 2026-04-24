let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/employee");
App.Component.trigger('Module', modules);

// Clear old lang cache để load key mới (emp.btnActivate, emp.btnDeactivate)
(function() {
    var _lang = localStorage.getItem("lang") || 'vi';
    var _cacheKey = App.siteUrl + '/modules/companyui/employee/langs/' + _lang;
    localStorage.removeItem(_cacheKey);
})();

Lang.load('companyui', 'employee').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    // Chỉ hiện nếu có quyền Quản lý người dùng hoặc Admin tối cao
    if (App.getUser().hasPrivilege('manageUser') || App.isFullControl) {
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
