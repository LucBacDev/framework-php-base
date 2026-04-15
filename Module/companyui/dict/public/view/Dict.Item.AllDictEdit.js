Dict.Item.AllDictEdit = class DictItemAllDictEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newtype(),
            'modal': 'modalDictItem'
        };
    }

    // mở modal
    static open(type) {
        var type = type;
        DictItemAllDictEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            type = $.extend(instance.newtype(), type);
            instance.setPureState({
                'form': type,
                'currenttype': $.extend(instance.newtype(), type)
            }, () => {
            });
        });
        return new Promise((done) => {
            DictItemAllDictEdit.instance.done = done || new Function;
        });
    }

    newtype() {
        return {
            'id': '',
            'name': '',
            'type': ''
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

    // ghi lại
    handleSubmit(ev) {

    }

    render() {
        return (
            <div>
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t("dict.header")}</Modal.Header>
                    <Modal.Body>
                        <Dict.Collection.List
                            modal={this.state.modalOptions}
                            ref={(elm) => { this.collectionComp = elm; }}
                            events={{
                                'update': () => {
                                    if (this.collectionComp) {
                                        DictItemAllDictEdit.instance.done(this.collectionComp.getSelectedCollection());
                                        this.modal.hideModal();
                                    }
                                }
                            }}

                        />
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dict.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </div>
        );
    }
}