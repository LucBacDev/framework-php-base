class SiteList extends PureComponent {
    constructor(props) {
        super(props);
        this.siteModel = new SiteModel;
        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            'sites': [],
            'IDsiteSelected': '',
            'filter': {
                'nameSearch': '',
                'pageSize': 50,
                'pageNo': 1,
                'tags': []
            },
            'pageCount': 1,
            'recordCount': 0
        };
    }

    isModal() {
        return this.state && this.state.isModal;
    }

    handleModalOptions(opts) {
        //default
        return $.extend({
            'multiple': false,
            'type': 'department'
        }, opts);
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

    componentDidMount() {
        App.requireLogin();
        this.getSites();
    }

    componentWillMount() {
        if(!this.isModal()){
            App.Component.trigger('leftNav.active', 'sites');
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    onPrintEditorReady(editor) {
        this.editor = editor;
    }

    editSite(site) {
        SiteEdit.open(site).then((resp) => {
            if (resp.status) {
                this.getSites();
            } else {
                Alert.open(Lang.t('update.error'));
            }
        });
    }

    toggleAllCheck(checked) {
        // this.state.deps.map((dep) => {
        //     dep.checked = checked;
        //     return dep;
        // });
        this.setState({});

    }

    getSites() {
        return new Promise((done) => {
            var filter = {
                'name': this.state.filter.nameSearch,
                'pageSize': this.state.filter.pageSize,
                'pageNo': this.state.filter.pageNo,
                'tags': this.state.filter.tags
            };

            this.siteModel.getSite(filter).then((resp) => {
                // if (!resp.rows.length || !resp.rows[0].id) {
                //     resp.rows = [];
                // }

                // không hiện site
                if (this.state.modalOptions.notSites) {
                    for (var i = 0; i < resp.rows.length; i++) {
                        if ($.inArray(resp.rows[i].id, this.state.modalOptions.notSites) != -1) {
                            resp.rows.splice(i, 1);
                            i = -1;
                        }
                    }
                }

                this.setState({ 'sites': resp.rows}, () => {
                    // dựa vào tổng các item và số lượng các item được show để hiện số lượng trang tương ứng
                    this.setPureState({ pageCount: resp.pageCount, recordCount: resp.recordCount });
                    done();
                });
            });
        });
    }

    deleteSite(site) {
        // App.confirm('Xác nhận xóa site <b>' + site.name + '</b>?').then(() => {
        //     this.siteModel.deleteSite(site.id).then(() => {

        //     });
        // });
        this.siteModel.deleteSite(site.id).then(() => {
            this.getSites();
        });
    }
    restoreSite(site) {
        site.active = 1;
        site.willDeleteAt = '';
        this.siteModel.updateSite(site.id, site).then(() => {
            this.getSites();
        });
    }

    showOption(site) {
        var option;
        if (site.active) {
            option = <button className="dropdown-item" type="button" onClick={() => { this.deleteSite(site) }}>{Lang.t('site.btnDelete')}</button>
        } else {
            option = <button className="dropdown-item" type="button" onClick={() => { this.restoreSite(site) }}>{Lang.t('site.btnRestore')}</button>
        }
        return option;
    }

    handleChangeFilter() {
        this.setPureState({ filter: this.state.filter });
        this.getSites()
    }

    handleCheckSite(checked, site) {
        if (checked && this.isModal()) {
            this.state.sites.map((currSite, idx) => {
                if (site.id == currSite.id) {
                    this.state.sites[idx].checked = checked;
                    this.state.IDsiteSelected = site.id;
                }
            });
        } else {
            site.checked = checked;
            this.state.IDsiteSelected = '';
        }
        this.setPureState({ 'sites': this.state.sites, 'IDsiteSelected': this.state.IDsiteSelected });
    }

    handleChangeTag(values) {
        if(this.state.filter.tags.toString() == values.toString())
            return
        this.state.filter.tags = values;
        this.getSites();
    }


    setDefaultState() {
        // set default IDsiteSelected
        this.setPureState({
            'IDsiteSelected': ''
        });
    }

    getSelectedSites(getObject) {
        getObject = getObject || false; //lấy cả object thay vì lấy id
        var selected = [];
        this.state.sites.map((site) => {
            if (site.checked)
                selected.push(getObject ? $.extend({}, site) : site.id);
        });
        return selected;
    }

    disabledCheckboxSite(site) {
        var status = false;
        // disable khi là chọn một
        if (!this.state.modalOptions.multiple && this.isModal()) {
            if (this.state.IDsiteSelected && this.state.IDsiteSelected != site.id) {
                status = true;
            }
        }
        return status;
    }

    pageContent() {
        return (
            <div>
                {!this.isModal() && <PageHeader>{Lang.t('site.header')}</PageHeader>}
                <div className="card">
                    <div className="card-body">
                        <div>
                            {!this.isModal() &&
                                <div className="left">
                                    <button type="button" className="btn btn-primary" onClick={() => { this.editSite() }}>{Lang.t('site.btnNew')}</button>
                                </div>
                            }
                            <div className="filter-tags">
                                <SiteTagSelect
                                    onChange={(values) => { this.handleChangeTag(values); }}
                                />
                            </div>

                            <div className="input-group right div-search-site" style={{ maxWidth: '300px' }}>
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control" placeholder={Lang.t('site.placeSearch')} onChange={(ev) => { this.state.filter.nameSearch = ev.target.value; this.handleChangeFilter(); }} />

                            </div>

                        </div>

                        <h4></h4>
                        <table className="table table-striped table-hover" ref={(elm) => { this.table = elm; }}>
                            <thead>
                                <tr>
                                    <th style={{ 'minWidth': '30px' }}>
                                        {(!this.isModal() || this.state.modalOptions.multiple) &&
                                            <CheckBox onChange={(checked) => { this.toggleAllCheck(checked); }} />
                                        }

                                    </th>
                                    {!this.isModal() && <th style={{ 'minWidth': '50px' }}>&nbsp;</th>}
                                    {!this.isModal() && <th style={{ 'minWidth': '200px' }}>ID</th>}
                                    <th style={{ 'width': '100%' }}>{Lang.t('site.name')}</th>
                                    <th style={{ 'minWidth': '300px' }}>{Lang.t('site.tags')}</th>
                                    <th style={{ 'minWidth': '150px' }}>{Lang.t('site.status')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {this.state.sites.map((site) =>
                                    <tr key={site.id}>
                                        <td>
                                            <CheckBox className="chkUserItem"
                                                checked={site.checked ? true : false}
                                                onChange={(checked) => { this.handleCheckSite(checked, site); }}
                                                disabled={this.disabledCheckboxSite(site)} />
                                        </td>
                                        {/* <i className="ti ti-user"></i> */}
                                        {!this.isModal() && <td>
                                            <div className="dropdown">
                                                <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i className="ti ti-menu"></i>
                                                </a>
                                                <div className="dropdown-menu">
                                                    <button className={'dropdown-item ' + (site.active == 1 ? '' : 'hidden')} type="button"
                                                        onClick={() => { this.editSite(site); }} >{Lang.t('site.btnEdit')}</button>
                                                    {this.showOption(site)}
                                                </div>
                                            </div>
                                        </td>}
                                        {!this.isModal() && <td>{site.id}</td>}
                                        <td><a href="javascript:;" onClick={() => { if (site.active && !this.isModal()) this.editSite(site); }}>{site.name}</a></td>
                                        <td>{site.tags.join(', ')}</td>
                                        <td>
                                            {site.active == 1
                                                ? <span className="badge  badge-success">{Lang.t('site.sttAction')}</span>
                                                : <span className="badge  badge-default"> {Lang.t('site.sttDelete') + ' ' + site.willDeleteAt}</span>}
                                        </td>
                                    </tr>
                                )}

                            </tbody>
                        </table>

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