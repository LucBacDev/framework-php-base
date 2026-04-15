class UserList extends PureComponent {
    constructor(props) {
        super(props);
        this.depModel = new DepModel;
        this.userModel = new UserModel;
        this.state = {
            'currentDep': this.rootDep(),
            'deps': [],
            'users': [],
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false
        };
    }

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'users');
        }
    }

    componentDidMount() {
        if (this.isModal() == false) {
            this.handleHashChange();
            App.requireLogin();

        } else {
            this.setCurrentDep(this.rootDep());
        }
    }

    componentWillReceiveProps(nexProps) {
        if (!nexProps)
            return;
        if ('modal' in nexProps) {
            this.setState({ 'modalOptions': this.handleModalOptions(nexProps.modal) });
        } else {
            this.setState({ 'modalOptions': {} });
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    handleModalOptions(opts) {
        //default
        return $.extend({
            'multiple': false,
            'type': 'department'
        }, opts);
    }


    isModal() {
        return this.state && this.state.isModal;
    }

    setDepChecked(depID, checked) {
        checked = typeof checked != 'undefined' ? checked : true;
        this.state.deps.map((dep) => {
            if (dep.id == depID) {
                dep.checked = checked;
                this.setState({});
            }
        });
    }

    handleCheckDep(checked, dep) {
        //nếu không cho chọn nhiều
        if (checked && this.isModal() && this.state.modalOptions.multiple == false) {
            this.state.deps.map((currDep, idx) => {
                if (dep.id == currDep.id)
                    this.state.deps[idx].checked = true;
                else
                    this.state.deps[idx].checked = false;
            });
        } else {
            dep.checked = checked;
        }
        this.setPureState({ 'deps': this.state.deps });
    }

    handleCheckUser(checked, user) {
        if (checked && this.isModal()) {
            this.state.users.map((currUser, idx) => {
                if (user.id == currUser.id)
                    this.state.users[idx].checked = checked;
            });
        } else {
            user.checked = checked;
        }
        this.setPureState({ 'users': this.state.users });
    }

    deleteUser(user) {
        Confirm.open('Xác nhận xóa tài khoản ' + user.fullname + '?').then((resp) => {
            if (resp) {
                this.userModel.deleteUser(user.id).then((res) => {
                    if (res.status) {
                        // Thông báo thành công
                        this.getUsers();
                    } else {
                        Alert.open(Lang.t('update.error'));
                    }
                });
            }
        });
    }

    getSelectedDeps(getObject) {
        getObject = getObject || false; //lấy cả object thay vì lấy id
        var selected = [];
        this.state.deps.map((dep) => {
            if (dep.checked)
                selected.push(getObject ? $.extend({}, dep) : dep.id);
        });
        return selected;
    }

    getSelectedUsers(getObject) {
        getObject = getObject || false; //lấy cả object thay vì lấy id
        var selected = [];
        this.state.users.map((user) => {
            if (user.checked)
                selected.push(getObject ? $.extend({}, user) : user.id);
        });
        return selected;
    }


    handleHashChange() {
        var depID = window.location.hash.replace('#', '');
        if (depID === '0' || !depID) {
            this.setCurrentDep(this.rootDep());
        } else {
            this.depModel.getDep(depID, { 'loadAncestors': 1 })
                .then((dep) => {
                    this.setCurrentDep(dep ? dep : this.rootDep());
                });
        }
    }

    rootDep() {
        return { id: 0, 'name': Lang.t('RootDirectory'), 'ancestors': [] };
    }

    /**
     * Khi thay đổi thư mục
     * @param {*} dep 
     */
    setCurrentDep(dep) {
        var that = this;
        return new Promise((done) => {
            dep = dep || this.rootDep();
            if (this.isModal() == false)
                window.location.hash = dep.id;
            if (dep.id) {
                this.depModel.getDep(dep.id, { 'loadAncestors': 1 })
                    .then((dep) => {
                        onCurrentDepLoaded(dep);
                    });
            } else {
                onCurrentDepLoaded(dep);
            }

            function onCurrentDepLoaded(dep) {
                that.setState({ 'currentDep': dep }, () => {
                    var countDone = 0;
                    that.getDeps().then(testDone);
                    that.getUsers().then(testDone);

                    function testDone() {
                        countDone++;
                        if (countDone >= 2)
                            done();
                    }
                });
            }
        });
    }

    getDeps() {
        return new Promise((done) => {
            var filter = {
                'parentID': !this.state.currentDep.id ? 0 : this.state.currentDep.id,
                'loadAncestors': 1
            };

            if (this.isModal() && this.state.modalOptions.not)
                filter.not = this.state.modalOptions.not;

            this.depModel.getDeps(filter).then((resp) => {
                if (!resp.length || !resp[0].id) {
                    resp = [];
                }
                this.setState({ 'deps': resp }, () => {
                    done();
                });
            });
        });
    }

    getUsers(fullname = null) {
        return new Promise((done) => {
            var filter = {
                'parentID': !this.state.currentDep.id ? 0 : this.state.currentDep.id
            };
            if (fullname) {
                filter.fullname = fullname;
            }

            this.userModel.getUsers(filter).then((users) => {
                if (!users.length || !users[0].id) {
                    users = [];
                }

                // không hiện user
                if (this.state.modalOptions.notUsers) {
                    for (var i = 0; i < users.length; i++) {
                        if ($.inArray(users[i].id, this.state.modalOptions.notUsers) != -1) {
                            users.splice(i, 1);
                            i = -1;
                        }
                    }
                }

                this.setState({ 'users': users }, () => {
                    done();
                });
            });
        });
    }

    editUser(user) {
        UserEdit.open(user).then((resp) => {
            if (resp.status) {
                this.getUsers();
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });;
    }

    editDep(department) {
        //khi thêm mới, mặc định chọn đơn vị hiện tại
        var department = $.extend({
            'parentID': this.state.currentDep.id,
            'parentDep': this.state.currentDep
        }, department || {});
        DepEdit.open(department).then((resp) => {
            if (resp.status) {
                this.getDeps();
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });
    }
    handleChangeSearch(event) {
        this.getUsers(event.target.value);
    }

    deleteDep(dep) {
        Confirm.open(Lang.t('userList.confirm.deleteDep') +" "+ dep.name + '?').then((resp) => {
            if (resp) {
                this.depModel.deleteDep(dep.id).then((res) => {
                    if (res.status) {
                        // Thông báo thành công
                        this.getDeps();
                    } else {
                        Alert.open(Lang.t('update.error'));
                    }
                });
            }
        });
    }

    renderDepName(name){
        if(name == "[RootDirectory]"){
            return Lang.t('RootDirectory');
        }

        return name;
    }

    pageContent() {
        return (
            <div className="user-list-page">
                {!this.isModal() && <PageHeader>{Lang.t('user.header')}</PageHeader>}
                <div className="card card-nav">
                    <div className="card-body">
                        <nav className="breadcrumb breadcrumb-dash">
                            {this.state.currentDep
                                && this.state.currentDep.ancestors
                                && this.state.currentDep.ancestors.map((dep) =>
                                    <a key={dep.id} className="breadcrumb-item" href={'#' + dep.id}
                                        onClick={() => { this.setCurrentDep(dep) }}
                                    >
                                        {this.renderDepName(dep.name)}
                                    </a>
                                )}

                            <span className="breadcrumb-item active">{this.renderDepName(this.state.currentDep.name)}</span>
                        </nav>
                    </div>
                </div>

                <div className="card">
                    <div className="card-body">
                        <div className="btn-and-search">
                            {!this.isModal() &&
                                <div className="left">
                                    <button className="btn btn-primary" onClick={() => { this.editUser(); }}>
                                        <i className="ti-user"></i>&nbsp;
                                        {Lang.t('user.btnNewUser')}
                                    </button>
                                    <button className="btn btn-secondary" onClick={() => { this.editDep(); }}>
                                        <i className="ti-folder"></i>&nbsp;
                                        {Lang.t('user.btnNewDep')}
                                    </button>
                                </div>
                            }
                            <div className="right searchBox">
                                <div className="input-group">
                                    <div className="input-group-prepend">
                                        <span className="input-group-text"><i className="ti-search"></i></span>
                                    </div>
                                    <input type="text" className="form-control" placeholder={Lang.t('user.placeSearch')} onChange={(event) => { this.handleChangeSearch(event); }} />
                                </div>
                            </div>
                            <div className="clear"></div>
                        </div>
                        <h4></h4>

                        <table className="table table-striped table-hover" ref={(elm) => { this.table = elm; }}>
                            <thead>
                                <tr>
                                    <th style={{ 'minWidth': '30px' }}>

                                    </th>
                                    <th style={{ 'minWidth': '50px' }}>{Lang.t('user.category')}</th>
                                    {!this.isModal() && <th style={{ 'minWidth': '50px' }}>&nbsp;</th>}
                                    <th style={{ 'width': '100%' }}>{Lang.t('user.name')}</th>
                                    {!this.isModal() && <th style={{ 'minWidth': '300px' }}>ID</th>}
                                    <th style={{ 'minWidth': '150px' }}>{Lang.t('user.status')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {(!this.isModal() || this.state.modalOptions.type == 'department' || ($.inArray('department', this.state.modalOptions.type) != -1)) && this.state.deps.map((dep, idx) =>
                                    <tr key={dep.id}>
                                        <th>
                                            {($.inArray('department', this.state.modalOptions.type) == -1) &&
                                                <CheckBox className="chkUserDepItem"
                                                    checked={dep.checked ? true : false}
                                                    onChange={(checked) => { this.handleCheckDep(checked, dep); }} />
                                            }
                                        </th>
                                        <td><i className="ti ti-folder"></i></td>
                                        {!this.isModal() && <td>
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.editDep(dep); }}>{Lang.t('user.btnEdit')}</button>
                                                    <button className="dropdown-item" type="button">{Lang.t('user.btnForward')}</button>
                                                    <button className="dropdown-item" type="button" onClick={() => { this.deleteDep(dep); }}>{Lang.t('user.btnDelete')}</button>
                                                </div>
                                            </div>
                                        </td>}
                                        <td>
                                            <a href="javascript:;" onClick={() => { this.setCurrentDep(dep); }}>
                                                {dep.name}
                                            </a>
                                            {!this.isModal() &&
                                                <span>
                                                    &nbsp;&nbsp;&nbsp;
                                                <a className="open" href="javascript:;" title={Lang.t('userList.title.update')} onClick={() => { this.editDep(dep); }}>
                                                        <i className="ti-pencil"></i>
                                                    </a>
                                                </span>}

                                        </td>
                                        {!this.isModal() && <td>{dep.id}</td>}
                                        <td>
                                            {dep.active == 1
                                                ? <span className="badge  badge-success">{Lang.t('user.sttAction')}</span>
                                                : <span className="badge  badge-default">{Lang.t('user.sttDelete')}</span>}
                                        </td>
                                    </tr>
                                )}
                                {(!this.isModal() || this.state.modalOptions.type == 'user' || ($.inArray('user', this.state.modalOptions.type) != -1)) && this.state.users.map((user) =>
                                    <tr key={user.id}>
                                        <td>
                                            <CheckBox className="chkUserItem"
                                                checked={user.checked ? true : false}
                                                onChange={(checked) => { this.handleCheckUser(checked, user); }} />
                                        </td>
                                        <td>
                                            <i className="ti ti-user"></i>
                                        </td>
                                        {!this.isModal() && <td>
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.editUser(user); }}>{Lang.t('user.btnEdit')}</button>
                                                    <button className="dropdown-item" type="button">{Lang.t('user.btnForward')}</button>
                                                    <button className="dropdown-item" type="button" onClick={() => { this.deleteUser(user) }}>{Lang.t('user.btnDelete')}</button>
                                                </div>
                                            </div>
                                        </td>}
                                        <td>
                                            <a href="javascript:;" onClick={() => {
                                                if (($.inArray('user', this.state.modalOptions.type) == -1)) {
                                                    this.editUser(user);
                                                }
                                            }}>
                                                {user.fullname}
                                            </a>
                                        </td>
                                        {!this.isModal() && <td>{user.id}</td>}
                                        <td>
                                            {user.active == 1
                                                ? <span className="badge  badge-success">{Lang.t('user.sttAction')}</span>
                                                : <span className="badge  badge-default">{Lang.t('user.sttDelete')}</span>}
                                        </td>
                                    </tr>
                                )}

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        );
    }

    render() {
        if (this.isModal())
            return this.pageContent();
        else
            return (
                <AdminLayout>
                    {this.pageContent()}
                </AdminLayout>
            );
    }
}