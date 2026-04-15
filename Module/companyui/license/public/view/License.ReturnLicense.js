License.ReturnLicense = class LicenseReturnLicense extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'license': this.newLicense()
        };
        this.model = new License.Model;
        this.checkLicenseExist = false;
    }

    // open modal
    static open(license) {
        var license = license;
        LicenseReturnLicense.getInstance().then((instance) => {
            instance.modal.showModal();
            license = $.extend(instance.newLicense(), license);
            instance.checkLicenseExist = license.id ? true : false;
            instance.setPureState({
                'license': license
            }, () => { });
        });
        return new Promise((done) => {
            LicenseReturnLicense.instance.done = done || new Function;
        });
    }

    newLicense() {
        return {
            'id': '',
            'licenseData': '',
            'productName': ''
        };
    }

    // show modal
    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');
        console.log('Hiện modal');
    }
    // hide modal
    onModalHidden() {
        console.log('Ẩn modal');
    }

    // return license online
    returnLicense(type) {
        if (!this.checkLicenseExist) {
            Alert.open(Lang.t('license.validate.notLicense'));
        } else {
            var data = {
                'licenseType': type
            };
            // return license
            this.model.returnLicense(this.state.license.id, data).then((resp) => {
                if (resp.status) {
                    if (LicenseReturnLicense.instance.done)
                    LicenseReturnLicense.instance.done(resp);
                    this.modal.hideModal();
                }
            });
        }
    }

    render() {
        console.log(this.state.license.length)
        return (
            <Modal ref={(elm) => { this.modal = elm; }} events={{
                'modal.shown': this.onModalShown,
                'modal.hidden': this.onModalHidden
            }} size="modal-lg">
                <Modal.Header>{Lang.t('license.edit.return.header')}</Modal.Header>
                <Modal.Body>
                    <div className="form-group row">
                        <div className="col-sm-12">
                            <label>{Lang.t("license.edit.return.note")}</label>
                        </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-primary" onClick={() => { this.returnLicense('online') }}>{Lang.t('license.btnReturnOnline')}</button>
                    <button type="button" className="btn btn-primary" onClick={() => { this.returnLicense('offline') }}>{Lang.t('license.btnReturnOffline')}</button>
                    <button type="button" className="btn btn-primary" onClick={() => { this.returnLicense('cloud') }}>{Lang.t('license.btnReturnCloud')}</button>
                    <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('license.btnClose')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}