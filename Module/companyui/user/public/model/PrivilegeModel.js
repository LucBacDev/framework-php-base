class PrivilegeModel {
    getAllPrivs() {
        return $.rest({
            'url': App.url('/rest/privileges/all')
        });
    }

    hasPrivilege(privilegeID, arrPrivilegeID) {
        var idx = $.inArray(privilegeID, arrPrivilegeID);
        if(idx == -1)
            return false;
        return true;
    }
}