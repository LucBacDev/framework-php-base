class SettingEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            'form': this.newsetting()
        };
        this.settingModel = new Setting.Model;
        this.getFields(this.props.settingID);
    }

    // theo dõi thay đổi của props
    componentWillReceiveProps(nextProps) {
        console.log('nextProps +++--------', nextProps);
        this.getFields(nextProps.settingID);
    }

    inArray(key, arr) {
        return jQuery.inArray(key, arr) !== -1 ? true : false;
    };

    getFields(id) {
        this.settingModel.getFieldsFormId(id).then((resp) => {
            resp = resp.map(item => {
                console.log(item["value"]);
                if (item["value"] == null)
                    item["value"] = item["defaultVal"];

                return item;
            });
            if (!resp.length || !resp[0].id) {
                resp = [];
            }
            this.setPureState({ 'form': resp });
        });
    }

    // ghi lại
    handleSubmit(ev) {
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        //Xử lý ghi lại
        this.settingModel.updateValueField(data).then((resp) => {
            if (resp.status) {
                Alert.open(Lang.t('settingEdit.notifi.success'));
            }
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    newsetting() {
        return [{
            'id': '',
            'name': '',
            'value': '',
            'desc': '',
            'defaultVal': ''
        }];
    }

    render() {
        return (
            <div id={'setting-' + this.props.settingID} className={this.inArray(this.props.settingID, this.props.configdetail) ? 'tab-pane active show' : 'tab-pane'}>
                <div className="panel setting">
                    <div className="panel-body">
                        <form className="form-horizontal" onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }}>
                            <div className="form-group">
                                <table className="table table-striped table-hover table-bordered" ref={(elm) => { this.table = elm; }}>
                                    <thead>
                                        <tr>
                                            <th style={{ "minWidth": "250px", "textAlign": "center" }}>{Lang.t('setting.field.name')}</th>
                                            <th style={{ "minWidth": "250px", "textAlign": "center" }}>{Lang.t('setting.field.value')}</th>
                                            <th style={{ "width": "100%", "textAlign": "center" }}>{Lang.t('setting.field.desc')}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {this.state.form.map((field, idx) =>
                                            <tr key={field.id}>
                                                <td>{field.label}</td>
                                                <td>
                                                    <input type={field.dataType} className="form-control" id="txt-value"
                                                        value={field.value ? field.value : field.defaultVal}
                                                        onChange={(ev) => { this.state.form[idx].value = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                                    />
                                                </td>
                                                <td dangerouslySetInnerHTML={{ __html: field.desc}}></td>
                                            </tr>
                                        )}
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
        );
    }
}