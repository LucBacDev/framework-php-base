class MessageDetail extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'message': this.newMessage()
        };
        this.model = new MessageQueueModel();
    }

    // mở modal
    static open(message) {
        MessageDetail.getInstance().then((instance) => {
            instance.modal.showModal();
            instance.setPureState({
                'message': message
            }, () => {
                //load lại dữ liệu cho chắc
                // if (site.id)
                //     instance.siteModel.getSite(site.id).then((site) => {
                //         instance.setPureState({ 'form': site });
                //     });
            });
        });
        return new Promise((done) => {
            MessageDetail.instance.done = done || new Function;
        });
    }

    newMessage() {
        return {
            "topic" : "",
            "message_id": "",
            "created_time": "",
            "body": "",
            "response": "",
        };
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

    saveMessage(ev) {
        this.model.updateMessage(this.state.message.message_id, this.state.message.body).then((resp) => {
            if (resp["status"] === true) {
                $.toast({
                    text: Lang.t("queue.success"),
                    icon: 'success',
                    position: 'top-right'
                });
            } else {
                $.toast({
                    text: Lang.t("queue.error"),
                    icon: 'error',
                    position: 'top-right'
                });
            }
        });
    }

    retry(ev) {
        this.model.retry(this.state.message.message_id).then((resp) => {
            $.toast({
                text: Lang.t("queue.success"),
                icon: 'success',
                position: 'top-right'
            });

            this.modal.hideModal();

        });
    }

    render() {
        return (
            <form ref={(elm) => { this.form = elm; }} noValidate>
                <Modal ref={(elm) => { this.modal = elm; }} size="modal-lg" events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('message.header')}</Modal.Header>
                    <Modal.Body>

                        <div className="row mb-2">
                            <label className="col-sm-4">{Lang.t('queue.message.topic')}:</label>
                            <span className="col-sm-8">{this.state.message.topic}</span>
                        </div>
                        <div className="row mb-2">
                            <label className="col-sm-4">{Lang.t('queue.message.message_id')}:</label>
                            <span className="col-sm-8">{this.state.message.message_id}</span>
                        </div>
                        <div className="row mb-2">
                            <label className="col-sm-4">{Lang.t('queue.message.created_time')}:</label>
                            <span className="col-sm-8">{this.state.message.created_time}</span>
                        </div>
                        <div className="row mb-2">
                            <label className="col-sm-4">{Lang.t('queue.message.body')}:</label>
                        </div>
                        <textarea rows="10" className="form-control" aria-label="With textarea"
                                  value={this.state.message.body}
                                  onChange={(ev) => {
                                      this.state.message.body = ev.target.value;
                                      this.setPureState({message: this.state.message});
                                  }}
                        />
                        <div className="row mb-2">
                            <label className="col-sm-4">{Lang.t('queue.message.response')}:</label>
                        </div>
                        <textarea rows="10" className="form-control" aria-label="With textarea" value={this.state.message.response}/>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" className="btn btn-primary" onClick={(ev) => {this.retry(ev)}}>{Lang.t('queue.retry')}</button>
                        <button type="button" className="btn btn-primary" onClick={(ev) => {this.saveMessage(ev)}}>{Lang.t('queue.save')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('queue.cancel')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}