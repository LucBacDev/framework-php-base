class SystemSite extends PureComponent {

    constructor(props) {
        super(props);

        this.state = {
            'sites': [],
            'filter': {
                'search': '',
                'pageSize': 50,
                'pageNo': 1
            },
            'pageCount': 1,
            'recordCount': 0,
            'render': false
        };
        this.siteModel = new SiteModel;
        this.bindThis([
            'handleChooseSite'
        ]);
    }

    componentDidMount() {
        App.requireLogin();
        this.getUserSites();
        Lang.load('companyui', 'site').then(() => {
            this.setState({'render': true});
        });
    }

    getUserSites() {
        return new Promise((done) => {
            var filter = {
                'name': this.state.filter.search,
                'pageSize': this.state.filter.pageSize,
                'pageNo': this.state.filter.pageNo,
            };

            this.siteModel.getUserSites(App.user.id, filter).then((resp) => {
                // if (!resp.rows.length || !resp.rows[0].id) {
                //     resp.rows = [];
                // }
                this.setState({ 'sites': resp.rows }, () => {
                    // dựa vào tổng các item và số lượng các item được show để hiện số lượng trang tương ứng
                    this.setPureState({ pageCount: resp.pageCount, recordCount: resp.recordCount });
                    done();
                });
            });
        });
    }

    handleChangeFilter() {
        this.setPureState({ filter: this.state.filter });
        this.getUserSites();
    }

    handleChooseSite(site) {
        // set site active 
        localStorage.setItem('site', site.id);
        window.location.href = App.url('/:siteID/nhansu', { siteID: site.id });
    }

    render() {
        return this.state.render ?
             (
                <AdminLayout>
                    <div className="role-list-page">
                        <div className="card search-systemSite">
                            <div>
                                <div className="input-group left" style={{ maxWidth: '300px' }}>
                                    <div className="input-group-prepend">
                                        <span className="input-group-text"><i className="ti-search"></i></span>
                                    </div>
                                    <input type="text" className="form-control" placeholder={Lang.t('site.placeSearch')} onChange={(ev) => { this.state.filter.search = ev.target.value; this.handleChangeFilter(); }} />

                                </div>
                            </div>
                        </div>
                        <h5 className="card-title">{Lang.t('site.list')}</h5>
                        <div className="row">
                            {this.state.sites.map((site, index) =>
                                <div className="col-sm-4" key={index}>
                                    <div className="card">
                                        <div className="card-body">
                                            <h5 className="card-title">{site.name}</h5>
                                            <p className="card-text">{site.name}</p>
                                            <button type="button" className="btn btn-primary" onClick={() => { this.handleChooseSite(site); }}>{Lang.t('site.chooseSite')}</button>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                        <div>
                            <Pagination
                                onChange={(pageSize, pageNo) => {
                                    this.state.filter.pageSize = pageSize; this.state.filter.pageNo = pageNo;
                                    this.handleChangeFilter()
                                }}
                                pageCount={this.state.pageCount}
                                recordCount={this.state.recordCount}
                            />
                        </div>
                    </div>


                </AdminLayout>
            )
        : null;
    }
}