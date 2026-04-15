App.Component.on('UserEdit/construct', (that) => {
    that.tabUserPriv = () => {
        return (
            <Tab id="tab-user-priv" key="tab-user-priv" label={Lang.t('userEdit.tabPriv')}>
                {that.state.privileges.map((privGroup) => <div className="accordion nested" id="accordion-nested" role="tablist" key={privGroup.id}>
                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a data-toggle="collapse" href={'#privgroup-' + privGroup.id} aria-expanded="false" className="collapsed">
                                    <span>{privGroup.name}</span>
                                </a>
                            </h5>
                        </div>
                        <div id={'privgroup-' + privGroup.id} className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                <a href="javascript:;" style={{'display': privGroup.privs.length == 0 ? 'none' : ''}} onClick={() => { that.toggleAllPrivilege(true) }}>{Lang.t('userEdit.tabPriv.checkAll')}</a>
                                <span>&nbsp;&nbsp;&nbsp;</span>
                                <a href="javascript:;" style={{'display': privGroup.privs.length == 0 ? 'none' : ''}} onClick={() => { that.toggleAllPrivilege(false) }}>{Lang.t('userEdit.tabPriv.unChecked')}</a>
                                <h4></h4>
                                <table className="table table-striped table-hover">
                                    <tbody>
                                    {privGroup.privs.map((privilege) =>
                                        <tr key={privilege.id} >
                                            <th>
                                                <CheckBox
                                                    id={"chk-role-privilege-" + privilege.id}
                                                    checked={that.privilegeModel.hasPrivilege(privilege.id, that.state.form.privileges)}
                                                    onChange={(checked) => { that.toggleUserPrivilege(checked, privilege.id); }}
                                                />
                                            </th>
                                            <td style={{ 'width': '100%' }}>{privilege.name}</td>
                                        </tr>
                                    )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>)}

            </Tab>
        );
    }
});