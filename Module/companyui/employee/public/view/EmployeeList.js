class EmployeeList extends PureComponent {
    constructor(props) {
        super(props);
        this.empModel = new EmployeeModel;
        this.state = {
            'employees': [],
            'filter': {
                'fullname': '',
                'code': '',
                'depFK': '',
                'active': '',
                'pageNo': 1,
                'pageSize': 20,
                'loadDep': 1
            },
            'total': 0,
            'pageCount': 1
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getEmployees();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'nhansu');
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    getEmployees() {
        this.empModel.getEmployees(this.state.filter).then((resp) => {
            this.setState({
                'employees': resp.data || [],
                'total': resp.total || 0,
                'pageCount': Math.ceil((resp.total || 0) / this.state.filter.pageSize)
            });
        });
    }

    editEmployee(emp) {
        EmployeeEdit.open(emp).then((resp) => {
            if (resp.status) {
                this.getEmployees();
            } else {
                Alert.open(Lang.t('emp.error.update'));
            }
        });
    }

    deleteEmployee(emp) {
        Confirm.open(Lang.t('emp.confirm.delete') + ' ' + emp.fullname + '?').then((resp) => {
            if (resp) {
                this.empModel.deleteEmployee(emp.id).then((res) => {
                    if (res.status) {
                        this.getEmployees();
                    } else {
                        Alert.open(Lang.t('emp.error.delete'));
                    }
                }).catch((xhr) => {
                    Alert.open(xhr.responseJSON ? xhr.responseJSON.message : Lang.t('emp.error.delete'));
                });
            }
        });
    }

    toggleActive(emp) {
        this.empModel.toggleActive(emp.id).then(() => {
            this.getEmployees();
        }).catch((xhr) => {
            Alert.open(xhr.responseJSON ? xhr.responseJSON.message : Lang.t('emp.error.update'));
        });
    }

    handleSearch() {
        this.state.filter.pageNo = 1;
        this.getEmployees();
    }

    handlePageChange(pageNo) {
        this.state.filter.pageNo = pageNo;
        this.setPureState({filter: this.state.filter});
        this.getEmployees();
    }

    renderStatus(emp) {
        if (emp.active == 1) {
            return <span className="badge badge-success">{Lang.t('emp.status.active')}</span>;
        }
        return <span className="badge badge-secondary">{Lang.t('emp.status.inactive')}</span>;
    }

    renderPagination() {
        if (this.state.pageCount <= 1) return null;
        var pages = [];
        for (var i = 1; i <= this.state.pageCount; i++) {
            pages.push(i);
        }
        return (
            <nav>
                <ul className="pagination pagination-sm justify-content-center">
                    {pages.map((page) =>
                        <li key={page} className={'page-item ' + (page === this.state.filter.pageNo ? 'active' : '')}>
                            <a className="page-link" href="javascript:;" onClick={() => { this.handlePageChange(page); }}>{page}</a>
                        </li>
                    )}
                </ul>
            </nav>
        );
    }

    render() {
        return (
            <AdminLayout>
                <PageHeader>{Lang.t('emp.header')}</PageHeader>
                <div className="card">
                    <div className="card-body">
                        <div>
                            <div className="left">
                                <button type="button" className="btn btn-primary" onClick={() => { this.editEmployee(); }}>
                                    {Lang.t('emp.btnNew')}
                                </button>
                            </div>
                            <div className="input-group right" style={{maxWidth: '300px'}}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control"
                                    placeholder={Lang.t('emp.placeSearch')}
                                    onChange={(ev) => { this.state.filter.fullname = ev.target.value; this.handleSearch(); }}
                                />
                            </div>
                        </div>

                        <h4></h4>
                        <div className="text-muted mb-2">
                            {Lang.t('emp.total')}: {this.state.total}
                        </div>
                        <table className="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style={{minWidth: '50px'}}>&nbsp;</th>
                                    <th style={{width: '25%'}}>{Lang.t('emp.col.fullname')}</th>
                                    <th style={{width: '10%'}}>{Lang.t('emp.col.code')}</th>
                                    <th style={{width: '15%'}}>{Lang.t('emp.col.email')}</th>
                                    <th style={{width: '10%'}}>{Lang.t('emp.col.phone')}</th>
                                    <th style={{width: '15%'}}>{Lang.t('emp.col.position')}</th>
                                    <th style={{width: '15%'}}>{Lang.t('emp.col.department')}</th>
                                    <th style={{minWidth: '120px'}}>{Lang.t('emp.col.status')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {this.state.employees.map((emp) =>
                                    <tr key={emp.id}>
                                        <td>
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.editEmployee(emp); }}>
                                                        {Lang.t('emp.btnEdit')}
                                                    </button>
                                                    <button className="dropdown-item" type="button"
                                                        onClick={() => { this.toggleActive(emp); }}>
                                                        {emp.active == 1 ? Lang.t('emp.btnDeactivate') : Lang.t('emp.btnActivate')}
                                                    </button>
                                                    {emp.active != 1 && !emp.noDelete &&
                                                        <button className="dropdown-item text-danger" type="button"
                                                            onClick={() => { this.deleteEmployee(emp); }}>
                                                            {Lang.t('emp.btnDelete')}
                                                        </button>
                                                    }
                                                </div>
                                            </div>
                                        </td>
                                        <td>{emp.fullname}</td>
                                        <td>{emp.code}</td>
                                        <td>{emp.email}</td>
                                        <td>{emp.phone}</td>
                                        <td>{emp.position}</td>
                                        <td>{emp.department ? emp.department.name : ''}</td>
                                        <td>{this.renderStatus(emp)}</td>
                                    </tr>
                                )}
                                {this.state.employees.length === 0 &&
                                    <tr>
                                        <td colSpan="8" className="text-center text-muted">{Lang.t('emp.noData')}</td>
                                    </tr>
                                }
                            </tbody>
                        </table>

                        {this.renderPagination()}
                    </div>
                </div>
                <EmployeeEdit />
            </AdminLayout>
        );
    }
}
