class ServiceEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden', 'handleSaveButton'
        ]);

        this.state = {
            service: {},
            attrs: {}
        }
        this.model = new ServiceModel();
    }


    // khi hiện modal
    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');
        console.log('Hiện modal');
    }

    // khi ẩn modal
    onModalHidden() {
        console.log('Ẩn modal');
    }

    handleSaveButton(ev) {
        this.model.editService(this.state.service["id"], JSON.stringify(this.state.attrs)).then((resp) => {
            if (resp.status) {
                if (ServiceEdit.instance.done)
                    ServiceEdit.instance.done(resp);
                this.modal.hideModal();
            }
        });
    }

    static open(service) {
        var service = Object.assign({}, service);
        ServiceEdit.getInstance().then((instance) => {

            instance.modal.showModal();
            delete service["numProcess"];
            delete service["command"];
            let attrs = {};
            try {
                attrs = JSON.parse(service.attrs);
            } catch (e) {

            }
            delete service["attrs"];
            instance.setPureState({
                service: service,
                attrs: attrs
            });
        })
        return new Promise((done) => {
            ServiceEdit.instance.done = done || new Function;
        });
    }

    renderAttrs() {
        if (this.state.service["id"] == "DEEP_COMPRESSION") {
            return <DeepCompressionAttributes
                attrs={this.state.attrs}
                setAttrs={(attrs) => this.setPureState({attrs: attrs})}
                dicomCompressionType={["JpegLossy", "Jpeg2000Lossy"]}
            />
        } else if (this.state.service["id"] == "MOVE_NEARLINE") {
            return <CompressionAttributes
                attrs={this.state.attrs}
                setAttrs={(attrs) => this.setPureState({attrs: attrs})}
                dicomCompressionType={["JpegLossless", "Jpeg2000Lossless", "JpegLSLossless"]}
                moveType={["move", "copy"]}
            />
        } else if (this.state.service["id"] == "REBALANCE") {
            return <RebalanceAttributes attrs={this.state.attrs} setAttrs={(attrs) => this.setState({attrs: attrs})}/>
        } else if (this.state.service["id"] == "UPLOAD_AI")
            return <UploadAIAttributes attrs={this.state.attrs} setAttrs={(attrs) => this.setState({attrs: attrs})}/>
        else if (this.state.service["id"] == "CLEAN_DATA")
            return <CleanDataAttributes attrs={this.state.attrs} setAttrs={(attrs) => this.setState({attrs: attrs})}/>
        else if (this.state.service["id"] == "SYNC_ELASTIC")
            return <SyncElasticAttributes attrs={this.state.attrs} setAttrs={(attrs) => this.setState({attrs: attrs})}/>
        else if (this.state.service["id"] == "STORE_DATASET")
            return <StoreDatasetAttributes attrs={this.state.attrs} setAttrs={(attrs) => this.setState({attrs: attrs})}/>
        else
            return null;
    }

    render() {
        return (
            <Modal ref={(elm) => {
                this.modal = elm;
            }} events={{
                'modal.shown': this.onModalShown,
                'modal.hidden': this.onModalHidden
            }} size="modal-xl">
                <Modal.Header>{Lang.t('service.edit.header')} <b>{this.state.service["name"]}</b></Modal.Header>
                <Modal.Body>
                    <table className="table table-bordered">
                        <thead>
                        <tr>
                            <th>{Lang.t('service.edit.field')}</th>
                            <th>{Lang.t('service.edit.value')}</th>
                            <th>{Lang.t('service.edit.description')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {
                            this.renderAttrs()
                        }
                        </tbody>
                    </table>

                </Modal.Body>
                <Modal.Footer>
                    <button className="btn btn-primary" onClick={this.handleSaveButton}>{Lang.t('service.edit.save')}</button>
                    <button type="button" className="btn btn-secondary"
                            data-dismiss="modal">{Lang.t('process.btn.close')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}