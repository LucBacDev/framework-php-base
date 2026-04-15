class EditPasswordModal extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            "oldPassword": "",
            "newPassword": "",
            "rePassword": ""
        }
    }

    static open() {
        EditPasswordModal.getInstance().then((instance) => {
            instance.modal.showModal();
        });

        return new Promise((done) => {
            EditPasswordModal.instance.done = done || new Function;
        });
    }

    handleChangeRePassword(ev) {
        this.setState({rePassword: ev.target.value}, () => {
            if (this.state.newPassword !== this.state.rePassword)
                this.txtRePassword.setValid(false);
            else
                this.txtRePassword.setValid(true);
        });
    }

    onModalShown() {
        this.setState({
            "oldPassword": "",
            "newPassword": "",
            "rePassword": ""
        });
    }

    onModalHidden() {

    }

    handleSubmit() {
        let loginData = App.user.login.localdb;

        (new UserModel).changePassword({
            account: loginData.account,
            type: loginData.type,
            oldPassword: this.state.oldPassword,
            newPassword: this.state.newPassword
        }).then((resp) => {
           if (EditPasswordModal.instance.done)
               EditPasswordModal.instance.done(resp);

           this.modal.hideModal();

        }).catch((xhr) => {

        });
    }

    render() {
        return (
            <Modal
                ref={(elm) => { this.modal = elm; }}
                events={{
                    'modal.shown':  () => { this.onModalShown(); },
                    'modal.hidden': () => { this.onModalHidden(); }
                }}>
                <Modal.Header>{Lang.t('editPassword.title')}</Modal.Header>
                <Modal.Body>
                    <div className="p-v-20">
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-login">{Lang.t('editPassword.oldPassword')} <Require /></label>
                            <div className="col-sm-7">
                                <Input type="password" className="form-control" id="txt-login"
                                       required='required'
                                       value={this.state.oldPassword}
                                       onChange={(ev) => { this.setState({oldPassword: ev.target.value});}}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('userEdit.tabLogin.validatePass')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-password"> {Lang.t('editPassword.newPassword')} <Require /></label>
                            <div className="col-sm-7">
                                <Input type="password" className="form-control" id="txt-password"
                                       required='required'
                                       value={this.state.newPassword}
                                       onChange={(ev) => { this.setState({newPassword: ev.target.value});}}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('userEdit.tabLogin.validatePass')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-re-password"> {Lang.t('editPassword.rePassword')} <Require /> </label>
                            <div className="col-sm-7">
                                <Input type="password" className="form-control" id="txt-re-password"
                                       ref={(elm) => { this.txtRePassword = elm; }}
                                       value={this.state.rePassword}
                                       onChange={(ev) => { this.handleChangeRePassword(ev); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('userEdit.tabLogin.validateRePass')}
                                </div>
                            </div>
                        </div>

                    </div>
                </Modal.Body>
                <Modal.Footer>
                    <button type="submit" className="btn btn-primary" onClick={() => this.handleSubmit()}>{Lang.t('userEdit.btnSave')}</button>
                    <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('userEdit.btnCancel')}</button>
                </Modal.Footer>
            </Modal>
        );
    }
}