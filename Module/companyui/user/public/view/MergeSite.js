class MergeSite extends PureComponent {

    constructor(props) {
        super(props);

        this.state = {
            'sites': [],
            'form': {
                'account': '',
                'password': '',
                'mainAccount': App.getUser().login.localdb.account,
                'accountOpts': [App.getUser().login.localdb.account]
            },
        };
        this.siteModel = new SiteModel;
    }

    componentDidMount() {
        App.requireLogin();
        this.getDataSitePresent();
    }

    // lấy danh sách các site được truy cập
    getDataSitePresent() {
        return new Promise((done) => {
            var filter = {};
            this.siteModel.getUserSites(App.user.id, filter).then((resp) => {
                if (!resp.rows.length || !resp.rows[0].id) {
                    resp.rows = [];
                }
                this.setState({ 'sites': resp.rows }, () => {
                    done();
                });
            });
        });
    }

    handleChangeForm() {
        this.setPureState({ form: this.state.form });
    }

    // set lại opts khi chọn tài khoản login chính
    handleChangeAccountOpts(ev) {
        if (ev.target.value) {
            this.state.form.accountOpts = [App.getUser().login.localdb.account, ev.target.value];
        } else {
            this.state.form.accountOpts = [App.getUser().login.localdb.account];
        }
    }

    renderSites() {
        if (App.getUser().login.localdb.account != 'admin') {
            return this.state.sites.map((site, idx) => {
                if (idx < 5) {
                    return (<p style={{ color: 'black' }} key={idx}>{site.name}</p>);
                }
            });
        } else {
            return (<p style={{ color: 'black' }}>{Lang.t('mergeSite.siteAllow')}</p>);
        }
    }

    handleSubmitMerge(ev) {
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        // Ghép tài khoản
        Confirm.open('Ghép tài khoản?').then((resp) => {
            if (resp) {
                this.siteModel.updateMergeSite(data).then((resp) => {
                    if (resp.status) {
                        //thông báo kết nối thành công
                        Alert.open(Lang.t('mergeSite.notifi.success')).then(() => {
                            window.location = App.siteUrl + '/auth/login';
                        });
                    } else {
                        //thông báo kết nối lỗi
                        Alert.open(resp.data);
                    }
                }).catch((xhr) => {
                    if (this.editFail)
                        this.editFail(xhr);
                });
            }
        });
    }

    render() {

        return (
            <AdminLayout>
                <PageHeader><i>{Lang.t('mergeSite.header')}</i></PageHeader>
                <div className="role-list-page">
                    <PageHeader>{Lang.t('mergeSite.accountPresent')}</PageHeader>
                    {/* <h5 className="card-title">Tài khoản hiện tại</h5> */}
                    <div className="row col-sm-6">
                        <table className="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>{Lang.t('mergeSite.accountName')}</th>
                                    <td><p style={{ color: 'black' }}>{App.getUser().login.localdb.account}</p></td>
                                </tr>
                                <tr>
                                    <th>{Lang.t('mergeSite.allowSites')}</th>
                                    <td>
                                        {this.renderSites()}
                                    </td>
                                </tr>
                                <tr>
                                    <th>{Lang.t('mergeSite.totalAllowSites')}</th>
                                    <td>
                                        {this.state.sites.length}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <PageHeader />
                    <PageHeader>{Lang.t('mergeSite.accountConnect')}</PageHeader>
                    {/* <h5 className="card-title">Kết nối đến tài khoản khác</h5> */}
                    <form onSubmit={(ev) => { this.handleSubmitMerge(ev); }} ref={(elm) => { this.form = elm; }} noValidate>
                        <div className="form-group row col-sm-6">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-login">{Lang.t('mergeSite.account')} <Require /></label>
                            <div className="col-sm-5">
                                <input type="text" className="form-control" id="txt-login"
                                    value={this.state.form.account}
                                    onChange={(ev) => {
                                        this.state.form.account = ev.target.value;
                                        this.handleChangeAccountOpts(ev);
                                        this.handleChangeForm()
                                    }}
                                    required='required'
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('mergeSite.validateForm')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row col-sm-6">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-password">{Lang.t('mergeSite.password')} <Require /></label>
                            <div className="col-sm-5">
                                <input type="password" className="form-control" id="txt-password"
                                    value={this.state.form.password}
                                    onChange={(ev) => { this.state.form.password = ev.target.value; this.handleChangeForm() }}
                                    required='required'
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('mergeSite.validateForm')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row col-sm-6">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-password">{Lang.t('mergeSite.accountSelect')}</label>
                            <div className="col-sm-5">
                                <select className="form-control" id="exampleSelect1"
                                    onChange={(ev) => { this.state.form.mainAccount = ev.target.value; this.handleChangeForm(); }}
                                    value={this.state.form.mainAccount}
                                >
                                    {this.state.form.accountOpts.map((account, idx) =>
                                        <option key={idx} value={account}>{account}</option>
                                    )}
                                </select>
                                <br />
                                <div className="invalid">
                                    <i>{Lang.t('mergeSite.note')}</i>
                                </div>
                                <br />
                                <button type="submit" className="btn btn-primary right">{Lang.t('mergeSite.btnMerge')}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </AdminLayout>
        );
    }
}