class DepartmentModel {

    getDepartments(filter) {
        var url = App.url('/:siteID/rest/phongban', {siteID: App.siteID});
        filter = filter || {};
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    getDepartment(id, opts) {
        var url = App.url('/:siteID/rest/phongban/:id', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url,
            'data': opts
        });
    }

    updateDepartment(id, data) {
        var url = App.url('/:siteID/rest/phongban', {siteID: App.siteID});
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'url': url,
            'method': 'put',
            'data': data
        });
    }

    deleteDepartment(id) {
        var url = App.url('/:siteID/rest/phongban', {siteID: App.siteID});
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'url': url,
            'method': 'DELETE'
        });
    }

    toggleActive(id) {
        var url = App.url('/:siteID/rest/phongban/:id/active', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url,
            'method': 'PUT'
        });
    }
}
