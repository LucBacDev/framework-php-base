
var tabs = App.Component.getEventState('roleEdit/tab') || [];

tabs.push(function (that) {

    that.getListCustomDisplay = function () {
        return new Promise((done) => {
            that.roleModel.getListCustomDisplay().then((list) => {
                that.listCusDspList = [];
                that.listCusDspStatus = [];
                that.listCusDspHighlight = [];

                // lấy danh sách trường dicom và worklist
                for (var i in list) {
                    // HighLight
                    if (list[i].id == 'customDisplayStatus' || list[i].id == 'customDisplayBurnDisc' || list[i].id == 'customDisplayPriority') {
                        that.listCusDspHighlight.push(list[i]);
                    }
                    if (list[i].id == 'customDisplayDicom' || list[i].id == 'customDisplayWorklist') {
                        that.listCusDspList.push(list[i]);
                    } else {
                        that.listCusDspStatus.push(list[i]);
                    }
                }

                // set giá trị ban đầu cho form attrs
                var elementAttrs = ['cusDspList', 'cusDspStatus', 'cusDspHighlight'];
                for (var i in elementAttrs) {
                    if (typeof (that.state.form.attrs[elementAttrs[i]]) == 'undefined') {
                        that.state.form.attrs[elementAttrs[i]] = that.state.form[elementAttrs[i]] ? that.state.form[elementAttrs[i]] : [];
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
    that.getListCustomDisplay();

    // chọn hiển thị trạng thái
    that.toggleCustomDisplayStatus = function (checked, customDisplayStatus) {
        if (checked) {
            // chọn danh sách
            that.state.form.attrs.cusDspStatus.push(customDisplayStatus);
        } else {
            // loại bỏ khi không chọn
            for (var i in that.state.form.attrs.cusDspStatus) {
                if (customDisplayStatus.id == that.state.form.attrs.cusDspStatus[i].id) {
                    that.state.form.attrs.cusDspStatus.splice(i, 1);
                }
            }
        }
        that.setState({ form: that.state.form });
    }

    // checked hiển thị trạng thái
    that.hasCustomDisplayStatus = function (customDisplayStatus) {
        var listCusDspChecked = that.state.form.attrs.cusDspStatus;
        for (var i in listCusDspChecked) {
            if (customDisplayStatus.id == listCusDspChecked[i].id) {
                return true;
            }
        }
        return false;
    }

    that.handleChangeSelectBox = function (listSelected) {
        // thêm trường ordinalNum 
        for (var i in listSelected) {
            listSelected[i].ordinalNum = parseInt(i) + 1;
        }
        that.state.form.attrs.cusDspList = listSelected;
        that.setState({ form: that.state.form });
    }

    that.handleChangeHighlight = function (listSelected) {
        that.state.form.attrs.cusDspHighlight = listSelected;
        that.setState({ form: that.state.form });
    }

    return (
        <Tab id="tab-custom-display" key="tab-custom-display" label={Lang.t('roleEdit.tabCustomDisplay')}>
            <div className="p-h-15 p-v-20">
                <div className="accordion nested" id="accordion-nested" role="tablist">
                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a data-toggle="collapse" href="#customDisplayWorklist" aria-expanded="false" className="collapsed">
                                    <span>{Lang.t('roleEdit.tabCustomDisplay.form.customDisplayWorklist')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="customDisplayWorklist" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                {
                                    typeof (that.listCusDspList) != 'undefined' &&
                                    <MultipleSelectBox
                                        list={that.listCusDspList}
                                        listSelected={that.state.form.attrs.cusDspList}
                                        onChange={(listSelected) => { that.handleChangeSelectBox(listSelected) }}
                                        selectGroup
                                    />
                                }
                            </div>
                        </div>
                    </div>
                    <div className="card">
                        <div className="card-header" role="tab">
                            <h5 className="card-title">
                                <a className="collapsed" data-toggle="collapse" href="#customDisplayDicom" aria-expanded="false">
                                    <span>{Lang.t('roleEdit.tabCustomDisplay.form.customDisplayDicom')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="customDisplayDicom" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                {typeof (that.listCusDspStatus) != 'undefined' && that.listCusDspStatus.map((status, idx) =>
                                    <div key={idx}>
                                        <label><i>{status.name}</i></label>
                                        <h4></h4>
                                        <table className="table table-striped table-hover">
                                            <tbody>
                                                {status.list && status.list.map((list, key) =>
                                                    <tr key={key}>
                                                        <th>
                                                            <CheckBox
                                                                id={"chk-CustomDiplay-list-" + list.id}
                                                                checked={that.hasCustomDisplayStatus(list)}
                                                                onChange={(checked) => { that.toggleCustomDisplayStatus(checked, list); }}
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
                                <a data-toggle="collapse" href="#customDisplayHighlight" aria-expanded="false" className="collapsed">
                                    <span>{Lang.t('roleEdit.tabCustomDisplay.form.customDisplayHighLight')}</span>
                                </a>
                            </h5>
                        </div>
                        <div id="customDisplayHighlight" className="collapse" data-parent="#accordion-nested" >
                            <div className="card-body">
                                {
                                    typeof (that.listCusDspHighlight) != 'undefined' &&
                                    <Highlight
                                        list={that.listCusDspHighlight}
                                        listHighLightSelected={that.state.form.attrs.cusDspHighlight}
                                        onChange={(listSelected) => { that.handleChangeHighlight(listSelected) }}
                                        selectGroup
                                    />
                                }
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Tab>
    );
});
App.Component.trigger('roleEdit/tab', tabs)