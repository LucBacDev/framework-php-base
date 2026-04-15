class Alert extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            message: ''
        };

        this.bindThis(['onModalReady', 'close', 'onModalHidden', 'openModalShown']);
    }

    static open(message) {
        if (!Alert.instance) {
            $('body').append('<div id="alert-component"></div>');
            Alert.instance = ReactDOM.render(<Alert />, document.getElementById('alert-component'));
            console.log($('#alert-component').html())
        }
        Alert.instance.setState({ 'message': message });
        Alert.instance.modal.showModal();
        return new Promise((done) => {
            Alert.instance.done = done || new Function;
        });
    }

    onModalReady(modal) {
        this.modal = modal;
    }

    close() {
        this.modal.hideModal();
    }

    openModalShown() {
        this.btnClose.focus();
    }

    onModalHidden() {
        Alert.instance.done();
    }

    render() {
        return (
            <Modal ref={this.onModalReady} events={{
                'modal.shown': this.openModalShown,
                'modal.hidden': this.onModalHidden
            }}>
                <Modal.Header>{Lang.t('alert.title')}</Modal.Header>
                <Modal.Body>{this.state.message}</Modal.Body>
                <Modal.Footer>
                    <button className="btn btn-primary" onClick={this.close} ref={(elm) => { this.btnClose = elm; }}>
                        {Lang.t('alert.ok')}
                    </button>
                </Modal.Footer>
            </Modal>
        );
    }
}