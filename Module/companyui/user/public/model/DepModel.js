class DepModel {

    updateDep(depID, updateData) {
        var url = App.url('/:siteID/rest/departments',{siteID: App.siteID});
        if (depID) {
            url += '/' + depID;
        }

        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

    getDeps(filter) {
        var url = App.url('/:siteID/rest/departments',{siteID: App.siteID});
        filter = filter || {};
        return $.rest({
            'data': filter,
            'url': url
        });
    }

    getDep(depID, opts) {
        var url = App.url('/:siteID/rest/departments/:depID',{siteID: App.siteID, depID: depID});
        return $.rest({
            'url': url,
            'data': opts
        });
    }

    deleteDep(depID) {
        var url = App.url('/:siteID/rest/departments',{siteID: App.siteID});
        if (depID) {
            url += '/' + depID;
        }
        return $.rest({
            'url': url,
            'method': 'DELETE'
        });
    }
}