class UserPicker extends Component {
    constructor(props) {
        super(props);
        this.state = {
            'modalOptions': {},
            'selected': [],
            'notUsers': []
        };
    }

    static open(opts) {
        UserPicker.getInstance().then((instance) => {
            opts = opts || {};
            opts.notUsers = opts.notUsers || [];
            opts.type = ['user', 'department'];

            instance.setState({ 'modalOptions': opts });
            instance.modal.showModal();

            // load lại danh sách user
            if (instance.userListComp) {
                instance.userListComp.getUsers();
            }
        });

        return new Promise((done, fail) => {
            UserPicker.done = done;
            UserPicker.catch = fail;
        });;
    }

    handleSubmit(selectedOverride) {
        UserPicker.done(selectedOverride || this.state.selected);
        this.modal.hideModal();
    }

    render() {
        return (
            <Modal ref={(elm) => { this.modal = elm; }} size="modal-lg">
                <Modal.Header>{Lang.t('userPicker.header')}</Modal.Header>
                <Modal.Body>
                    <UserList
                        modal={this.state.modalOptions}
                        ref={(elm) => { this.userListComp = elm; }}
                        events={{
                            'update': () => {
                                if (this.userListComp)
                                    this.setState({ 'selected': this.userListComp.getSelectedUsers(true) });
                            }
                        }}
                    />
                </Modal.Body>
                <Modal.Footer>
                    <button type="button" className="btn btn-primary" disabled={this.state.selected.length ? false : true} onClick={() => { this.handleSubmit(); }}>{Lang.t('userPicker.btnChoose')}</button>
                    <button type="button" className="btn btn-secondary close-modal" onClick={() => { this.modal.hideModal(); }}>{Lang.t('userPicker.btnCancel')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}