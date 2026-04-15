class DepPicker extends Component {
    constructor(props) {
        super(props);

        this.state = {
            'modalOptions': {},
            'selected': [],
            'isShowModal': false
        };

        this.depModel = new DepModel;

    }

    static open(opts) {
        DepPicker.getInstance().then((instance) => {
            var that = instance;
            opts = opts || {};
            opts.selectedDepID = opts.selectedDepID || 0;
            opts.pickRootBtn = opts.pickRootBtn || true;
            opts.type = 'department';

            instance.setState({ 'modalOptions': opts });
            instance.modal.showModal();

            if (opts.selectedDepID) {
                that.depModel.getDep(opts.selectedDepID, { 'loadAncestors': 1 })
                    .then((dep) => {
                        var parent = dep.ancestors.length ? dep.ancestors[dep.ancestors.length - 1] : null;
                        that.userListComp.setCurrentDep(parent).then(function () {
                            that.userListComp.setDepChecked(opts.selectedDepID);
                        });
                        that.state.selected = [dep];
                    });
            } else if (that.userListComp) {
                that.userListComp.setCurrentDep(null);
            }
        });

        return new Promise((done, fail) => {
            DepPicker.done = done;
            DepPicker.catch = fail;
        });;
    }

    handleSubmit(selectedOverride) {
        DepPicker.done(selectedOverride || this.state.selected);
        this.modal.hideModal();
    }

    render() {
        return (
            <Modal ref={(elm) => { this.modal = elm; }}
                size="modal-lg"
                className="modal-second">
                <Modal.Header>{Lang.t('depPicker.header')}</Modal.Header>
                <Modal.Body>
                    <UserList
                        isModal="true"
                        modal={this.state.modalOptions}
                        ref={(elm) => { this.userListComp = elm; }}
                        events={{
                            'update': () => {
                                if (this.userListComp)
                                    this.setState({ 'selected': this.userListComp.getSelectedDeps(true) });
                            }
                        }}
                    />
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-primary" disabled={this.state.selected.length ? false : true} onClick={() => { this.handleSubmit(); }}>{Lang.t('depPicker.btnChooseDep')}</button>
                    {this.state.modalOptions.pickRootBtn &&
                        <button type="button" className="btn btn-primary"
                            onClick={() => { this.handleSubmit([{ 'id': 0, 'name': Lang.t('RootDirectory') }]); }}>
                            {Lang.t('depPicker.folderRoot')}
                        </button>
                    }

                    <button type="button" className="btn btn-secondary close-modal" onClick={() => { this.modal.hideModal(); }}>{Lang.t('depPicker.btnCancel')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}