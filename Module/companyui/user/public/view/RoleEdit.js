class RoleEdit extends Component {

    constructor(props) {
        super(props);
        this.roleModel = new RoleModel;
        this.privilegeModel = new PrivilegeModel;

        App.Component.trigger('RoleEdit/construct', this, true);
        //Doanh: lấy dữ liệu từ trigger thêm tab
        this.state = {
            'form': this.newRole(),
            'users': [],
            'disableEdit': false,
            'privileges': [],
            'tabsDynamic': App.Component.getEventState('roleEdit/tab') || [] // nếu add tab trước khi component này được khởi tạo
        };
    }

    componentDidmount() {
        // nếu add tab sau khi component được khởi tạo
        App.Component.on('roleEdit/tab', (tabs) => {
            this.setPureState({
                'tabsDynamic': tabs
            });
        });
    }

    onModalShown() {
        setTimeout(() => {
            this.tabs.setActiveFirst();
            // this.txtID.focus();
        });
    }

    onModalHidden() {
        $(this.form).removeClass('was-validated');
        this.tabs.setActiveFirst();
    }

    static open(role) {
        RoleEdit.getInstance().then((instance) => {
            instance.setState({
                'currentForm': $.extend(instance.newRole(), role),
                'form': $.extend(instance.newRole(), role)
            }, () => {
                if (role) {
                    // add roleDefault
                    if (role.siteFK == '0') {
                        role.roleDefault = true;
                        instance.setState({
                            form: $.extend(instance.newRole(), role)
                        });
                    }
                    // get user có trong role
                    instance.getUsers(role.id);
                } else {
                    // không có user trong role khi thêm  mới
                    instance.getUsers(0);
                }
                // enable tab when edit
                instance.enableTabEdit(role);
                // get privilege
                instance.getPrivilegesSystem();
                instance.modal.showModal();
            });
        });
        return new Promise((done) => {
            RoleEdit.instance.done = done;

        });
    }

    newRole() {
        return { id: '', name: '', roleDefault: false, users: [], privileges: [], attrs: {} };
    }

    handleSubmit(ev) {
        ev.preventDefault();

        var form = $(this.form);
        form.addClass('was-validated');
        if (form[0].checkValidity() === false) {
            return;
        }

        this.roleModel.updateRole(this.state.currentForm.id, this.state.form).then((resp) => {
            if (RoleEdit.instance.done)
                RoleEdit.instance.done(resp);
            this.modal.hideModal();
        });
    }

    getUsers(roleID) {
        return new Promise((done) => {

            this.roleModel.getUsers(roleID).then((users) => {
                if (!users.length || !users[0].id) {
                    users = [];
                }

                this.setState({ 'users': users }, () => {
                    this.state.form.users = this.getIDUsers();
                    this.setState({ form: this.state.form });
                    done();
                });
            });
        });
    }

    setFormValue() {
        this.setState({ form: this.state.form });
    }

    addMember() {
        var opts = {
            'notUsers': this.getIDUsers()
        };
        UserPicker.open(opts).then((users) => {
            for (var i in users) {
                if ($.inArray(users[i].id, this.getIDUsers()) == -1) {
                    this.state.users.push(users[i]);
                }
            }
            this.setState({ 'user': this.state.users });
            //
            this.state.form.users = this.getIDUsers();
            this.setState({ 'form': this.state.form });

        });
    }


    deleteMember() {
        for (var i in this.state.users) {
            var user = this.state.users[i];
            if (user.checked) {
                this.state.users.splice(i, 1);
                this.state.form.users.splice($.inArray(user.id, this.state.form.users), 1);
            }
        }
        this.setState({ users: this.state.users, form: this.state.form });
    }

    getIDUsers() {
        var idUsers = [];
        for (var k = 0; k < this.state.users.length; k++) {
            idUsers.push(this.state.users[k].id);
        }
        return idUsers;
    }

    // check user
    toggleUser(checked, user) {
        user.checked = checked;
        this.setState({ 'users': this.state.users });
    }

    renderCheckRoleDefault() {
        var siteID = App.siteID;
        if (siteID == 'master') {
            return (
                <div className="form-group row">
                    <label className="col-sm-3 col-form-label control-label" htmlFor="txt-role-name">{Lang.t('roleEdit.roleDefault')}</label>
                    <div className="col-sm-9">
                        <CheckBox id="chk-role-default" ref="ckRoleDefault"
                            checked={this.state.form.roleDefault}
                            onChange={(checked) => { this.state.form.roleDefault = checked; this.setFormValue(); }}
                        />
                    </div>
                </div>
            )
        }
    }

    enableTabEdit(role) {
        var siteID = App.siteID;
        if (siteID == 'master') {
            this.state.disableEdit = false;
        } else {
            if (role) {
                // set enable when edit role
                if (role.siteFK != 0) {
                    this.state.disableEdit = false;
                } else {
                    this.state.disableEdit = true;
                }
            } else {
                this.state.disableEdit = false;
            }
        }
        this.setState({ 'disableEdit': this.state.disableEdit });
    }

    toggleRolePrivilege(checked, privilegeID) {
        if (checked) {
            this.state.form.privileges.push(privilegeID);
        } else {
            var idx = $.inArray(privilegeID, this.state.form.privileges);
            if (idx != -1) {
                this.state.form.privileges.splice(idx, 1);
            }
        }
        this.setState({ form: this.state.form });
    }

    getPrivilegesSystem() {
        return new Promise((done) => {
            this.privilegeModel.getAllPrivs().then((privileges) => {
                this.setState({ 'privileges': privileges }, () => {
                    done(privileges);
                });
            });
        });
    }


    toggleAllPrivilege = (checked) => {
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

        this.setState({ 'form': this.state.form });
    }

    renderTabRole() {
        return (
            <Tab id="tab-info" key="tab-info" label={Lang.t('roleEdit.tabRole')}>
                <div className="p-h-15 p-v-20">
                    <div className="form-group row">
                        <label className="col-sm-3 col-form-label control-label" htmlFor="txt-role-id">{Lang.t('roleEdit.roleCode')} <Require /></label>
                        <div className="col-sm-9">
                            <Input type="code" className="form-control" id="txt-role-id"
                                required
                                disabled={this.state.disableEdit}
                                value={this.state.form.id}
                                onChange={(event) => { this.state.form.id = event.target.value; this.setFormValue(); }}
                            />
                            <div className="invalid-tooltip">
                                {Lang.t('roleEdit.validateRole')}
                            </div>
                        </div>
                    </div>

                    <div className="form-group row">
                        <label className="col-sm-3 col-form-label control-label" htmlFor="txt-role-name">{Lang.t('roleEdit.roleName')} <Require /></label>
                        <div className="col-sm-9">
                            <input type="text" className="form-control" id="txt-role-name"
                                required
                                disabled={this.state.disableEdit}
                                value={this.state.form.name}
                                onChange={(event) => { this.state.form.name = event.target.value; this.setFormValue(); }}
                            />
                            <div className="invalid-tooltip">
                                {Lang.t('roleEdit.validateRole')}
                            </div>
                        </div>
                    </div>
                    {this.renderCheckRoleDefault()}
                </div>

            </Tab>
        );
    }

    renderTabMember() {
        return (
            <Tab id="tab-users" key="tab-users" label={Lang.t('roleEdit.tabMember')}>
                <div className="p-h-15 p-v-20">
                    <div>
                        <div className="left">
                            <button type="button" className="btn btn-primary" onClick={() => { this.addMember() }}>
                                <i className="ti ti-plus"></i> {Lang.t('roleEdit.btnNewMember')}
                            </button>
                            <button type="button" className="btn btn-primary" onClick={() => { this.deleteMember() }}>
                                <i className="ti ti-minus"></i> {Lang.t('roleEdit.btnDeleteMember')}
                            </button>
                        </div>
                        <div className="right" style={{ 'width': '200px' }}>
                            <div className="input-group">
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control" placeholder={Lang.t('roleEdit.placeSearch')} />
                            </div>
                        </div>
                        <div className="clear"></div>
                    </div>
                    <h4></h4>
                    <table className="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th style={{ 'minWidth': '50px' }}>
                                    <CheckBox />
                                </th>
                                <th style={{ 'width': '100%' }}>
                                    {Lang.t('roleEdit.memberName')}
                                </th>
                                <th style={{ 'minWidth': '300px' }}>
                                    {Lang.t('roleEdit.unit')}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {this.state.users.map((user, idx) => {
                                var siteID = App.siteID;
                                if (siteID == user.siteFK) {
                                    return (<tr key={user.id}>
                                        <td style={{ 'minWidth': '50px' }}>
                                            <CheckBox
                                                id={"chk-user-in-role-" + idx}
                                                checked={user.checked ? true : false}
                                                onChange={(checked) => { this.toggleUser(checked, user); }}
                                            />
                                        </td>
                                        <td style={{ 'width': '100%' }}>
                                            {user.fullname}
                                        </td>
                                        <td style={{ 'minWidth': '300px' }}>
                                            {user.id}
                                        </td>
                                    </tr>);
                                }
                            }
                            )}
                        </tbody>
                    </table>
                </div>
            </Tab>
        );
    }

    renderTabPriv() {
        return (
            <Tab id="tab-user-priv" key="tab-user-priv" label={Lang.t('roleEdit.tabPrivs')}>
                <div className="p-h-15 p-v-20">
                    <div className="accordion nested" id="accordion-nested" role="tablist">
                        {this.state.privileges.map((privGroup) =>
                        <div className="card" key={privGroup.name}>
                            <div className="card-header" role="tab">
                                <h5 className="card-title">
                                    <a data-toggle="collapse" href={'#privGroup-' + privGroup.id} aria-expanded="false" className="collapsed">
                                        <span>{privGroup.name}</span>
                                    </a>
                                </h5>
                            </div>
                            <div id={'privGroup-' + privGroup.id} className="collapse" data-parent="#accordion-nested" >
                                <div className="card-body">
                                    <a href="javascript:;" style={{ 'display': this.state.disableEdit ? 'none' : '' }} onClick={() => { this.toggleAllPrivilege(true) }}>{Lang.t('roleEdit.checkAll')}</a>
                                    <span>&nbsp;&nbsp;&nbsp;</span>
                                    <a href="javascript:;" style={{ 'display': this.state.disableEdit ? 'none' : '' }} onClick={() => { this.toggleAllPrivilege(false) }}>{Lang.t('roleEdit.unChecked')}</a>
                                    <h4></h4>
                                    <table className="table table-striped table-hover">
                                        <tbody>
                                            {!this.state.disableEdit && privGroup.privs.map((privilege) =>
                                                <tr key={privilege.id}>
                                                    <th>
                                                        <CheckBox
                                                            id={"chk-role-privilege-" + privilege.id}
                                                            checked={this.privilegeModel.hasPrivilege(privilege.id, this.state.form.privileges)}
                                                            onChange={(checked) => { this.toggleRolePrivilege(checked, privilege.id); }}
                                                        />
                                                    </th>
                                                    <td style={{ 'width': '100%' }}>{privilege.name}</td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>)}
                    </div>
                </div>
            </Tab>
        );
    }

    render() {
        return (
            <form role="dialog" aria-hidden="true"
                onSubmit={(ev) => { this.handleSubmit(ev); }}
                ref={(elm) => { this.form = elm; }}
                noValidate
            >
                <Modal
                    ref={(elm) => { this.modal = elm; }}
                    size="modal-lg"
                    events={{
                        'modal.shown': () => { this.onModalShown(); },
                        'modal.hidden': () => { this.onModalHidden(); }
                    }}
                >
                    <Modal.Header>{Lang.t('roleEdit.header')}</Modal.Header>
                    <Modal.Body>
                        <Tabs ref={(elm) => { this.tabs = elm; }}>
                            {this.renderTabRole()}
                            {this.renderTabMember()}
                            {this.renderTabPriv()}
                            {
                                this.state.tabsDynamic.length != 0 &&
                                this.state.tabsDynamic.map((fn) => fn(this))
                            }
                        </Tabs>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('roleEdit.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" onClick={() => { this.modal.hideModal(); }}>{Lang.t('roleEdit.btnCancel')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );

    }
}