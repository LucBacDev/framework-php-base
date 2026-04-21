window.userEditCompId = 0;

class UserEdit extends PureComponent {
    constructor(props) {
        super(props);
        this.elmId = 'modal-user-edit-' + window.userEditCompId++;
        this.userModel = new UserModel;
        this.roleModel = new RoleModel;
        this.privilegeModel = new PrivilegeModel;
        App.Component.trigger('UserEdit/construct', this, true);
        this.bindThis([
            'tabUserBasic', 'tabUserRole', 'tabUserPriv', 'tabUserLogin'
        ]);
        this.state = {
            'form': this.newUser(),
            'roles': [],
            'tabs': [
                this.tabUserBasic, //userEdit.TabBasic.js
                this.tabUserLogin, //userEdit.TabLogin.js
                this.tabUserRole, //userEdit.TabRole.js
                this.tabUserPriv //userEdit.TabPriv.js
            ],
            'privileges': []
        };
    }

    pickDep(dep) {
        DepPicker.open(dep).then((deps) => {
            var dep = deps[0];
            this.state.form.department = dep;
            this.state.form.depFK = dep.id;
            this.setPureState({ 'form': this.state.form });
        });
    }

    static open(user) {
        var user = user;
        UserEdit.getInstance().then((instance) => {
            user = $.extend(instance.newUser(), user);
            instance.setPureState({ 'form': user }, () => {
                //load lại dữ liệu cho chắc
                if (user.id) {
                    instance.userModel.getUser(user.id).then((user) => {
                        instance.setPureState({ 'form': user });
                    });
                }
                //load nhom
                instance.roleModel.getRoles().then((roles) => {
                    instance.setState({ 'roles': roles.rsPrivate });
                });
            });
            // get privilege
            instance.getAllPrivs();

            instance.modal.showModal();
        });
        // instance.modal.showModal();
        return new Promise((done) => {
            UserEdit.instance.done = done || new Function;
        });
    }

    newUser() {
        return {
            'id': 0,
            'fullname': '',
            'jobTitle': '',
            'login': { 'localdb': { 'account': '', 'password': '', 'repassword': '' } },
            'department': { 'id': 0, 'name': Lang.t('RootDirectory') },
            'desc': '',
            'active': 1,
            'depFK': 0,
            'privileges': [],
            'userLinkID': 0
        };
    }

    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');

        this.tabs.setActive('tab-user-basic').then(() => {
            if (this.txtName && this.txtName.focus)
                this.txtName.focus();
        });
    }

    onModalHidden() {
        //reset ui về trạng thái mặc định
        this.tabs.setActive('tab-user-basic');
    }

    componentDidMount() {

    }

    setTab(tabName) {
        $('[href="#' + tabName + '"]', this.modal).click();
    }

    setFormValue() {
        this.setPureState({ form: this.state.form });
    }

    handleSubmit(ev) {
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        // nhập lại password sai khi thêm mới
        if (!data.id) {
            var pwd = data.login && data.login.localdb ? data.login.localdb.password : '';
            var rpwd = data.login && data.login.localdb ? data.login.localdb.repassword : '';
            if (!rpwd || rpwd !== pwd) {
                this.txtRePassword.setValid(false);
                $(form).addClass('was-validated');
                return;
            }
        }

        this.userModel.updateUser(this.state.form.id, data).then((resp) => {
            if (UserEdit.instance.done)
                UserEdit.instance.done(resp);

            this.modal.hideModal();
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    handleChangeRePassword(ev) {
        this.state.form.login.localdb.repassword = ev.target.value;
        this.setPureState({ form: this.state.form });
        if (this.state.form.login.localdb.repassword !== this.state.form.login.localdb.password) {
            this.txtRePassword.setValid(false);
        } else {
            this.txtRePassword.setValid(true);
        }
    }

    // lấy danh sách quyền quản trị hệ thống
    getAllPrivs() {
        return new Promise((done) => {
            this.privilegeModel.getAllPrivs().then((privileges) => {
                this.setPureState({ 'privileges': privileges }, () => {
                    done(privileges);
                });
            });
        });
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

        this.setPureState({ 'form': this.state.form });
    }



    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} 
                  ref={(elm) => { this.form = elm; }} 
                  noValidate>
                <Modal 
                  ref={(elm) => { this.modal = elm; }} 
                  events={{
                    'modal.shown':  () => { this.onModalShown(); },
                    'modal.hidden': () => { this.onModalHidden(); }
                }}>
                    <Modal.Header>{Lang.t('userEdit.tabLogin')}</Modal.Header>
                    <Modal.Body>
                        <Tabs className="tab-info center-tabs" ref={(elm) => { this.tabs = elm; }} preRender>
                            {this.state.tabs.map((fn) => fn())}
                        </Tabs>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('userEdit.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('userEdit.btnCancel')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}
