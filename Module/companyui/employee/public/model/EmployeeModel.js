class EmployeeModel {

    getEmployees(filter) {
        var url = App.url('/:siteID/rest/nhansu', {siteID: App.siteID});
        filter = filter || {};
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    getEmployee(id) {
        var url = App.url('/:siteID/rest/nhansu/:id', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url
        });
    }

    getEmployeesByDep(depID) {
        var url = App.url('/:siteID/rest/nhansu/phongban/:depID', {siteID: App.siteID, depID: depID});
        return $.rest({
            'url': url
        });
    }

    updateEmployee(id, data) {
        var url = App.url('/:siteID/rest/nhansu', {siteID: App.siteID});
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'url': url,
            'method': 'put',
            'data': data
        });
    }

    deleteEmployee(id) {
        var url = App.url('/:siteID/rest/nhansu', {siteID: App.siteID});
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'url': url,
            'method': 'DELETE'
        });
    }

    toggleActive(id) {
        var url = App.url('/:siteID/rest/nhansu/:id/active', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url,
            'method': 'put'
        });
    }
}
