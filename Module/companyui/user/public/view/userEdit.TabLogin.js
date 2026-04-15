App.Component.on('UserEdit/construct', (that) => {
    that.tabUserLogin = () => {
        if(!that.state.form || !that.state.form.login)
            return (<div>Not ready</div>)
        console.log(that.state.form)
        return (
            <Tab id="tab-user-login" key="tab-user-login" label={Lang.t('userEdit.tabLogin')}>
                {(that.state.form.id == 0 || that.state.form.userLinkID == that.state.form.id) && <div className="p-v-20">
                    <div className="form-group row">
                        <label className="col-sm-5 col-form-label control-label" htmlFor="txt-login">{Lang.t('userEdit.tabLogin.account')} <Require /></label>
                        <div className="col-sm-7">
                            <input type="text" className="form-control" id="txt-login" ref={(elm) => { that.txtLogin = elm; }}
                                   required='required'
                                   value={that.state.form.login.localdb.account}
                                   onChange={(ev) => { that.state.form.login.localdb.account = ev.target.value; that.setFormValue(); }}
                            />
                            <div className="invalid-tooltip">
                                {Lang.t('userEdit.tabLogin.validateAccount')}
                            </div>
                        </div>
                    </div>
                    <div className="form-group row">
                        <label className="col-sm-5 col-form-label control-label" htmlFor="txt-password"> {Lang.t('userEdit.tabLogin.password')} {!that.state.form.id ? <Require /> : ''}</label>
                        <div className="col-sm-7">
                            <Input type="password" className="form-control" id="txt-password"
                                   required={!that.state.form.id ? true : false} ref={(elm) => { that.txtPassword = elm; }}
                                   ref={(elm) => { that.txtPassword = elm; }}
                                   value={that.state.form.login.localdb.password}
                                   onChange={(ev) => { that.state.form.login.localdb.password = ev.target.value; that.setFormValue(); }}
                            />
                            <div className="invalid-tooltip">
                                {Lang.t('userEdit.tabLogin.validatePass')}
                            </div>
                        </div>
                    </div>
                    <div className="form-group row">
                        <label className="col-sm-5 col-form-label control-label" htmlFor="txt-re-password"> {Lang.t('userEdit.tabLogin.rePassword')} {!that.state.form.id ? <Require /> : ''}</label>
                        <div className="col-sm-7">
                            <Input type="password" className="form-control" id="txt-re-password"
                                   ref={(elm) => { that.txtRePassword = elm; }}
                                   value={that.state.form.login.localdb.repassword}
                                   onChange={(ev) => { that.handleChangeRePassword(ev); }}
                            />
                            <div className="invalid-tooltip">
                                {Lang.t('userEdit.tabLogin.validateRePass')}
                            </div>
                        </div>
                    </div>

                </div>}
                {!that.state.form.id || that.state.form.userLinkID != that.state.form.id && <div className="p-v-20">
                    {Lang.t('userEdit.tabLogin.userLinkedCantChangePass')}:<br/>
                    <b>{that.state.form.userLinkID}</b>
                </div>}

            </Tab>
        );
    }
});