class Confirm extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            message: ''
        };

        this.bindThis(['onModalReady', 'close', 'onModalHidden', 'openModalShown']);
    }

    static open(message) {
        if (!Confirm.instance) {
            $('body').append('<div id="confirm-component"></div>');
            Confirm.instance = ReactDOM.render(<Confirm />, document.getElementById('confirm-component'));
        }
        Confirm.instance.setState({ 'message': message });
        Confirm.instance.modal.showModal();
        return new Promise((done) => {
            Confirm.instance.done = done || new Function;
        });
    }

    onModalReady(modal) {
        this.modal = modal;
    }

    close(result) {
        this.state.result = result;
        this.modal.hideModal();
    }

    openModalShown() {
        this.btnClose.focus();
    }

    onModalHidden() {
        Confirm.instance.done(this.state.result ? true : false);
    }

    render() {
        return (
            <Modal ref={this.onModalReady} events={{
                'modal.shown': this.openModalShown,
                'modal.hidden': this.onModalHidden
            }}>
                <Modal.Header>{Lang.t('confirm.title')}</Modal.Header>
                <Modal.Body>{this.state.message}</Modal.Body>
                <Modal.Footer>
                    <button className="btn btn-primary"
                        ref={(elm) => { this.btnClose = elm; }}
                        onClick={() => { this.close(true); }}
                    >
                        {Lang.t('confirm.ok')}
                    </button>
                    <button className="btn btn-secondary"
                        onClick={() => { this.close(false); }}
                    >
                         {Lang.t('confirm.cancel')}
                    </button>
                </Modal.Footer>
            </Modal>
        );
    }
}