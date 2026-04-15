License.UploadLicense = class LicenseUploadLicense extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden', 'handleFileUpload'
        ]);
        this.state = {
            'licenseUpload': []
        };
        this.model = new License.Model;
    }

    // open modal
    static open() {
        LicenseUploadLicense.getInstance().then((instance) => {
            instance.modal.showModal();
            instance.setPureState({
                'form': instance.state.form
            }, () => { });
        });
        return new Promise((done) => {
            LicenseUploadLicense.instance.done = done || new Function;
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

    uploadFile() {
        $('#licenseFileUpload').remove()
        var input = document.createElement("input")
        input.type = "file"
        input.id = "licenseFileUpload"
        input.name = "licenseFileUpload"
        input.accept = ".lic"
        input.style = "display: none"
        input.onchange = (ev) => {
            var file_data = $('#licenseFileUpload').prop('files')[0];
            this.handleFileUpload(file_data);
        }
        $("body").append(input)
        $("#licenseFileUpload").click();
    }

    handleFileUpload(input) {
        LicenseUploadLicense.getInstance().then((instance) => {
            var form_data = new FormData();
            form_data.append('file', input);
            $.ajax({
                url: App.url('/:siteID/rest/licenses/upload', { siteID: App.siteID }),
                cache: false,
                dataType: 'json',
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function (resp) {
                    if (resp.status) {
                        if (LicenseUploadLicense.instance.done)
                            LicenseUploadLicense.instance.done(resp);
                        instance.modal.hideModal();
                    }
                }
            });
        });

        // this.setPureState({ "licenseUpload": [] });
        // var textType = /lic.*/;
        // if (input[0].type.match(textType)) {
        //     //if is file .lic
        //     //save file license
        // }
        // else {
        //     Alert.open(Lang.t("license.fileTypeErr"));
        // }
    }

    render() {
        return (
            <Modal ref={(elm) => { this.modal = elm; }} events={{
                'modal.shown': this.onModalShown,
                'modal.hidden': this.onModalHidden
            }}>
                <Modal.Header>{Lang.t('license.edit.upload.header')}</Modal.Header>
                <Modal.Body>
                    <div className="form-group row">
                        <div className="col-sm-12">
                            <label>{Lang.t('license.edit.upload.note')}</label>
                        </div>
                    </div>
                    <div className="form-group row">
                        <div className="col-sm-12">
                            <button type="button" id="uploadFile" className="btn btn-primary" onClick={() => { this.uploadFile() }}>Upload file</button>
                        </div>
                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('license.btnClose')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}