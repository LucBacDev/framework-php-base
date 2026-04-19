class EmployeeEdit extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            'form': this.newEmployee(),
            'empID': null
        };
        this.empModel = new EmployeeModel;
    }

    newEmployee() {
        return {
            active: true,
            fullname: '',
            code: '',
            email: '',
            phone: '',
            position: '',
            depFK: 0,
            department: {
                'name': '',
                'id': 0
            }
        };
    }

    static open(employee) {
        var employee = employee;
        EmployeeEdit.getInstance().then((instance) => {
            employee = $.extend(instance.newEmployee(), employee);

            if (!employee.department) {
                employee.department = {
                    'name': '',
                    'id': 0
                };
            }

            instance.setState({
                'empID': employee.id,
                'form': employee
            });

            instance.modal.showModal();
        });

        return new Promise((done) => {
            EmployeeEdit.instance.done = done || new Function;
        });
    }

    onModalShown() {
        setTimeout(() => {
            this.txtFullname.focus();
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
        this.empModel.updateEmployee(this.state.empID, data).then((resp) => {
            if (EmployeeEdit.instance.done)
                EmployeeEdit.instance.done(resp);
            this.modal.hideModal();
        }).catch((xhr) => {
            Alert.open(xhr.responseJSON ? xhr.responseJSON.message : Lang.t('emp.error.update'));
        });
    }

    pickDepartment() {
        // Sử dụng DepPicker từ companyui/user nếu có
        if (typeof DepPicker !== 'undefined') {
            DepPicker.open({
                'selectedDepID': this.state.form.depFK
            }).then((deps) => {
                this.state.form.department = deps[0];
                this.state.form.depFK = deps[0].id;
                this.setState({});
            });
        }
    }

    renderDepName(dep) {
        if (!dep || !dep.name || dep.name === 'RootDirectory') {
            return Lang.t('emp.edit.noDep');
        }
        return dep.name;
    }

    render() {
        return (
            <form role="dialog" aria-hidden="true" noValidate
                onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }}
            >
                <Modal
                    ref={(elm) => { this.modal = elm; }}
                    size="modal-lg"
                    events={{
                        'modal.shown': () => { this.onModalShown(); },
                        'modal.hidden': () => { this.onModalHidden(); }
                    }}
                >
                    <Modal.Header>{Lang.t('emp.edit.header')}</Modal.Header>
                    <Modal.Body>
                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.fullname')} <Require /></label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    required
                                    ref={(input) => { this.txtFullname = input; }}
                                    value={this.state.form.fullname}
                                    onChange={(ev) => { this.state.form.fullname = ev.target.value; this.setState({}); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('emp.validate.fullname')}
                                </div>
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.code')}</label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    value={this.state.form.code}
                                    onChange={(ev) => { this.state.form.code = ev.target.value; this.setState({}); }}
                                />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.email')}</label>
                            <div className="col-sm-8">
                                <input type="email" className="form-control"
                                    value={this.state.form.email}
                                    onChange={(ev) => { this.state.form.email = ev.target.value; this.setState({}); }}
                                />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.phone')}</label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    value={this.state.form.phone}
                                    onChange={(ev) => { this.state.form.phone = ev.target.value; this.setState({}); }}
                                />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.position')}</label>
                            <div className="col-sm-8">
                                <input type="text" className="form-control"
                                    value={this.state.form.position}
                                    onChange={(ev) => { this.state.form.position = ev.target.value; this.setState({}); }}
                                />
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.department')}</label>
                            <div className="col-sm-8">
                                <div className="input-group" style={{'cursor': 'pointer'}} onClick={() => { this.pickDepartment(); }}>
                                    <input type="text" className="form-control" readOnly style={{'cursor': 'pointer'}}
                                        value={this.renderDepName(this.state.form.department)} />
                                    <div className="input-group-append">
                                        <span className="input-group-text">{Lang.t('emp.edit.chooseDep')}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="form-group row">
                            <label className="col-sm-4 col-form-label control-label">{Lang.t('emp.edit.status')}</label>
                            <div className="col-sm-8">
                                <CheckBox
                                    checked={this.state.form.active}
                                    onChange={(checked) => { this.state.form.active = checked; this.setPureState({form: this.state.form}); }}
                                />
                            </div>
                        </div>

                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('emp.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('emp.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}
