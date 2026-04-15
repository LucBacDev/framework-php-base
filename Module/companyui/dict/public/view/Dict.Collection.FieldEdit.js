Dict.Collection.FieldEdit = class DictCollectionFieldEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newfield()
        };
    }

    // mở modal
    static open(field) {
        var field = field;
        DictCollectionFieldEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            field = $.extend(instance.newfield(), field);
            instance.setPureState({
                'form': field,
                'currentfield': $.extend(instance.newfield(), field)
            }, () => {
            });
        });
        return new Promise((done) => {
            DictCollectionFieldEdit.instance.done = done || new Function;
        });
    }

    newfield() {
        return {
            'id': '',
            'name': '',
            'dataType': '',
            'defaultValue': '',
            'comment': ''
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
        var data = $.extend({}, this.state.form);
        ev.preventDefault();
        var form = $(this.form);
        if (form[0].checkValidity() === false) {
            $(form).addClass('was-validated');
            return;
        }
        //Thực hiện submit
        DictCollectionFieldEdit.instance.done(data);
        this.modal.hideModal();
    }

    editType(field) {
        Dict.Collection.TypeListEdit.open(field);
    }

    changeType(ev) {
        this.setPureState({ form: this.state.form });
        if (ev.target.value == 'list') {
            this.editType();
        }
    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} noValidate >
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t("dict.field.header")}</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-fieldID">{Lang.t('dict.id')} <Require /></label>
                            <div className="col-sm-8">
                                <input type="code" className="form-control" id="txt-fieldID"
                                    required
                                    value={this.state.form.id}
                                    onChange={(ev) => { this.state.form.id = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('dict.validateID')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-namefield">{Lang.t('dict.field.name')} <Require /></label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control" id="txt-namefield"
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
                            <label className="col-sm-4 col-form-label control-label" htmlFor="select-type">{Lang.t('dict.field.type')} <Require /></label>
                            <div className="col-sm-8" >
                                <select className="form-control" id="select-type" value={this.state.form.dataType}
                                    onChange={(ev) => { this.state.form.dataType = ev.target.value; this.changeType(ev) }}
                                    required='required'
                                >
                                    <option value=''>{Lang.t('dict.field.type.none')}</option>
                                    <option value='list'>{Lang.t('dict.field.type.list')}</option>
                                    <option value='text'>{Lang.t('dict.field.type.text')}</option>
                                    <option value='textarea'>{Lang.t('dict.field.type.longtext')}</option>
                                </select>
                                <div className="invalid-tooltip">
                                    {Lang.t('dict.validateType')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-namefield">{Lang.t('dict.field.defaultValue')}</label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control" id="txt-namefield"
                                    value={this.state.form.defaultValue}
                                    onChange={(ev) => { this.state.form.defaultValue = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label" htmlFor="txt-namefield">{Lang.t('dict.field.comment')}</label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control" id="txt-namefield"
                                    value={this.state.form.comment}
                                    onChange={(ev) => { this.state.form.comment = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
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