class DepartmentEdit extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            'form': this.newDepartment(),
            'depID': null
        };
        this.depModel = new DepartmentModel;
    }

    newDepartment() {
        return {
            active: true,
            name: '',
            code: '',
            parentID: 0,
            parentDep: {
                'name': '',
                'id': 0
            },
            desc: ''
        };
    }

    static open(department) {
        var department = department;
        DepartmentEdit.getInstance().then((instance) => {
            department = $.extend(instance.newDepartment(), department);

            if (department.ancestors) {
                department.ancestors.map((parent) => {
                    department.parentDep = parent;
                });
            }

            if (!department.parentDep) {
                department.parentDep = {
                    'name': '',
                    'id': 0
                };
            }

            instance.setState({
                'depID': department.id,
                'form': department
            });

            instance.modal.showModal();
        });

        return new Promise((done) => {
            DepartmentEdit.instance.done = done || new Function;
        });
    }

    onModalShown() {
        setTimeout(() => {
            this.txtDepName.focus();
            $(this.form).removeClass('was-validated');
        });
    }

    onModalHidden() {
        $(this.form).removeClass('was-validated');
    }

    handleSubmit(ev) {
        ev.preventDefault();

        var form = $(this.form);
        $(form).addClass('was-validated');
        if (form[0].checkValidity() === false) {
            return;
        }

        var data = $.extend({}, this.state.form);
        this.depModel.updateDepartment(this.state.depID, data).then((resp) => {
            if (DepartmentEdit.instance.done)
                DepartmentEdit.instance.done(resp);
            this.modal.hideModal();
        }).catch((xhr) => {
            Alert.open(xhr.responseJSON ? xhr.responseJSON.message : Lang.t('dep.error.update'));
        });
    }

    pickParent() {
        // Sử dụng DepartmentPicker nếu có, hoặc chọn từ danh sách
        if (typeof DepPicker !== 'undefined') {
            DepPicker.open({
                'selectedDepID': this.state.form.parentDep.id,
                'not': this.state.depID
            }).then((deps) => {
                this.state.form.parentDep = deps[0];
                this.state.form.parentID = deps[0].id;
                this.setPureState({form: this.state.form});
            });
        }
    }

    renderDepName(name) {
        if (!name || name === '[RootDirectory]' || name === 'RootDirectory') {
            return Lang.t('dep.root');
        }
        return name;
    }

    render() {
        return (
            <form role="dialog" aria-hidden="true" noValidate
                onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }}
            >
                <Modal
                    ref={(elm) => { this.modal = elm; }}
                    events={{
                        'modal.shown': () => { this.onModalShown(); },
                        'modal.hidden': () => { this.onModalHidden(); }
                    }}
                >
                    <Modal.Header>{Lang.t('dep.edit.header')}</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('dep.edit.parent')}</label>
                            <div className="col-sm-8">
                                <div className="input-group" style={{'cursor': 'pointer'}} onClick={() => { this.pickParent(); }}>
                                    <input type="text" className="form-control" readOnly style={{'cursor': 'pointer'}}
                                        value={this.renderDepName(this.state.form.parentDep.name)} />
                                    <div className="input-group-append">
                                        <span className="input-group-text">{Lang.t('dep.edit.choose')}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('dep.edit.name')} <Require /></label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    required
                                    ref={(input) => { this.txtDepName = input; }}
                                    value={this.state.form.name}
                                    onChange={(ev) => { this.state.form.name = ev.target.value; this.setPureState({form: this.state.form}); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('dep.validate.name')}
                                </div>
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('dep.edit.code')} <Require /></label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    required
                                    value={this.state.form.code}
                                    onChange={(ev) => { this.state.form.code = ev.target.value; this.setPureState({form: this.state.form}); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('dep.validate.code')}
                                </div>
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('dep.edit.status')}</label>
                            <div className="col-sm-8">
                                <CheckBox
                                    checked={this.state.form.active}
                                    onChange={(checked) => { this.state.form.active = checked; this.setPureState({form: this.state.form}); }}
                                />
                            </div>
                        </div>

                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('dep.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('dep.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}
