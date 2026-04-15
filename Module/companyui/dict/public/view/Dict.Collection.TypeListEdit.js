Dict.Collection.TypeListEdit = class DictCollectionTypeListEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newtype(),
            'modal': 'modalDictCollection'
        };
    }

    // mở modal
    static open(type) {
        var type = type;
        DictCollectionTypeListEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            type = $.extend(instance.newtype(), type);
            instance.setPureState({
                'form': type,
                'currenttype': $.extend(instance.newtype(), type)
            }, () => {
            });
        });
        return new Promise((done) => {
            DictCollectionTypeListEdit.instance.done = done || new Function;
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
                        <Dict.Collection.List modal={this.state.modal} />
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dict.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </div>
        );
    }
}