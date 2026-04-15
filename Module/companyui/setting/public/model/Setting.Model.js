Setting.Model = class SettingModel {
    // api lấy danh sách nhóm cấu hình trong quản trị
    getForms(filter) {
        var url = App.url('/rest/setting/forms');
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    //Lấy danh sách các trường tham số theo id nhóm
    getFieldsFormId(id) {
        var url = App.url('/:siteID/rest/settings/forms/:id/fields', {siteID: App.siteID, id: id});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'get'
        });
    }

    // cập nhật trường tham số
    updateValueField(updateData) {
        var url = App.url('/:siteID/rest/settings/forms/fields', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

    //lấy dữ liệu setting name tương ứng
    getDataSetting(data){
        var url = App.url('/:siteID/rest/settings/getData', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'data': data
        });
    }

    // cập nhật cấu hình tích hợp
    updateSettingIntegrate(updateData) {
        var url = App.url('/:siteID/rest/settings/updateIntegrate', {siteID: App.siteID});
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }


}