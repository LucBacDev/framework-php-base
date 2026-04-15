Setting.List = class SettingList extends PureComponent {
    constructor(props) {
        super(props);
        this.model = new Setting.Model;
        this.state = {
            'filter': {
                'nameSearch': '',
                'pageSize': 50,
                'pageNo': 1
            },
            'settings': [],
            'pageCount': 1,
            'recordCount': 0,
            'configdetail': [],
            'settingID': ''
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getSetting();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'setting');

    }

    editSetting(setting) {
        Setting.Edit.open(setting);
    }

    getSetting() {
        return new Promise((done) => {
            var filter = {
                'name': this.state.filter.nameSearch,
                'pageSize': this.state.filter.pageSize,
                'pageNo': this.state.filter.pageNo,
            };

            this.model.getForms(filter).then((resp) => {
                if (!resp.rows.length || !resp.rows[0].id) {
                    resp.rows = [];
                }

                this.setState({
                    'settings': resp.rows,
                    'configdetail': resp.rows.length != 0 ? [resp.rows[0].id] : '',
                    'settingID': resp.rows.length != 0 ? resp.rows[0].id : '',
                }, () => {
                    // dựa vào tổng các item và số lượng các item được show để hiện số lượng trang tương ứng
                    this.setPureState({ pageCount: resp.pageCount, recordCount: resp.recordCount });
                    done();
                });
            });
        });
    }

    handleChangeFilter() {
        this.getSetting();
    }

    expandConfig(settingID) {
        var configdetail = [];
        configdetail.push(settingID);
        this.setPureState({
            'configdetail': configdetail,
            'settingID': settingID
        });
    }

    inArray(key, arr) {
        return jQuery.inArray(key, arr) !== -1 ? true : false;
    };

    render() {
        return <AdminLayout>
            <PageHeader>{Lang.t('setting.header')}</PageHeader>
            <div className="card">
                <div className="btn-and-search">
                    <div className="left searchBox setting-search">
                        <div className="input-group">
                            <div className="input-group-prepend">
                                <span className="input-group-text"><i className="ti-search"></i></span>
                            </div>
                            <input type="text" className="form-control" placeholder={Lang.t('setting.placeSearch')} onChange={(ev) => { this.state.filter.nameSearch = ev.target.value; this.handleChangeFilter(); }} />
                        </div>
                    </div>
                </div>
                <div className="card-body">
                    <div className="p-v-20 form-group row">
                        <div className="tab-vertical tab-base tab-stacked-left">
                            <ul className="nav nav-tabs" id="tabs-setting-print">
                                {this.state.settings.map((setting, idx) =>
                                    <li key={idx}>
                                        <a data-toggle="tab" href={'#setting-' + setting.id} aria-expanded="true"
                                            onClick={() => { this.expandConfig(setting.id); }}
                                            className={this.inArray(setting.id, this.state.configdetail) ? 'active show' : ''}>
                                            <span>
                                                {setting.name}
                                            </span>
                                        </a>
                                    </li>
                                )}
                            </ul>
                            <div className="tab-content">
                                {this.state.settingID &&
                                    <SettingEdit settingID={this.state.settingID} configdetail={this.state.configdetail} />
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    }

}