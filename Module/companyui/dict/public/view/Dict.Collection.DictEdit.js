Dict.Collection.DictEdit = class DictCollectionDictEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newdict()
        };
    }

    // mở modal
    static open(dict) {
        var dict = dict;
        DictCollectionDictEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            dict = $.extend(instance.newdict(), dict);
            instance.setPureState({
                'form': dict,
                'currentdict': $.extend(instance.newdict(), dict)
            }, () => {
            });
        });
        return new Promise((done) => {
            DictCollectionDictEdit.instance.done = done || new Function;
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

    render() {
        return (
            <div>
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>Tên danh mục</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-7 col-form-label control-label">Nhấn vào tên trường để chọn</label>
                            <div className="col-sm-5 input-group right">
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control" placeholder="Tìm kiếm" onChange={(ev) => { this.state.filter.nameSearch = ev.target.value; this.handleChangeFilter(); }} />
                            </div>
                        </div>
                        <div className="form-group r ow">
                            <table className="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên trường</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><a href="javascript:;" onClick={() => { this.chooseDict(dict) }}>Tên</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button dict="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dict.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </div>
        );
    }
}