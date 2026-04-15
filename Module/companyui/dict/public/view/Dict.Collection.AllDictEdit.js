Dict.Collection.AllDictEdit = class DictCollectionAllDictEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newdict(),
            'selected': 'radio-autoRenderID'
        };
        this.model = new Dict.Collection.Model;
    }

    // mở modal
    static open(dict) {
        var dict = dict;
        DictCollectionAllDictEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            dict = $.extend(instance.newdict(), dict);
            instance.setPureState({
                'form': dict,
                'currentdict': $.extend(instance.newdict(), dict)
            }, () => {
            });

        });
        return new Promise((done) => {
            DictCollectionAllDictEdit.instance.done = done || new Function;
        });
    }

    newdict() {
        return {
            'id': '',
            'name': '',
            'autoRenderID': true,
            'enterID': false,
            'itemFields': []
        };
    }

    // khi hiện modal
    onModalShown() {
        //reset validate
        $(this.form).removeClass('was-validated');
        // console.log('Hiện modal');
    }
    // khi ẩn modal
    onModalHidden() {
        // console.log('Ẩn modal');
    }
    // ghi lại
    handleSubmit(ev) {
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        //thực hiện submit
        this.model.updateCollection(this.state.form.id, data).then((resp) => {
            if (resp.status) {
                DictCollectionAllDictEdit.instance.done(resp);
                this.modal.hideModal();
            }
        });

    }

    editField(field) {
        Dict.Collection.FieldEdit.open(field).then((data) => {
            this.state.form.itemFields.push(data);
            this.setPureState({ itemFields: this.state.form.itemFields });
        });
    }

    deleteField(idField) {
        Confirm.open('Xác nhận xóa trường có id là: ' + idField + '?').then((resp) => {
            if (resp) {
                var fields = this.state.form.itemFields;
                for (var i in fields) {
                    if (fields[i].id == idField) {
                        fields.splice(i, 1);
                        break;
                    }
                }
                this.setPureState({ itemFields: fields });
            }
        });
    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} noValidate >
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }} size="modal-lg">
                    <Modal.Header>{Lang.t('dict.title')}</Modal.Header>
                    <Modal.Body>
                        <div className="form-modal">
                            <div className="form-group row">
                                <label className="col-sm-5 col-form-label control-label" htmlFor="txt-dictID">{Lang.t('dict.id')} <Require /></label>
                                <div className="col-sm-7">
                                    <input type="code" className="form-control" id="txt-dictID"
                                        required
                                        ref={(elm) => { this.dictID = elm; }}
                                        value={this.state.form.id}
                                        onChange={(ev) => { this.state.form.id = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                    />
                                    <div className="invalid-tooltip">
                                        {Lang.t('dict.validateID')}
                                    </div>
                                </div>
                            </div>
                            <div className="form-group row">
                                <label className="col-sm-5 col-form-label control-label" htmlFor="txt-namedict">{Lang.t('dict.dictName')}<Require /></label>
                                <div className="col-sm-7">
                                    <input type="text" className="form-control" id="txt-namedict" ref={(elm) => { this.namedict = elm; }}
                                        required='required'
                                        value={this.state.form.name}
                                        onChange={(ev) => { this.state.form.name = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                    />
                                    <div className="invalid-tooltip">
                                        {Lang.t('dict.validateName')}
                                    </div>
                                </div>
                            </div>
                            <div className="form-group row">
                                <label className="col-sm-5 col-form-label control-label" htmlFor="chk-dict-status">{Lang.t('dict.allDictID')} <Require /></label>
                                <div className="col-sm-4">
                                    <input type='radio' id='radio-autoRenderID' name='radio-autoRenderID' value='radio-autoRenderID'
                                        checked={this.state.selected === 'radio-autoRenderID'} onChange={(e) => this.setState({ selected: e.target.value })} />
                                    <label htmlFor="radio-autoRenderID">{Lang.t('dict.autoRenderID')}</label>
                                </div>
                                <div className="col-sm-3">
                                    <input type='radio' id='radio-enterID' name='radio-enterID' value='radio-enterID'
                                        checked={this.state.selected === 'radio-enterID'} onChange={(e) => this.setState({ selected: e.target.value })} />
                                    <label htmlFor="radio-enterID">{Lang.t('dict.enterID')}</label>
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <div className="col-sm-12">
                                <label className=" col-form-label control-label">{Lang.t('dict.field.list')}</label>
                            </div>
                        </div>
                        <div className="form-group row">
                            <div className="col-sm-12">
                                <button type="button" className="btn btn-primary" onClick={() => { this.editField() }}>{Lang.t('dict.field.add')}</button>
                            </div>
                        </div>

                        <div className="form-group row">
                            <table className="table table-striped table-hover" ref={(elm) => { this.table = elm; }}>
                                <thead>
                                    <tr>
                                        <th style={{ 'minWidth': '50px' }}>{Lang.t('dict.stt')}</th>
                                        <th style={{ 'minWidth': '30px' }}>&nbsp;</th>
                                        <th style={{ 'minWidth': '100px' }}>{Lang.t('dict.id')}</th>
                                        <th style={{ 'minWidth': '100px' }}>{Lang.t('dict.field.datatype')}</th>
                                        <th style={{ 'minWidth': '150px' }}>{Lang.t('dict.field.name')}</th>
                                        <th style={{ 'minWidth': '150px' }}>{Lang.t('dict.field.defaultValue')}</th>
                                        <th style={{ 'width': '100%' }}>{Lang.t('dict.field.comment')}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {this.state.form.itemFields.map((field, idx) =>
                                        <tr key={field.id}>
                                            <td>{idx + 1}</td>
                                            <td>
                                                <div className="dropdown">
                                                    <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i className="ti ti-menu"></i>
                                                    </a>
                                                    <div className="dropdown-menu">
                                                        <button className="dropdown-item" type="button" onClick={() => { this.editField(field) }}>{Lang.t('dict.btnEdit')}</button>
                                                        <button className="dropdown-item" type="button" onClick={() => { this.deleteField(field.id) }}>{Lang.t('dict.btnDelete')}</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{field.id}</td>
                                            <td>{field.dataType}</td>
                                            <td>{field.name}</td>
                                            <td>{field.defaultValue}</td>
                                            <td>{field.comment}</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('dict.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dict.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}