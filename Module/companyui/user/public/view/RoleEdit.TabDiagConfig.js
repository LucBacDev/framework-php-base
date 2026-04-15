
var tabs = App.Component.getEventState('roleEdit/tab') || []
tabs.push(function (that) {

    that.getListDiagConfig = function () {
        return new Promise((done) => {
            that.roleModel.getListDiagConfig().then((list) => {
                // Danh sách quy trình thực hiện
                that.listDiagConWorkflow = [];
                // Danh sách thao tác xử lý nhanh
                that.listDiagConQuickSelect = [];
                // Danh sách thông tin bổ sung
                that.listDiagConAncillaryInfo = [];

                // lấy danh sách trường quy trình thực hiện
                for (var i in list) {
                    if (list[i].id == 'diagConfigWorkflow') {
                        that.listDiagConWorkflow = list[i].list;
                    } else if (list[i].id == 'diagConfigAncillaryInfo') {
                        that.listDiagConAncillaryInfo = list[i].list;
                    } else {
                        that.listDiagConQuickSelect.push(list[i]);
                    }
                }

                // set giá trị ban đầu cho form attrs
                that.elementAttrs = {
                    'diagConfigWorkflow': 'workflow',
                    'diagConfigQuickSelectMWL': 'quickSelectMWL',
                    'diagConfigHandleAfterApproved': 'handleAfterApproved',
                    'diagConfigCaptureMode': 'captureMode',
                    'diagConfigAncillaryInfo': 'ancillaryInfo',
                };
                for (var i in that.elementAttrs) {
                    if (typeof (that.state.form.attrs[that.elementAttrs[i]]) == 'undefined') {
                        that.state.form.attrs[that.elementAttrs[i]] = that.state.form[that.elementAttrs[i]] ? that.state.form[that.elementAttrs[i]] : [];
                        that.setState({
                            form: that.state.form
                        }, () => {
                            done();
                        });
                    }
                }
            });
        });
    }

    // lấy danh sách tùy chỉnh cấu hình
    that.getListDiagConfig();

    // chọn cấu hình chẩn đoán
    that.toggleCheckDiagConfig = function (checked, data, group) {
        if (checked) {
            // chọn danh sách
            that.state.form.attrs[that.elementAttrs[group]].push(data);
        } else {
            // loại bỏ khi không chọn
            for (var i in that.state.form.attrs[that.elementAttrs[group]]) {
                if (data.id == that.state.form.attrs[that.elementAttrs[group]][i].id) {
                    that.state.form.attrs[that.elementAttrs[group]].splice(i, 1);
                }
            }
        }
        that.setState({ form: that.state.form });
    }

    // checked cấu hình chẩn đoán
    that.hasDiagConfig = function (workflow, group) {
        var list = that.state.form.attrs[that.elementAttrs[group]];
        for (var i in list) {
            if (workflow.id == list[i].id) {
                return true;
            }
        }
        return false;
    }

    // thay đổi giá trị chế độ chụp ảnh Nondicom
    that.handChangeCaptureMode = function (ev) {
        that.state.form.attrs.captureMode = JSON.parse(ev.currentTarget.value);
        that.setState({ form: that.state.form });
    }

    // kiểm tra giá trị chế độ chụp ảnh Nondicom
    that.hasCheckCaptureMode = function (value) {
        if(typeof(that.state.form.attrs.captureMode) != 'undefined'){
            return that.state.form.attrs.captureMode.id == value;
        }
    }

    return (
        <Tab id="tab-diag-config" key="tab-diag-config" label={Lang.t('roleEdit.tabDiagConfig')}>
            <div className="p-h-15 p-v-20">
                <div className="accordion nested" id="accordion-nested" role="tablist">
                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a data-toggle="collapse" href="#diagConfigProcedure" aria-expanded="false" className="collapsed">
                                    <span>{Lang.t('roleEdit.tabDiagConfig.form.diagConfigProcedure')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="diagConfigProcedure" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                <table className="table table-striped table-hover">
                                    <tbody>
                                        {typeof (that.listDiagConWorkflow) != 'undefined' && that.listDiagConWorkflow.map((workflow, idx) =>
                                            <tr key={idx}>
                                                <th>
                                                    <CheckBox
                                                        id={"chk-DiagConfig-Workflow-" + workflow.id}
                                                        checked={that.hasDiagConfig(workflow, workflow.userCusGroupFK)}
                                                        onChange={(checked) => { that.toggleCheckDiagConfig(checked, workflow, workflow.userCusGroupFK); }}
                                                    />
                                                </th>
                                                <td style={{ 'width': '100%' }}>{workflow.name}</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a className="collapsed" data-toggle="collapse" href="#diagConfigQuick" aria-expanded="false">
                                    <span>{Lang.t('roleEdit.tabDiagConfig.form.diagConfigQuick')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="diagConfigQuick" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                {typeof (that.listDiagConQuickSelect) != 'undefined' && that.listDiagConQuickSelect.map((quickSelect, idx) =>
                                    <div key={idx}>
                                        <label><i>{quickSelect.name}</i></label>
                                        <h4></h4>
                                        <table className="table table-striped table-hover">
                                            <tbody>
                                                {quickSelect.id == 'diagConfigCaptureMode' && quickSelect.list && quickSelect.list.map((list, key) =>
                                                    <tr key={key}>
                                                        <th>
                                                            <Input type="radio" name={list.userCusGroupFK} value={JSON.stringify(list)} checked={that.hasCheckCaptureMode(list.id)} onChange={(ev) => { that.handChangeCaptureMode(ev) }} />
                                                        </th>
                                                        <td style={{ 'width': '100%' }}>{list.name}</td>
                                                    </tr>
                                                )}
                                                {quickSelect.id != 'diagConfigCaptureMode' && quickSelect.list && quickSelect.list.map((list, key) =>
                                                    <tr key={key}>
                                                        <th>
                                                            <CheckBox
                                                                id={"chk-" + list.id}
                                                                checked={that.hasDiagConfig(list, list.userCusGroupFK)}
                                                                onChange={(checked) => { that.toggleCheckDiagConfig(checked, list, list.userCusGroupFK); }}
                                                            />
                                                        </th>
                                                        <td style={{ 'width': '100%' }}>{list.name}</td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a className="collapsed" data-toggle="collapse" href="#diagConfigMoreInfo" aria-expanded="false">
                                    <span>{Lang.t('roleEdit.tabDiagConfig.form.diagConfigMoreInfo')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="diagConfigMoreInfo" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                <table className="table table-striped table-hover">
                                    <tbody>
                                        {typeof (that.listDiagConAncillaryInfo) != 'undefined' && that.listDiagConAncillaryInfo.map((ancillaryInfo, idx) =>
                                            <tr key={idx}>
                                                <th>
                                                    <CheckBox
                                                        id={"chk-ancillaryInfo" + ancillaryInfo.id}
                                                        checked={that.hasDiagConfig(ancillaryInfo, ancillaryInfo.userCusGroupFK)}
                                                        onChange={(checked) => { that.toggleCheckDiagConfig(checked, ancillaryInfo, ancillaryInfo.userCusGroupFK); }}
                                                    />
                                                </th>
                                                <td style={{ 'width': '100%' }}>{ancillaryInfo.name}</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Tab>
    );
});
App.Component.trigger('roleEdit/tab', tabs)