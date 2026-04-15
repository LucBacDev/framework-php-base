class ProcessInsert extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);

        this.state = {
            contactPoints: [],
            selectedContactPoint: "",
            serviceID: ""
        };

        this.contactPointModel = new ContactPointModel();
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

    static open(contactPoints, serviceID) {
        ProcessInsert.getInstance().then((instance) => {
            let selectedContactPoint = { ...contactPoints[0]};
            instance.modal.showModal();
            instance.setState({
                contactPoints: contactPoints,
                selectedContactPoint: selectedContactPoint,
                serviceID: serviceID
            });
        })
        return new Promise((done) => {
            ProcessInsert.instance.done = done || new Function;
        });
    }

    handleSubmit(ev) {
        ev.preventDefault();
        new ServiceModel().insertProcess({
            serviceID: this.state.serviceID,
            contactPointID: this.state.selectedContactPoint.id
        }).then(resp => {
            if (resp.status) {
                if (ProcessInsert.instance.done)
                    ProcessInsert.instance.done(resp);
                this.modal.hideModal();
            }
        });
    }

    render() {
        return (
            <form onSubmit={(ev) => {
                this.handleSubmit(ev);
            }} ref={(elm) => {
                this.form = elm;
            }} noValidate>
                <Modal ref={(elm) => {
                    this.modal = elm;
                }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('process.modal.insert.header')}</Modal.Header>
                    <Modal.Body>

                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label text-bold"
                                   htmlFor="sel-type">{Lang.t('process.contactPoint')}</label>
                            <div className="col-sm-7">
                                <select className="form-control" id="sel-type" value={this.state.selectedContactPoint.address}
                                        onChange={(ev) => {
                                            this.setPureState({selectedContactPoint: {...this.state.contactPoints[ev.target.value]}});
                                        }}>
                                    {this.state.contactPoints.map((el, index) =>
                                        <option key={index} value={index}>{el.address}</option>
                                    )}
                                </select>
                            </div>
                        </div>

                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('process.btn.add')}</button>
                        <button type="button" className="btn btn-secondary"
                                data-dismiss="modal">{Lang.t('process.btn.close')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}