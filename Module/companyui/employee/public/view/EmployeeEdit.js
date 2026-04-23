class EmployeeEdit extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            'form': this.newEmployee(),
            'empID': null,
            'roles': [],
            'privileges': []
        };
        this.empModel = new EmployeeModel;
        this.userModel = typeof UserModel !== 'undefined' ? new UserModel() : null;
        this.roleModel = typeof RoleModel !== 'undefined' ? new RoleModel() : null;
        this.privilegeModel = typeof PrivilegeModel !== 'undefined' ? new PrivilegeModel() : null;
        
        this.bindThis(['tabBasic', 'tabUserRole', 'tabUserPriv']);
    }

    newEmployee() {
        return {
            id: 0,
            active: true,
            fullname: '',
            code: '',
            email: '',
            phone: '',
            password: '',
            repassword: '',
            position: '',
            depFK: 0,
            roles: [],
            privileges: [],
            department: {
                'name': '',
                'id': 0
            }
        };
    }

    static open(employee) {
        var employee = employee;
        EmployeeEdit.getInstance().then((instance) => {
            employee = $.extend(instance.newEmployee(), employee);

            if (!employee.department) {
                employee.department = {
                    'name': '',
                    'id': 0
                };
            }

            instance.setState({
                'empID': employee.id,
                'form': employee
            }, () => {
                // Lấy Role từ db
                if (instance.roleModel) {
                    instance.roleModel.getRoles().then((roles) => {
                        instance.setPureState({ 'roles': roles.rsPrivate });
                    });
                }
                // Lấy Privilege từ db
                if (instance.privilegeModel) {
                    instance.privilegeModel.getAllPrivs().then((privileges) => {
                        instance.setPureState({ 'privileges': privileges });
                    });
                }
                // Lấy user roles logic cũ để hiển thị nếu là update
                if (employee.id && instance.userModel) {
                    instance.userModel.getUser(employee.id).then((user) => {
                        var form = instance.state.form;
                        form.roles = user.roles || [];
                        form.privileges = user.privileges || [];
                        instance.setPureState({ 'form': form });
                    }).catch(console.error);
                }
            });

            instance.modal.showModal();
        });

        return new Promise((done) => {
            EmployeeEdit.instance.done = done || new Function;
        });
    }

    onModalShown() {
        setTimeout(() => {
            if(this.txtFullname && this.txtFullname.focus) this.txtFullname.focus();
            $(this.form).removeClass('was-validated');
            if (this.tabs) this.tabs.setActive('tab-emp-basic');
        });
    }

    onModalHidden() {
        $(this.form).removeClass('was-validated');
    }

    handleChangeRePassword(ev) {
        this.state.form.repassword = ev.target.value;
        this.setPureState({ form: this.state.form });
        if (this.state.form.repassword !== this.state.form.password) {
            if (this.txtRePassword) this.txtRePassword.setValid(false);
        } else {
            if (this.txtRePassword) this.txtRePassword.setValid(true);
        }
    }

    handleSubmit(ev) {
        ev.preventDefault();

        var form = $(this.form);
        $(form).addClass('was-validated');
        if (form[0].checkValidity() === false) {
            return;
        }

        var data = $.extend({}, this.state.form);

        if (!data.id) {
            if (!data.repassword || data.repassword !== data.password) {
                if (this.txtRePassword) this.txtRePassword.setValid(false);
                return;
            }
        }

        this.empModel.updateEmployee(this.state.empID, data).then((resp) => {
            if (EmployeeEdit.instance.done)
                EmployeeEdit.instance.done(resp);
            this.modal.hideModal();
        }).catch((xhr) => {
            var msg = Lang.t('emp.error.update');
            try {
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                else if (xhr.responseText) msg = JSON.parse(xhr.responseText).message;
            } catch (e) {}
            Alert.open(msg);
        });
    }

    pickDepartment() {
        if (typeof DepPicker !== 'undefined') {
            DepPicker.open({
                'selectedDepID': this.state.form.depFK
            }).then((deps) => {
                this.state.form.department = deps[0];
                this.state.form.depFK = deps[0].id;
                this.setPureState({form: this.state.form});
            });
        }
    }

    renderDepName(dep) {
        if (!dep || !dep.name || dep.name === 'RootDirectory') {
            return Lang.t('emp.edit.noDep');
        }
        return dep.name;
    }

    toggleUserRole(checked, targetRole) {
        for (var i in this.state.form.roles) {
            var role = this.state.form.roles[i];
            if (role.id == targetRole.id) {
                if (checked) return;
                else {
                    this.state.form.roles[i].default = 0;
                    this.state.form.roles.splice(i, 1);
                    this.setPureState({ 'form': this.state.form });
                    return;
                }
            }
        }
        if (checked) {
            this.state.form.roles.push(targetRole);
            this.setPureState({ 'form': this.state.form });
        }
    }

    toggleAllRoles(checked) {
        if (checked)
            this.state.form.roles = $.extend([], this.state.roles);
        else
            this.state.form.roles = [];
        this.setPureState({ 'form': this.state.form });
    }

    setRoleDefault(role) {
        for (var i in this.state.form.roles) {
            if (this.state.form.roles[i].default == 1) {
                this.state.form.roles[i].default = 0;
                break;
            }
        }
        this.setPureState({ 'form': this.state.form });
        this.toggleUserRole(false, role);
        role.default = 1;
        this.toggleUserRole(true, role);
    }

    renderClass(role) {
        var _name = "btn btn-sm btn-primary right btn-set-roleDefaut";
        if (this.userModel && this.userModel.checkRoleDefault(this.state.form, role.id)) {
            _name += " hide";
        }
        return _name;
    }

    toggleUserPrivilege(checked, privilegeID) {
        if (checked) {
            this.state.form.privileges.push(privilegeID);
        } else {
            var idx = $.inArray(privilegeID, this.state.form.privileges);
            if (idx != -1) {
                this.state.form.privileges.splice(idx, 1);
            }
        }
        this.setPureState({ form: this.state.form });
    }

    toggleAllPrivilege(checked) {
        if (checked) {
            var tmp = [];
            for (var i in this.state.privileges) {
                var priv = this.state.privileges[i];
                tmp.push(priv.id);
            }
            this.state.form.privileges = $.extend([], tmp);
        }
        else
            this.state.form.privileges = [];
        this.setPureState({ 'form': this.state.form });
    }

    tabBasic() {
        return (
            <Tab id="tab-emp-basic" key="tab-emp-basic" label="Thông tin chung">
                <div className="p-v-20">
                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.fullname')} <Require /></label>
                        <div className="col-sm-8">
                            <input type="text" className="form-control"
                                required
                                ref={(input) => { this.txtFullname = input; }}
                                value={this.state.form.fullname}
                                onChange={(ev) => { this.state.form.fullname = ev.target.value; this.setPureState({form: this.state.form}); }}
                            />
                            <div className="invalid-tooltip"> {Lang.t('emp.validate.fullname')} </div>
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.email')} <Require /></label>
                        <div className="col-sm-8">
                            <input type="email" className="form-control" required
                                value={this.state.form.email}
                                onChange={(ev) => { this.state.form.email = ev.target.value; this.setPureState({form: this.state.form}); }}
                            />
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.code')}</label>
                        <div className="col-sm-8">
                            <input type="text" className="form-control"
                                value={this.state.form.code}
                                onChange={(ev) => { this.state.form.code = ev.target.value; this.setPureState({form: this.state.form}); }}
                            />
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.phone')}</label>
                        <div className="col-sm-8">
                            <input type="text" className="form-control"
                                value={this.state.form.phone}
                                onChange={(ev) => { this.state.form.phone = ev.target.value; this.setPureState({form: this.state.form}); }}
                            />
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.position')}</label>
                        <div className="col-sm-8">
                            <input type="text" className="form-control"
                                value={this.state.form.position}
                                onChange={(ev) => { this.state.form.position = ev.target.value; this.setPureState({form: this.state.form}); }}
                            />
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.department')}</label>
                        <div className="col-sm-8">
                            <div className="input-group" style={{'cursor': 'pointer'}} onClick={() => { this.pickDepartment(); }}>
                                <input type="text" className="form-control" readOnly style={{'cursor': 'pointer'}}
                                    value={this.renderDepName(this.state.form.department)} />
                                <div className="input-group-append">
                                    <span className="input-group-text">{Lang.t('emp.edit.chooseDep')}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.status')}</label>
                        <div className="col-sm-8">
                            <CheckBox
                                checked={this.state.form.active}
                                onChange={(checked) => { this.state.form.active = checked; this.setPureState({form: this.state.form}); }}
                            />
                        </div>
                    </div>

                    {(!this.state.empID || this.state.form.id == 0) && <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">Mật khẩu đăng nhập <Require /></label>
                        <div className="col-sm-8">
                            <Input type="password" className="form-control" required
                                value={this.state.form.password}
                                onChange={(ev) => { this.state.form.password = ev.target.value; this.setPureState({form: this.state.form}); }} 
                            />
                        </div>
                    </div>}

                    {(!this.state.empID || this.state.form.id == 0) && <div className="form-group row">
                        <label className="col-sm-4 col-form-label control-label">Nhập lại mật khẩu <Require /></label>
                        <div className="col-sm-8">
                            <Input type="password" className="form-control" required
                                ref={(elm) => { this.txtRePassword = elm; }}
                                value={this.state.form.repassword}
                                onChange={(ev) => { this.handleChangeRePassword(ev); }} 
                            />
                            <div className="invalid-tooltip"> Mật khẩu nhập lại không trùng khớp </div>
                        </div>
                    </div>}
                </div>
            </Tab>
        );
    }

    tabUserRole() {
        return (
            <Tab id="tab-emp-role" key="tab-emp-role" label="Nhóm (Vai trò)">
                <div className="p-v-20">
                    <a href="javascript:;" style={{ 'display': this.state.roles.length == 0 ? 'none' : '' }} onClick={() => { this.toggleAllRoles(true) }}>Chọn tất cả</a>
                    <span>&nbsp;&nbsp;&nbsp;</span>
                    <a href="javascript:;" style={{ 'display': this.state.roles.length == 0 ? 'none' : '' }} onClick={() => { this.toggleAllRoles(false) }}>Bỏ chọn tất cả</a>
                    <table className="table table-striped table-hover m-t-15">
                        <tbody>
                            {this.state.roles.map((role) =>
                                <tr key={role.id} className="tr-set-roleDefault">
                                    <th>
                                        <CheckBox
                                            id={"chk-user-role-" + role.id}
                                            onChange={(checked) => { this.toggleUserRole(checked, role); }}
                                            checked={this.userModel ? this.userModel.hasRole(this.state.form, role.id) : false}
                                        />
                                    </th>
                                    <td style={{ 'width': '100%' }}>
                                        <label htmlFor={"chk-user-role-" + role.id}>{role.name}</label>
                                        {
                                            (this.userModel && this.userModel.checkRoleDefault(this.state.form, role.id)) &&
                                            <span className="text-danger"> ({Lang.t('userEdit.tabRole.default') || 'Mặc định'})</span>
                                        }
                                        <button type="button" className={this.renderClass(role)} onClick={() => { this.setRoleDefault(role) }}>{Lang.t('userEdit.tabRole.setDefault') || 'Đặt làm mặc định'}</button>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </Tab>
        );
    }

    tabUserPriv() {
        return (
            <Tab id="tab-emp-priv" key="tab-emp-priv" label="Quản lý quyền">
                <div className="p-v-20">
                {this.state.privileges.map((privGroup) => <div className="accordion nested" id="accordion-nested" role="tablist" key={privGroup.id}>
                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a data-toggle="collapse" href={'#privgroup-' + privGroup.id} aria-expanded="false" className="collapsed">
                                    <span>{privGroup.name}</span>
                                </a>
                            </h5>
                        </div>
                        <div id={'privgroup-' + privGroup.id} className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                <a href="javascript:;" style={{'display': privGroup.privs.length == 0 ? 'none' : ''}} onClick={() => { this.toggleAllPrivilege(true) }}>Chọn tất cả</a>
                                <span>&nbsp;&nbsp;&nbsp;</span>
                                <a href="javascript:;" style={{'display': privGroup.privs.length == 0 ? 'none' : ''}} onClick={() => { this.toggleAllPrivilege(false) }}>Bỏ chọn tất cả</a>
                                <table className="table table-striped table-hover m-t-15">
                                    <tbody>
                                    {privGroup.privs.map((privilege) =>
                                        <tr key={privilege.id} >
                                            <th>
                                                <CheckBox
                                                    id={"chk-role-privilege-" + privilege.id}
                                                    checked={this.privilegeModel ? this.privilegeModel.hasPrivilege(privilege.id, this.state.form.privileges) : false}
                                                    onChange={(checked) => { this.toggleUserPrivilege(checked, privilege.id); }}
                                                />
                                            </th>
                                            <td style={{ 'width': '100%' }}>{privilege.name}</td>
                                        </tr>
                                    )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>)}
                </div>
            </Tab>
        );
    }

    render() {
        return (
            <form role="dialog" aria-hidden="true" noValidate
                onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }}
            >
                <Modal
                    ref={(elm) => { this.modal = elm; }}
                    size="modal-lg"
                    events={{
                        'modal.shown': () => { this.onModalShown(); },
                        'modal.hidden': () => { this.onModalHidden(); }
                    }}
                >
                    <Modal.Header>{Lang.t('emp.edit.header') || 'Thông tin nhân sự'}</Modal.Header>
                    <Modal.Body>
                        <Tabs className="tab-info center-tabs" ref={(elm) => { this.tabs = elm; }} preRender>
                            {this.tabBasic()}
                            {this.tabUserRole()}
                            {this.tabUserPriv()}
                        </Tabs>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('emp.btnSave') || 'Ghi lại'}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('emp.btnClose') || 'Hủy bỏ'}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}
