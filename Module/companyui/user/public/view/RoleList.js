class RoleList extends Component {

    constructor(props) {
        super(props);

        this.state = {
            roles: [],
            rolesPublic: [],
            ds: [
                { name: 'row1', id: 1 },
                { name: 'row2', id: 2 }
            ]
        };

        this.roleModel = new RoleModel;
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.active', 'roles');

        this.getRoles();
    }

    editRole(role) {
        RoleEdit.open(role).then((resp) => {
            if (resp.status) {
                this.getRoles();
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });
    }

    getRoles() {
        return new Promise((done) => {
            this.roleModel.getRoles().then((roles) => {
                this.setState({ 'roles': roles.rsPrivate, 'rolesPublic': roles.rsPublic }, () => {
                    done(roles);
                });
            });
        });
    }

    deleteRole(role) {
        Confirm.open(Lang.t('role.confirm.delete') + role.name + '?').then((resp) => {
            if (resp) {
                this.roleModel.deleteRole(role.id).then((res) => {
                    if (res.status) {
                        // Thông báo thành công
                        this.getRoles();
                    } else {
                        Alert.open(Lang.t('update.error'));
                    }
                });
            }
        });
    }

    renderViewRolePublic() {
        if (this.state.rolesPublic) {
            return (
                <div>
                    <PageHeader>{Lang.t('roleEdit.roleDefault')}</PageHeader>
                    <div className="card-body">
                        <Datagrid rowKey={(row) => { return row.id }} dataset={this.state.rolesPublic} >
                            <Datagrid.Col
                                id="actions" thStyle={{ 'minWidth': '50px' }}
                                render={(role) => {
                                    return (
                                        <div className="dropdown">
                                            <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i className="ti ti-menu"></i>
                                            </a>
                                            <div className="dropdown-menu">
                                                <button className="dropdown-item" type="button" onClick={() => { this.editRole(role); }}>{Lang.t('role.btnEdit')}</button>
                                                <button className="dropdown-item" type="button" onClick={() => { this.deleteRole(role); }}>{Lang.t('role.btnDelete')}</button>
                                            </div>
                                        </div>
                                    );
                                }
                                }
                            />
                            <Datagrid.Col
                                id="id" thead="ID" thStyle={{ 'minWidth': '300px' }}
                                render={(role) => {
                                    return (<a href="javascript:;" onClick={() => { this.editRole(role); }}>{role.id}</a>);
                                }}
                            />
                            <Datagrid.Col
                                id="name" thead={Lang.t('role.name')} thStyle={{ 'width': '100%' }}
                                render={(role) => {
                                    return (<a href="javascript:;" onClick={() => { this.editRole(role); }}>{role.name}</a>);
                                }}
                            />
                        </Datagrid>
                    </div>
                </div>
            );
        }
    }


    render() {
        return (
            <AdminLayout>
                <div className="role-list-page">
                    <div className="card">
                        <div className="card-body">
                            <button className="btn btn-primary" onClick={() => { this.editRole(); }}>
                                <i className="ti-plus"></i>&nbsp;
                                {Lang.t('role.btnNew')}
                            </button>
                            <Datagrid rowKey={(row) => { return row.id }} dataset={this.state.roles} >
                                <Datagrid.Col
                                    id="actions" thStyle={{ 'minWidth': '50px' }}
                                    render={(role) => {
                                        return (
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className="dropdown-item" type="button" onClick={() => { this.editRole(role); }}>{Lang.t('role.btnEdit')}</button>
                                                    <button className="dropdown-item" type="button" onClick={() => { this.deleteRole(role); }}>{Lang.t('role.btnDelete')}</button>
                                                </div>
                                            </div>
                                        );
                                    }}
                                />
                                <Datagrid.Col
                                    id="id" thead="ID" thStyle={{ 'minWidth': '300px' }}
                                    render={(role) => {
                                        return (<a href="javascript:;" onClick={() => { this.editRole(role); }}>{role.id}</a>);
                                    }}
                                />
                                <Datagrid.Col
                                    id="name" thead={Lang.t('role.name')} thStyle={{ 'width': '100%' }}
                                    render={(role) => {
                                        return (<a href="javascript:;" onClick={() => { this.editRole(role); }}>{role.name}</a>);
                                    }}
                                />
                            </Datagrid>
                        </div>
                    </div>
                    <div className="card">
                        <div className="card-body">
                            {this.renderViewRolePublic()}
                        </div>
                    </div>
                </div>


            </AdminLayout>
        );
    }
}