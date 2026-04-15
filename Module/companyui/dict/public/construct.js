if (App.siteID == 'master') {
    Lang.load("companyui", "dict").then(() => {

        let navs = App.Component.getEventState('PageNavigator') || [];

        navs.push({
            'name': Lang.t("dict.nav.name"),
            'icon': 'ti ti-list',
            'navs': [
                { 'id': 'dict.collection', 'name': Lang.t("dict.nav.dictCollection"), 'href': App.url('/:siteID/dict/collection', { siteID: App.siteID }) },
                { 'id': 'dict.item', 'name': Lang.t("dict.nav.dictItem"), 'href': App.url('/:siteID/dict/item', { siteID: App.siteID }) }
            ]
        });

        App.Component.trigger('PageNavigator', navs);

    })
}

var Dict = {
    'Collection': {},
    'Item': {}
};