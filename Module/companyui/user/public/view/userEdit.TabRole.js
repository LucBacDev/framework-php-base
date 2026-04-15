App.Component.on('UserEdit/construct', (that) => {
    that.toggleUserRole = (checked, targetRole) => {
        for (var i in that.state.form.roles) {
            var role = that.state.form.roles[i];
            if (role.id == targetRole.id) {
                if (checked) {
                    //nếu chọn role, và role đã có thì ko cần làm j
                    return;
                } else {
                    // bỏ trạng thái default trước đó
                    that.state.form.roles[i].default = 0;
                    // loại bỏ role trong form
                    that.state.form.roles.splice(i, 1);
                    that.setPureState({ 'form': that.state.form });
                    return;
                }
            }
        }

        if (checked) {
            //không có role, thêm vào mảng
            that.state.form.roles.push(targetRole);
            that.setPureState({ 'form': that.state.form });
        }
    }

    that.toggleAllRoles = (checked) => {
        if (checked)
            that.state.form.roles = $.extend([], that.state.roles);
        else
            that.state.form.roles = [];
        that.setPureState({ 'form': that.state.form });
    }

    that.setRoleDefault = function (role) {
        // bỏ role default đã có trước đó
        for (var i in that.state.form.roles) {
            if (that.state.form.roles[i].default == 1) {
                that.state.form.roles[i].default = 0;
                break;
            }
        }
        that.setPureState({ 'form': that.state.form });

        // reset role trong form
        that.toggleUserRole(false, role);
        // gán lại role
        role.default = 1;
        that.toggleUserRole(true, role);
    }

    that.renderClass = function (role) {
        var _name = "btn btn-sm btn-primary right btn-set-roleDefaut";
        if (that.userModel.checkRoleDefault(that.state.form, role.id)) {
            _name += " hide";
        }

        return _name;
    }

    that.tabUserRole = () => {
        return (
            <Tab id="tab-user-role" key="tab-user-role" label={Lang.t('userEdit.tabRole')}>
                <div className="card card-nav">
                    <div className="card-body car-body-info-role">
                        <span className="breadcrumb-item" dangerouslySetInnerHTML={{ __html: Lang.t('userEdit.tabRole.comment') }}></span>
                    </div>
                </div>
                <div className="p-v-20 p-v-20-role">
                    <h5>{Lang.t('userEdit.tabRole.header')}</h5>
                    <a href="javascript:;" style={{ 'display': that.state.roles.length == 0 ? 'none' : '' }} onClick={() => { that.toggleAllRoles(true) }}>{Lang.t('userEdit.tabRole.checkAll')}</a>
                    <span>&nbsp;&nbsp;&nbsp;</span>
                    <a href="javascript:;" style={{ 'display': that.state.roles.length == 0 ? 'none' : '' }} onClick={() => { that.toggleAllRoles(false) }}>{Lang.t('userEdit.tabRole.unChecked')}</a>
                    <h4></h4>
                    <table className="table table-striped table-hover">
                        <tbody>
                            {that.state.roles.map((role) =>
                                <tr key={role.id} className="tr-set-roleDefault">
                                    <th>
                                        <CheckBox
                                            id={"chk-user-role-" + role.id}
                                            onChange={(checked) => { that.toggleUserRole(checked, role); }}
                                            checked={that.userModel.hasRole(that.state.form, role.id)}
                                        />
                                    </th>
                                    <td style={{ 'width': '100%' }}>
                                        <label htmlFor={"chk-user-role-" + role.id}>{role.name}</label>
                                        {
                                            that.userModel.checkRoleDefault(that.state.form, role.id) &&
                                            <span className="text-danger"> ({Lang.t('userEdit.tabRole.default')})</span>
                                        }
                                        <button type="button" className={that.renderClass(role)} onClick={() => { that.setRoleDefault(role) }}>{Lang.t('userEdit.tabRole.setDefault')}</button>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </Tab>
        );
    }
});