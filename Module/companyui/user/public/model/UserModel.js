class UserModel {
    handleLogin(method, account, password, captcha) {
        var url = App.siteUrl + '/rest/auth';
        return $.rest({
            'url': url,
            'data': {
                'type': method,
                'account': account,
                'password': password,
                'captcha': captcha
            },
            'method': 'POST'
        });
    }

    getUsers(filter) {
        var url = App.url('/:siteID/rest/users', { siteID: App.siteID });
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    getUser(id) {
        var url = App.url('/:siteID/rest/users/:id', { siteID: App.siteID, id: id });
        return $.rest({
            'url': url
        });
    }

    hasRole(user, roleID) {
        if (!user || !user.roles)
            return false;
        for (var i in user.roles) {
            var role = user.roles[i];
            if (role.id == roleID)
                return true;
        }
        return false;
    }

    
    checkRoleDefault(user, roleID) {
        if (!user || !user.roles)
            return false;
        for (var i in user.roles) {
            var role = user.roles[i];
            if (role.id == roleID && role.default == 1)
                return true;
        }
        return false;
    }

    updateUser(userID, updateData) {
        var url = App.url('/:siteID/rest/users', { siteID: App.siteID });
        if (userID) {
            url += '/' + userID;
        }
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

    deleteUser(userID) {
        var url = App.url('/:siteID/rest/users', { siteID: App.siteID });
        if (userID) {
            url += '/' + userID;
        }
        return $.rest({
            'url': url,
            'method': 'DELETE'
        });
    }

    changePassword(data) {
        var url = App.url('/:siteID/rest/users/changePassword', { siteID: App.siteID });

        return $.rest({
            'url': url,
            'method': 'POST',
            'data': data
        });
    }
}