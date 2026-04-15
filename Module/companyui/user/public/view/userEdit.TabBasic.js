App.Component.on('UserEdit/construct', (that) => {


    that.tabUserBasic = () => {
        return (
            <Tab id="tab-user-basic" key="tab-user-basic" label={Lang.t('userEdit.tabBasic')}>
                {that.state && that.state.form && that.state.form.department &&
                    <div className="p-v-20">
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label " htmlFor="txt-user-department">{Lang.t('userEdit.tabBasic.unit')}</label>
                            <div className="col-sm-7">
                                <div className="input-group clickable" onClick={() => { that.pickDep(that.state.form.department); }}>
                                    <input type="text" className="form-control" id="txt-user-department" readOnly
                                        value={that.state.form.department.name} />
                                    <div className="input-group-append">
                                        <span className="input-group-text">{Lang.t('userEdit.tabBasic.chooseDep')}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-u-name">{Lang.t('userEdit.tabBasic.name')} <Require /></label>
                            <div className="col-sm-7">
                                <input type="text" className="form-control clickable" id="txt-u-name"
                                    required
                                    value={that.state.form.fullname}
                                    ref={(elm) => { that.txtName = elm; }}
                                    onChange={(ev) => { that.state.form.fullname = ev.target.value; that.setFormValue(); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('userEdit.tabBasic.validateName')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-u-job-title">{Lang.t('userEdit.tabBasic.postion')}</label>
                            <div className="col-sm-7">
                                <input type="text" className="form-control" id="txt-u-job-title"
                                    value={that.state.form.jobTitle}
                                    onChange={(ev) => { that.state.form.jobTitle = ev.target.value; that.setFormValue(); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-expire">{Lang.t('userEdit.tabBasic.expiryDate')}</label>
                            <div className="col-sm-7">
                                <div className="icon-input expire-date">
                                    <i className="mdi mdi-calendar"></i>
                                    <input id="datepicker-1" data-provide="datepicker" id="txt-expire"
                                        type="text" className="form-control" placeholder={Lang.t("userEdit.TabBasic.selectDate")} />
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-desc">{Lang.t('userEdit.tabBasic.note')}</label>
                            <div className="col-sm-7">
                                <textarea className="form-control" id="txt-desc" rows="2"
                                    value={that.state.form.desc}
                                    onChange={(ev) => { that.state.form.desc = ev.target.value; }}
                                ></textarea>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="chk-user-status">{Lang.t('userEdit.tabBasic.status')}</label>
                            <div className="col-sm-7">
                                <CheckBox id="chk-user-status" ref="ckUserStatus"
                                    checked={that.state.form.active}
                                    onChange={(checked) => { that.state.form.active = checked; that.setFormValue(); }}
                                />
                            </div>
                        </div>
                    </div>
                }
            </Tab>
        );
    };
});