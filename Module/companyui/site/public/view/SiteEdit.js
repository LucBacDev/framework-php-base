class SiteEdit extends PureComponent {
    constructor(props) {
        super(props);

        this.bindThis([
            'onModalShown', 'onModalHidden'
        ]);
        this.state = {
            'form': this.newSite()
        };
        this.siteModel = new SiteModel;
    }

    // mở modal
    static open(site) {
        var site = site;
        SiteEdit.getInstance().then((instance) => {
            instance.modal.showModal();
            site = $.extend(instance.newSite(), site);
            instance.setPureState({
                'form': site,
                'currentSite': $.extend(instance.newSite(), site)
            }, () => {
                //load lại dữ liệu cho chắc
                // if (site.id)
                //     instance.siteModel.getSite(site.id).then((site) => {
                //         instance.setPureState({ 'form': site });
                //     });
            });
        });
        return new Promise((done) => {
            SiteEdit.instance.done = done || new Function;
        });
    }

    newSite() {
        return {
            'id': 0,
            'name': '',
            'shortName': '',
            'active': 1,
            'address': '',
            'phone': ''
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
        this.siteModel.updateSite(this.state.currentSite.id, data).then((resp) => {
            if (resp.status) {
                if (SiteEdit.instance.done)
                    SiteEdit.instance.done(resp);
                window.location.reload();
                //this.modal.hideModal();
            }
        }).catch((xhr) => {
            if (this.editFail)
                this.editFail(xhr);
        });
    }

    handleChangeCheckbox(checked) {
        this.state.form.active = checked;
        this.setPureState({ form: this.state.form });
    }

    handleChangeTag(values) {
        this.state.form.tags = values;
    }

    render() {
        return (
            <form onSubmit={(ev) => { this.handleSubmit(ev); }} ref={(elm) => { this.form = elm; }} noValidate>
                <Modal ref={(elm) => { this.modal = elm; }} events={{
                    'modal.shown': this.onModalShown,
                    'modal.hidden': this.onModalHidden
                }}>
                    <Modal.Header>{Lang.t('site.header')}</Modal.Header>
                    <Modal.Body>
                        {/* <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-siteID">ID site <Require /></label>
                            <div className="col-sm-7">
                                <Input type="code" className="form-control" id="txt-siteID"
                                    required
                                    ref={(elm) => { this.siteID = elm; }}
                                    value={this.state.form.id}
                                    onChange={(ev) => { this.state.form.id = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('site.validateID')}
                                </div>
                            </div>
                        </div> */}
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.name')} site <Require /></label>
                            <div className="col-sm-7">
                                <input type="text" className="form-control" id="txt-nameSite" ref={(elm) => { this.nameSite = elm; }}
                                    required='required'
                                    value={this.state.form.name}
                                    onChange={(ev) => { this.state.form.name = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                                <div className="invalid-tooltip">
                                    {Lang.t('site.validateName')}
                                </div>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="chk-site-status">{Lang.t('site.tags')}</label>
                            <div className="col-sm-7">
                                <SiteTagSelect
                                    onChange={(values) => { this.handleChangeTag(values); }}
                                    selected={this.state.form.tags}
                                ></SiteTagSelect>
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.address')}</label>
                            <div className="col-sm-7">
                                <input type="text" className="form-control" id="txt-address-site"
                                       value={this.state.form.address}
                                       onChange={(ev) => { this.state.form.address = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="txt-login">{Lang.t('site.phone')}</label>
                            <div className="col-sm-7">
                                <input type="text" className="form-control" id="txt-phone-site"
                                       value={this.state.form.phone}
                                       onChange={(ev) => { this.state.form.phone = ev.target.value; this.setPureState({ form: this.state.form }); }}
                                />
                            </div>
                        </div>
                        <div className="form-group row">
                            <label className="col-sm-5 col-form-label control-label" htmlFor="chk-site-status">{Lang.t('site.sttAction')}</label>
                            <div className="col-sm-7">
                                <CheckBox id="chk-site-status"
                                          checked={this.state.form.active}
                                          onChange={(checked) => { this.handleChangeCheckbox(checked); }}
                                />
                            </div>
                        </div>

                    </Modal.Body>
                    <Modal.Footer>
                        <button type="submit" className="btn btn-primary">{Lang.t('site.btnSave')}</button>
                        <button type="button" className="btn btn-secondary" data-dismiss="modal">{Lang.t('site.btnClose')}</button>
                    </Modal.Footer>
                </Modal>
            </form>
        );
    }
}