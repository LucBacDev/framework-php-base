let modules = App.Component.getEventState('Module') || [];
modules.push("companyui/queue");
App.Component.trigger('Module', modules);

Lang.load('companyui', 'queue').then(() => {
    let navs = App.Component.getEventState('PageNavigator') || [];

    if (App.isFullControl)
        navs.push({
            'name': Lang.t("queue.nav.name"), 'icon': 'ti ti-settings',
            'module': "companyui/queue",
            'navs': [
                {'id': 'MessageQueueManager', 'name': Lang.t("queue.nav.MessageQueueManager"), 'href': App.url('/master/messageQueueManager')},
            ]
        });


    App.Component.trigger('PageNavigator', navs);

})
