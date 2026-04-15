License.ActiveLicense = class LicenseActiveLicense extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': {
                'licenseKey': '',
                'licenseType': ''
            }
        };
        this.model = new License.Model;
    }

    // open modal
    static open() {
        LicenseActiveLicense.getInstance().then((instance) => {
            instance.modal.showModal();
            instance.state.form.licenseKey = '';
            instance.setPureState({
                'form': instance.state.form
            }, () => { });
        });
        return new Promise((done) => {
            LicenseActiveLicense.instance.done = done || new Function;
        });
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

    // submit event
    handleSubmit(licenseType) {
        if (!this.state.form.licenseKey) {
            Alert.open(Lang.t('license.enterKey'))
        } else {
            //save license
            this.state.form.licenseType = licenseType;
            this.setPureState({
                form: this.state.form
            });
            this.model.registerLicense(this.state.form).then((resp) => {
                if (resp.status) {
                    if (licenseType == 'online') {
                        // chưa xử lý
                    }
                    if (licenseType == 'offline') {
                        this.model.downloadFile('request.req', resp.data);
                    }
                    if (LicenseActiveLicense.instance.done)
                        LicenseActiveLicense.instance.done(resp);
                    this.modal.hideModal();
                }
            }).catch((xhr) => {
                if (this.editFail)
                    this.editFail(xhr);
            });
        }
    }

    render() {
        return (
            <Modal ref={(elm) => { this.modal = elm; }} events={{
                'modal.shown': this.onModalShown,
                'modal.hidden': this.onModalHidden
            }} size="modal-lg">
                <Modal.Header>{Lang.t('license.edit.active.header')}</Modal.Header>
                <Modal.Body>
                    <div className="form-group row">
                        <div className="col-sm-12">
                            <input type="text"
                                value={this.state.form.licenseKey}
                                className="form-control" id="txt-licenseID"
                                required
                                ref={(elm) => { this.licenseID = elm; }}
                                onChange={(ev) => { this.state.form.licenseKey = ev.target.value; this.setPureState({ form: this.state.form }); }}
                            />
                        </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-primary" onClick={() => { this.handleSubmit('online') }}>{Lang.t('license.btnActiveOnline')}</button>
                    <button type="button" className="btn btn-primary" onClick={() => { this.handleSubmit('offline') }}>{Lang.t('license.btnActiveOffline')}</button>
                    <button type="button" className="btn btn-primary" onClick={() => { this.handleSubmit('cloud') }}>{Lang.t('license.btnActiveCloud')}</button>
                    <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('license.btnClose')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}