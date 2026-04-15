Dict.Item.Edit = class DictItemEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newdict()
        };
        this.dictModel = new Dict.Item.Model;
    }

    // mở modal
    static open(dict) {
        var dict = dict;
        DictItemEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            dict = $.extend(instance.newdict(), dict);
            instance.setPureState({
                'form': dict,
                'currentdict': $.extend(instance.newdict(), dict)
            }, () => {
            });
        });
        return new Promise((done) => {
            DictItemEdit.instance.done = done || new Function;
        });
    }

    newdict() {
        return {
            'id': '',
            'name': '',
            'dict': ''
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
    handleSubmit() {

    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} noValidate >
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>Danh mục: sách</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-fieldID">{Lang.t('dict.id')} <Require /></label>
                            <div className="col-sm-8">
                                <Input type="code" className="form-control" id="txt-fieldID"
                                    required
                                    value={this.state.form.id}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('dict.validateID')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-fieldID">Trường 1</label>
                            <div className="col-sm-8">
                                <Input type="code" className="form-control" id="txt-fieldID" />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-fieldID">Trường 2</label>
                            <div className="col-sm-8">
                                <select className="form-control">
                                    <option>a</option>
                                    <option>b</option>
                                    <option>c</option>
                                </select>
                            </div>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('dict.btnSave')}</button>
                        <button dict="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dict.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}