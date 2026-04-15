License.List = class LicenseList extends PureComponent {
    constructor(props) {
        super(props);
        this.model = new License.Model;
        this.state = {
            'filter': {
                'nameSearch': ''
            },
            'licenses': [],
            'license': '',
            'selected': ''
        };
    }

    componentDidMount() {
        App.requireLogin();
        this.getLicenses();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'license');
    }

    // lấy danh sách license trong 1 site
    getLicenses(licenseID = null) {
        return new Promise((done) => {
            var filter = { 'loadData': 0 };
            this.model.getLicenses(filter).then((resp) => {
                this.setPureState({ 'licenses': resp }, () => {
                    //lấy dữ liệu license đầu
                    if (resp.length != 0) {
                        var id = licenseID ? licenseID : resp[0].id;
                        this.setPureState({
                            'selected': id
                        });
                        this.getLicense(id);
                    } else {
                        this.setPureState({
                            'license': ''
                        });
                    }
                    done();
                });
            });
        });
    }

    // lấy thông tin site khi biết id
    getLicense(licenseID) {
        return new Promise((done) => {
            var filter = { 'loadData': 1 };
            this.model.getLicense(licenseID, filter).then((resp) => {
                this.setPureState({ 'license': resp }, () => {
                    done();
                });
            });
        });
    }

    downloadHardWareID() {
        this.model.downloadHardWareID().then((resp) => {
            if (resp.status) {
                this.model.downloadFile('hardwareid.req', resp.data);
            }
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    activeLicense() {
        License.ActiveLicense.open().then((resp) => {
            if (resp.status) {
                if (resp.code && resp.code.id) {
                    this.getLicenses(resp.code.id);
                }
            } else {
                // Alert.open(Lang.t('update.error'));
            }
        });
    }

    refreshLicense() {
        var data = {
            'productName': this.state.license.productName
        }
        if (!this.state.license.id) {
            Alert.open(Lang.t('license.validate.notLicense'));
        } else {
            this.model.refreshLicense(this.state.license.id, data).then((resp) => {
                if (resp.status) {
                    if (resp.data.id) {
                        this.getLicenses(resp.data.id);
                    }
                } else {
                    // Alert.open(Lang.t('update.error'));
                }
            });
        }
    }

    uploadLicense() {
        License.UploadLicense.open().then((resp) => {
            if (resp.status) {
                if (resp.code && resp.code.id) {
                    this.getLicenses(resp.code.id);
                }
            } else {
                // Alert.open(Lang.t('update.error'));
            }
        });
    }

    returnLicense() {
        License.ReturnLicense.open(this.state.license).then((resp) => {
            if (resp.status) {
                this.getLicenses();
            } else {
                // Alert.open(Lang.t('update.error'));
            }
        });;
    }

    changeProductName(ev) {
        this.setPureState({
            'selected': ev.target.value
        });
        this.getLicense(ev.target.value);
    }

    formatLicenseKey(licenseKey) {
        if (!licenseKey)
            return;
        return licenseKey.slice(0, 5) + '-' + licenseKey.slice(5, 13) + '-' + licenseKey.slice(13, 18) + '-' + licenseKey.slice(18, 25) + '-' + licenseKey.slice(25, 32);
    }

    render() {
        return (
            <AdminLayout>
                <PageHeader>{Lang.t('license.header')}</PageHeader>
                <div className="card">
                    <div className="card-body">
                        <div>
                            <div className="left col-md-12 row">
                                <select className="form-control col-md-1"
                                    onChange={(ev) => { this.changeProductName(ev); }}
                                    value={this.state.selected}
                                >
                                    {this.state.licenses.map((license, idx) =>
                                        <option key={idx} value={license.id}>{license.productName}</option>
                                    )}
                                </select>
                                <div className="col-md-11" >
                                    <button type="button" className="btn btn-primary" onClick={() => { this.downloadHardWareID() }}>{Lang.t('license.btnDownloadHardwareID')}</button>
                                    <button type="button" className="btn btn-primary right" onClick={() => { this.activeLicense() }}>{Lang.t('license.btnEnterKey')}</button>
                                    <button type="button" className="btn btn-primary" onClick={() => { this.uploadLicense() }}>{Lang.t('license.btnUploadKey')}</button>
                                    <button type="button" className="btn btn-primary" onClick={() => { this.refreshLicense() }}>{Lang.t('license.btnRefreshKey')}</button>
                                    <button type="button" className="btn btn-danger" onClick={() => { this.returnLicense() }}>{Lang.t('license.btnReturnKey')}</button>
                                </div>
                            </div>
                        </div>
                        <h4></h4>
                        <table className="table table-striped table-hover table-bordered" ref={(elm) => { this.table = elm; }}>
                            {this.state.license.licenseInfo &&
                                <tbody>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>License</td>
                                        <td style={{ 'width': '100%' }}>{this.formatLicenseKey(this.state.license.licenseInfo.licenseKey)}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.licensePackage')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.planName}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.product')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.productName}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.status')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.status == 'activated' ? Lang.t('license.field.status.active') : ''}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.issueDate')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.issueDate}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.expiryDate')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.expiryDate}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.radiologist')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.radiologist}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.clinician')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.clinician}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.technician')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.technician}</td>
                                    </tr>
                                    <tr>
                                        <td style={{ 'minWidth': '200px' }}>{Lang.t('license.field.species')}</td>
                                        <td style={{ 'width': '100%' }}>{this.state.license.licenseInfo.offline == "0" ? 'License Offline' : 'License Online'}</td>
                                    </tr>
                                </tbody>
                            }
                        </table>
                    </div>
                </div>
            </AdminLayout>
        )
    }

}