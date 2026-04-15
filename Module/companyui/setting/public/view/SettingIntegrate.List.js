SettingIntegrate.List = class SettingIntegrateList extends PureComponent {
    constructor(props) {
        super(props);
        this.model = new Setting.Model;

        this.state = {
            'form': this.newSettingIntegrate()
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getValueSettingIntegrate();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'setting-integrate');
    }

    newSettingIntegrate() {
        return [{
            'id': 'integrateType',
            'label': 'Loại tích hợp',
            'value': 'Json'
        },
        {
            'id': 'integrateMode',
            'label': 'Phương thức tích hợp',
            'value': {
                'mode': '',
                'account': '',
                'password': '',
                'token': ''
            }
        },
        {
            'id': 'urlGetListRadiologist',
            'label': 'URL lấy danh sách BSCĐ',
            'value': ''
        },
        {
            'id': 'urlGetListClinician',
            'label': 'URL lấy danh sách BSLS',
            'value': ''
        },
        {
            'id': 'urlNotiCaptured',
            'label': 'URL thông báo ca đã chụp',
            'value': ''
        },
        {
            'id': 'urlGetResultToHis',
            'label': 'URL lấy kết quả cho HIS',
            'value': ''
        },
        {
            'id': 'formatData',
            'label': 'Định dạng lại dữ liệu',
            'value': ''
        }
        ];
    }

    getValueSettingIntegrate() {
        return new Promise((done) => {
            this.model.getFieldsFormId('integrate').then((resp) => {
                if (!resp.length || !resp[0].id) {
                    resp = [];
                }
                // format again data
                var fieldIDs = ['integrateType', 'integrateMode', 'urlGetListRadiologist', 'urlGetListClinician', 'urlNotiCaptured', 'urlGetResultToHis', 'formatData'];
                var dataList = [];
                if (resp.length) {
                    for (var i in fieldIDs) {
                        for (var j in resp) {
                            // format json when id integrateMode
                            if (fieldIDs[i] == 'integrateMode' && fieldIDs[i] == resp[j].id) {
                                resp[j].value = JSON.parse(resp[j].value);
                                dataList.push(resp[j]);
                            }
                            else if (fieldIDs[i] == resp[j].id) {
                                dataList.push(resp[j]);
                            }
                        }
                    }
                    this.setPureState({ 'form': dataList });
                }
                done();
            });
        });
    }

    inArray(key, arr) {
        return jQuery.inArray(key, arr) !== -1 ? true : false;
    };

    // ghi lại
    handleSubmit(ev) {
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        // var form = $(this.form);
        // if (form[0].checkValidity() === false) {
        //     $(form).addClass('was-validated');
        //     return;
        // }
        //Xử lý ghi lại
        this.model.updateSettingIntegrate(data).then((resp) => {
            if (resp.status) {
                Alert.open(Lang.t('settingEdit.notifi.success'));
            }
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    changeValueIntergrateMode(ev) {
        var value = this.state.form[1].value;
        switch (ev.target.value) {
            case 'notVerified':
                value.mode = 'notVerified';
                value.account = '';
                value.password = '';
                value.token = '';
                break;
            case 'basicAuth':
                value.mode = 'basicAuth';
                value.token = '';
                break;
            case 'bearerToken':
                value.mode = 'bearerToken';
                value.account = '';
                value.password = '';
                break;
        }
        this.setPureState({
            'form': this.state.form
        });
    }

    render() {
        return <AdminLayout>
            <PageHeader>{Lang.t('settingIntegrate.header')}</PageHeader>
            <div className="card">
                <div className="card-body">
                    <div className="p-v-20 form-group row">
                        <div className="tab-vertical tab-base tab-stacked-left">
                            <div className="tab-content">
                                <div className="panel setting">
                                    <div className="panel-body">
                                        <form className="form-horizontal" onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }}>
                                            <div className="form-group">
                                                <table className="table table-striped table-hover table-bordered" ref={(elm) => { this.table = elm; }}>
                                                    <thead>
                                                        <tr>
                                                            <th style={{ "minWidth": "300px", "textAlign": "center" }}>{Lang.t('setting.field.name')}</th>
                                                            <th style={{ "minWidth": "300px", "textAlign": "center" }}>{Lang.t('setting.field.value')}</th>
                                                            <th style={{ "width": "100%", "textAlign": "center" }}>{Lang.t('setting.field.desc')}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.integrateType')}</td>
                                                            <td>
                                                                <div>
                                                                    <label className="col-form-label">
                                                                        <input type="radio" name="gender"
                                                                            value="Json"
                                                                            checked={this.state.form[0].value == 'Json'}
                                                                            onChange={(ev) => { this.state.form[0].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                        /> JSON
                                                                    </label>
                                                                </div>
                                                                <div>
                                                                    <label className="col-form-label">
                                                                        <input type="radio" name="gender"
                                                                            value="Hl7"
                                                                            checked={this.state.form[0].value == 'Hl7'}
                                                                            onChange={(ev) => { this.state.form[0].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                        /> HL7
                                                                    </label>
                                                                </div>
                                                                <div>
                                                                    <label className="col-form-label">
                                                                        <input type="radio" name="gender"
                                                                            value="Json-Hl7"
                                                                            checked={this.state.form[0].value == 'Json-Hl7'}
                                                                            onChange={(ev) => { this.state.form[0].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                        /> JSON-HL7
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.integrateMode')}</td>
                                                            <td>
                                                                <select className="form-control"
                                                                    onChange={(ev) => { this.changeValueIntergrateMode(ev) }}
                                                                    value={this.state.form[1].value.mode}
                                                                >
                                                                    <option value="notVerified">Không xác thực</option>
                                                                    <option value="basicAuth">Basic Auth</option>
                                                                    <option value="bearerToken">Bearer Token</option>
                                                                </select>
                                                                <h4></h4>
                                                                {
                                                                    this.state.form[1].value.mode == 'basicAuth' &&
                                                                    <div>
                                                                        <div className="col-sm-12 row">
                                                                            <label className="col-sm-5 col-form-label">
                                                                                Tài khoản
                                                                        </label>
                                                                            <input type="text"
                                                                                className="form-control col-sm-7"
                                                                                value={this.state.form[1].value.account}
                                                                                onChange={(ev) => { this.state.form[1].value.account = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                            />
                                                                        </div>
                                                                        <h4></h4>
                                                                        <div className="col-sm-12 row">
                                                                            <label className="col-sm-5 col-form-label">
                                                                                Mật khẩu
                                                                            </label>
                                                                            <input type="password"
                                                                                className="form-control col-sm-7"
                                                                                value={this.state.form[1].value.password}
                                                                                onChange={(ev) => { this.state.form[1].value.password = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                }
                                                                {
                                                                    this.state.form[1].value.mode == 'bearerToken' &&
                                                                    <div>
                                                                        <div className="col-sm-12 row">
                                                                            <label className="col-sm-5 col-form-label">
                                                                                Token
                                                                        </label>
                                                                            <input type="text"
                                                                                className="form-control col-sm-7"
                                                                                value={this.state.form[1].value.token}
                                                                                onChange={(ev) => { this.state.form[1].value.token = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                }
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.urlGetListRadiologist')}</td>
                                                            <td>
                                                                <div>
                                                                    <input type="text"
                                                                        className="form-control"
                                                                        value={this.state.form[2].value}
                                                                        onChange={(ev) => { this.state.form[2].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                    />
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.urlGetListClinician')}</td>
                                                            <td>
                                                                <div>
                                                                    <input type="text"
                                                                        className="form-control"
                                                                        value={this.state.form[3].value}
                                                                        onChange={(ev) => { this.state.form[3].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                    />
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.urlNotiCaptured')}</td>
                                                            <td>
                                                                <div>
                                                                    <input type="text"
                                                                        className="form-control"
                                                                        value={this.state.form[4].value}
                                                                        onChange={(ev) => { this.state.form[4].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                    />
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.urlGetResultToHis')}</td>
                                                            <td>
                                                                <div>
                                                                    <input type="text"
                                                                        className="form-control"
                                                                        value={this.state.form[5].value}
                                                                        onChange={(ev) => { this.state.form[5].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                                    />
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>{Lang.t('settingIntegrate.field.formatData')}</td>
                                                            <td>
                                                                <div>
                                                                    <textarea
                                                                        className="form-control"
                                                                        rows="5"
                                                                        cols="50"
                                                                        onChange={(ev) => { this.state.form[6].value = ev.target.value; this.setPureState({ form: this.state.form }) }}
                                                                        value={this.state.form[6].value}>
                                                                    </textarea>
                                                                </div>
                                                            </td>
                                                            <td></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div className="form-group text-right">
                                                <div className="col-sm-12">
                                                    <button type="submit" className="btn btn-primary">{Lang.t('setting.btnSave')}</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    }

}