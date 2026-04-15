App = window.App || {};
App.getUser = function () {
    if (!App.user)
        App.user = {};
    //kiểm tra các biến đã được init chưa
    if (!App.user.hasPrivilege) {
        App.user.hasPrivilege = function (privCode) {
            if (privCode != 'fullcontrol' && App.user.hasPrivilege('fullcontrol')) {
                return true; //fullcontrol có hết quyền
            }
            //kiểm tra của nsd
            if (App.user.privileges && App.user.privileges.indexOf(privCode) > -1)
                return true;
            //kiểm tra quyền trong nhóm
            for (var i in App.user.roles) {
                var privs = App.user.roles[i].privileges;
                if (privs.indexOf(privCode) > -1)
                    return true;
            }
            return false;
        };

        App.user.hasRole = function (roleCode) {
            for (var i in App.user.roles) {
                var role = App.user.roles[i];
                if (role.id == roleCode)
                    return true;
            }
            return false;
        };


    }

    return App.user;
};

/**
 * 
 * @param {*} opts key: privleges, privilege, roles, role
 */
App.requireLogin = function (opts) {
    var FAIL_PRIVILEGE = 'privilege';
    var FAIL_ROLE = 'role';
    var FAIL_LOGIN = 'login';

    if (App.requireLogin.inv)
        return;

    if (!App.user || !App.user.id) {
        window.location = App.siteUrl + '/auth/login';
        return;
    }
    opts = opts || {};
    opts.roles = opts.roles || [];
    opts.privileges = opts.privileges || [];
    if (opts.role)
        opts.roles.push(opts.role);
    if (opts.privilege)
        opts.privileges.push(opts.privilege);

    check();

    App.requireLogin.inv = setInterval(refresh, 60000);
    $(window).focus(refresh);

    function refresh() {
        $.rest({
            'url': App.siteUrl + '/rest/auth'
        }).then(function (user) {
            App.user = $.extend(App.user, user);
            check();
        }).catch(function () {
            fail(FAIL_LOGIN);
        });
    }

    function check() {
        if (!App.user || !App.user.id) {
            fail();
            return;
        }
        //kiểm tra quyền
        if (opts.privileges) {
            for (var i in opts.privileges) {
                if (!App.user.hasPrivilege(opts.privileges[i])) {
                    fail(FAIL_PRIVILEGE);
                    return;
                }
            }
        }
        //Kiểm tra nhóm
        if (opts.roles) {
            for (var i in opts.roles) {
                if (!App.user.hasRole(opts.roles[i])) {
                    fail(FAIL_ROLE);
                    return;
                }
            }
        }

        //nếu ok xóa hết thông báo lỗi
        $('.alert-login').remove();
    }

    function fail(type) {
        type = type || FAIL_LOGIN;

        if (type == FAIL_LOGIN)
            var msg = 'Phiên đăng nhập đã hết do chờ quá lâu hoặc đăng xuất, vui lòng <a href="' + App.siteUrl + '/auth/login">Đăng nhập lại</a>!';
        else if (type == FAIL_PRIVILEGE || type == FAIL_ROLE)
            var msg = 'Bạn không có quyên truy cập chức năng này, vui lòng <a href="' + App.siteUrl + '/home">Quay lại trang chủ</a>!';
        else
            var msg = 'Xảy ra lỗi khi kiểm tra đăng nhập';

        $('body').append('<div class="alert alert-danger alert-login" role="alert">'
            + msg
            + '</div>');
    }

};

/**
 * App.url('/rest/:siteID/users/:id', {siteID: 'master', id: 10})
 * @param {type} path
 * @param {type} params
 * @returns {undefined}
 */
App.url = function (path, params) {
    params = params || {};
    if (path.substr(0, 4).toLowerCase() != "http") {
        //duong dan tuong doi, them siteUrl vao
        path = App.siteUrl + path;
    }
    for (var i in params) {
        path = path.replace(new RegExp(':' + i, "g"), params[i]);
    }

    return path;
}

