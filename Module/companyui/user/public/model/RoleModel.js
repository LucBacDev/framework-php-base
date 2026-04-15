class RoleModel {
    getRoles() {
        var url = App.url('/:siteID/rest/roles', { siteID: App.siteID });
        return $.rest({
            'url': url
        });
    }

    getUsers(roleID) {
        var url = App.url('/:siteID/rest/roles/:roleID/users', { siteID: App.siteID, roleID: roleID });
        return $.rest({
            'url': url
        });
    }

    updateRole(id, data) {
        var url = App.url('/:siteID/rest/roles', { siteID: App.siteID });
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'method': 'PUT',
            'url': url,
            'data': data
        });
    }

    updateRoleUser(id, data) {
        var url = App.url('/:siteID/rest/roles/user', { siteID: App.siteID });
        if (id) {
            url += '/' + id;
        }
        return $.rest({
            'method': 'PUT',
            'url': url,
            'data': data
        });
    }

    deleteRole(roleID) {
        var url = App.url('/:siteID/rest/roles/:roleID', { siteID: App.siteID, roleID: roleID });
        return $.rest({
            'method': 'DELETE',
            'url': url,
        });
    }


    getListCustomDisplay() {
        var url = App.url('/:siteID/rest/listCustomDisplay', { siteID: App.siteID });
        return $.rest({
            'url': url
        });
    }

    getListDiagConfig() {
        var url = App.url('/:siteID/rest/listDiagConfig', { siteID: App.siteID });
        return $.rest({
            'url': url
        });
    }
}