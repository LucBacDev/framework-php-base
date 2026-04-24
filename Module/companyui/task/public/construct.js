let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/task");
App.Component.trigger('Module', modules);

// Clear lang cache to load new keys
(function() {
    var _lang = localStorage.getItem("lang") || 'vi';
    var _cacheKey = App.siteUrl + '/modules/companyui/task/langs/' + _lang;
    localStorage.removeItem(_cacheKey);
})();

Lang.load('companyui', 'task').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    // Luôn hiện Quản lý công việc cho tất cả mọi người có quyền accessAdmin
    navs.push({
        'name': Lang.t("task.nav.name"), 'icon': 'ti ti-layout-media-overlay-alt-2',
        'module': "companyui/task",
        'navs': [
            {'id': 'taskboard', 'name': Lang.t("task.nav.board"), 'href': App.url('/:siteID/task', {siteID: App.siteID || localStorage.getItem('site')})}
        ]
    });

    App.Component.trigger('PageNavigator', navs);
});
