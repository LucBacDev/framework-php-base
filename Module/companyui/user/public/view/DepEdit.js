window.depEditComponentIdx = 0;
class DepEdit extends Component {

  constructor(props) {
    super(props);
    this.elmId = 'modal-dep-edit-' + window.depEditComponentIdx++;
    this.state = {
      'form': this.newDepartment(),
      'depID': null
    };
  }

  onModalShown() {
    setTimeout(() => {
      this.txtDepName.focus();
      var form = this.form;
      $(form).removeClass('was-validated');
    });

  }

  onModalHidden() {
    $(this.form).removeClass('was-validated');
  }

  newDepartment() {
    return {
      active: true,
      name: '',
      code: '',
      parentDep: {
        'name': '',
        'id': 0
      },
      desc: ''
    };
  }

  /**
   * 
   * @param {*} department 
   * @return Promise sau khi update
   */
  static open(department) {
    var department = department;
    DepEdit.getInstance().then((instance) => {
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
      DepEdit.instance.done = done || new Function;
    });
  }

  handleSubmit(ev) {
    ev.preventDefault();

    var form = $(this.form);
    $(form).addClass('was-validated');
    if (form[0].checkValidity() === false) {
      return;
    }

    var data = $.extend({}, this.state.form);
    var depModel = new DepModel;
    depModel.updateDep(this.state.depID, data).then((resp) => {
      if (DepEdit.instance.done)
        DepEdit.instance.done(resp);

      this.modal.hideModal();
    }).catch((xhr) => {
      console.log(xhr);
      if (this.editFail)
        this.editFail(xhr);
    });
  }

  pickParent() {
    DepPicker.open({
      'selectedDepID': this.state.form.parentDep.id,
      'not': this.state.depID
    }).then((deps) => {
      this.state.form.parentDep = deps[0];
      this.state.form.parentID = deps[0].id;
      this.setState({});
    });
  }

  renderDepName(name) {
    if (name == "[RootDirectory]") {
      return Lang.t('RootDirectory');
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
          <Modal.Header>{Lang.t('depEdit.header')}</Modal.Header>
          <Modal.Body>
            <div className="form-group row">
              <label className="col-sm-5 col-form-label control-label" htmlFor="txt-dep-parent">{Lang.t('depEdit.unit')}</label>
              <div className="col-sm-7">
                <div className="input-group" style={{ 'cursor': 'pointer' }} onClick={() => { this.pickParent(); }}>
                  <input type="text" className="form-control" id="txt-dep-parent" readOnly style={{ 'cursor': 'pointer' }}
                    value={this.renderDepName(this.state.form.parentDep.name)} />
                  <div className="input-group-append">
                    <span className="input-group-text">{Lang.t('depEdit.chooseDep')}</span>
                  </div>
                </div>
              </div>
            </div>

            <div className="form-group row">
              <label className="col-sm-5 col-form-label control-label" htmlFor="txt-dep-name">{Lang.t('depEdit.depName')} <Require /></label>
              <div className="col-sm-7">
                <input type="text" className="form-control" id="txt-dep-name"
                  required
                  ref={(input) => { this.txtDepName = input; }}
                  value={this.state.form.name}
                  onChange={(event) => { this.state.form.name = event.target.value; this.setState({}); }}
                />
                <div className="invalid-tooltip">
                  {Lang.t('depEdit.validateDepName')}
                </div>
              </div>
            </div>

            <div className="form-group row">
              <label className="col-sm-5 col-form-label control-label" htmlFor="txt-dep-code">{Lang.t('depEdit.depCode')}</label>
              <div className="col-sm-7">
                <input type="text" className="form-control" id="txt-dep-code"
                  required
                  value={this.state.form.code}
                  onChange={(event) => { this.state.form.code = event.target.value; this.setState({}); }} />
                <div className="invalid-tooltip">
                  {Lang.t('depEdit.validateDepCode')}
                </div>
              </div>
            </div>

            <div className="form-group row">
              <label className="col-sm-5 col-form-label control-label" htmlFor="chk-dep-status">{Lang.t('depEdit.status')}</label>
              <div className="col-sm-7">
                <CheckBox id="chk-dep-status"
                  ref={this.chkStatus}
                  checked={this.state.form.active ? true : false}
                  onChange={(checked) => { this.state.form.active = checked; this.setState({}); }} />
              </div>
            </div>

            <div className="form-group row">
              <label className="col-sm-5 col-form-label control-label" htmlFor="txt-dep-desc">{Lang.t('depEdit.note')}</label>
              <div className="col-sm-7">
                <textarea id="txt-dep-desc" className="form-control"
                  onChange={(ev) => { this.state.form.desc = ev.target.value; this.setState({}); }} value={this.state.form.desc}></textarea>
              </div>
            </div>
          </Modal.Body>
          <Modal.Footer>
            <button type="submit" className="btn btn-primary">{Lang.t('depEdit.btnSave')}</button>
            <button type="button" className="btn btn-secondary" onClick={() => { this.modal.hideModal(); }}>{Lang.t('depEdit.btnCancel')}</button>
          </Modal.Footer>
        </Modal>
      </form>
    );
  }
}