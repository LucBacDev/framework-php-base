class DepartmentList extends PureComponent {
    constructor(props) {
        super(props);
        this.depModel = new DepartmentModel;
        this.state = {
            'departments': [],
            'filter': {
                'name': '',
                'parentID': ''
            },
            'currentParent': null,
            'breadcrumb': []
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getDepartments();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'phongban');
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    getDepartments() {
        var filter = {
            'name': this.state.filter.name,
            'parentID': this.state.currentParent ? this.state.currentParent.id : ''
        };
        this.depModel.getDepartments(filter).then((deps) => {
            this.setState({'departments': deps});
        });
    }

    editDepartment(dep) {
        DepartmentEdit.open(dep).then((resp) => {
            if (resp.status) {
                this.getDepartments();
            } else {
                Alert.open(Lang.t('dep.error.update'));
            }
        });
    }

    deleteDepartment(dep) {
        Confirm.open(Lang.t('dep.confirm.delete') + ' <b>' + dep.name + '</b>?').then((resp) => {
            if (resp) {
                this.depModel.deleteDepartment(dep.id).then((res) => {
                    if (res.status) {
                        this.getDepartments();
                    } else {
                        Alert.open(Lang.t('dep.error.delete'));
                    }
                }).catch((xhr) => {
                    Alert.open(xhr.responseJSON ? xhr.responseJSON.message : Lang.t('dep.error.delete'));
                });
            }
        });
    }

    toggleActive(dep) {
        this.depModel.toggleActive(dep.id).then(() => {
            this.getDepartments();
        });
    }

    navigateToChild(dep) {
        var breadcrumb = this.state.breadcrumb.slice();
        breadcrumb.push(dep);
        this.setState({
            'currentParent': dep,
            'breadcrumb': breadcrumb
        }, () => {
            this.getDepartments();
        });
    }

    navigateToBreadcrumb(index) {
        if (index < 0) {
            // về gốc
            this.setState({
                'currentParent': null,
                'breadcrumb': []
            }, () => {
                this.getDepartments();
            });
        } else {
            var breadcrumb = this.state.breadcrumb.slice(0, index + 1);
            this.setState({
                'currentParent': breadcrumb[breadcrumb.length - 1],
                'breadcrumb': breadcrumb
            }, () => {
                this.getDepartments();
            });
        }
    }

    handleSearch() {
        this.getDepartments();
    }

    renderStatus(dep) {
        if (dep.active) {
            return <span className="badge badge-success">{Lang.t('dep.status.active')}</span>;
        }
        return <span className="badge badge-secondary">{Lang.t('dep.status.inactive')}</span>;
    }

    render() {
        return (
            <AdminLayout>
                <PageHeader>{Lang.t('dep.header')}</PageHeader>
                <div className="card">
                    <div className="card-body">
                        <div>
                            <div className="left">
                                <button type="button" className="btn btn-primary" onClick={() => { this.editDepartment(); }}>
                                    {Lang.t('dep.btnNew')}
                                </button>
                            </div>
                            <div className="input-group right" style={{maxWidth: '300px'}}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control"
                                    placeholder={Lang.t('dep.placeSearch')}
                                    onChange={(ev) => { this.state.filter.name = ev.target.value; this.handleSearch(); }}
                                />
                            </div>
                        </div>

                        {/* Breadcrumb điều hướng cây */}
                        <nav aria-label="breadcrumb" className="mt-3">
                            <ol className="breadcrumb">
                                <li className={'breadcrumb-item ' + (!this.state.currentParent ? 'active' : '')}>
                                    <a href="javascript:;" onClick={() => { this.navigateToBreadcrumb(-1); }}>
                                        {Lang.t('dep.root')}
                                    </a>
                                </li>
                                {this.state.breadcrumb.map((item, idx) =>
                                    <li key={item.id} className={'breadcrumb-item ' + (idx === this.state.breadcrumb.length - 1 ? 'active' : '')}>
                                        <a href="javascript:;" onClick={() => { this.navigateToBreadcrumb(idx); }}>
                                            {item.name}
                                        </a>
                                    </li>
                                )}
                            </ol>
                        </nav>

                        <h4></h4>
                        <table className="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style={{minWidth: '50px'}}>&nbsp;</th>
                                    <th style={{width: '30%'}}>{Lang.t('dep.col.name')}</th>
                                    <th style={{width: '20%'}}>{Lang.t('dep.col.code')}</th>
                                    <th style={{width: '30%'}}>{Lang.t('dep.col.parent')}</th>
                                    <th style={{minWidth: '120px'}}>{Lang.t('dep.col.status')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {this.state.departments.map((dep) =>
                                    <tr key={dep.id}>
                                        <td>
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.editDepartment(dep); }}>
                                                        {Lang.t('dep.btnEdit')}
                                                    </button>
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.navigateToChild(dep); }}>
                                                        {Lang.t('dep.btnViewChild')}
                                                    </button>
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.toggleActive(dep); }}>
                                                        {dep.active ? Lang.t('dep.btnDeactivate') : Lang.t('dep.btnActivate')}
                                                    </button>
                                                    {!dep.active && !dep.noDelete &&
                                                        <button className="dropdown-item text-danger" type="button"
                                                            onClick={() => { this.deleteDepartment(dep); }}>
                                                            {Lang.t('dep.btnDelete')}
                                                        </button>
                                                    }
                                                </div>
                                            </div>
                                        </td>
                                        <td>{dep.name}</td>
                                        <td>{dep.code}</td>
                                        <td>
                                            {dep.ancestors && dep.ancestors.length > 0
                                                ? dep.ancestors[dep.ancestors.length - 1].name
                                                : Lang.t('dep.root')
                                            }
                                        </td>
                                        <td>{this.renderStatus(dep)}</td>
                                    </tr>
                                )}
                                {this.state.departments.length === 0 &&
                                    <tr>
                                        <td colSpan="5" className="text-center text-muted">{Lang.t('dep.noData')}</td>
                                    </tr>
                                }
                            </tbody>
                        </table>
                    </div>
                </div>
                <DepartmentEdit />
            </AdminLayout>
        );
    }
}
