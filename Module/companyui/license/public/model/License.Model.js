License.Model = class licenseModel {

    // lấy danh sách license
    getLicenses(filter) {
        var url = App.url('/:siteID/rest/licenses', { siteID: App.siteID });
        if (filter.loadData) {
            url = url + '?loadData=' + filter.loadData;
        }
        return $.rest({
            'url': url
        });
    }

    // lấy thông tin licese theo id
    getLicense(licenseID, filter) {
        var url = App.url('/:siteID/rest/license/:id', { siteID: App.siteID, id: licenseID });
        if (filter.loadData) {
            url = url + '?loadData=' + filter.loadData;
        }
        return $.rest({
            'url': url
        });
    }

    // đăng ký license
    registerLicense(data) {
        var url = App.url('/:siteID/rest/licenses/register', { siteID: App.siteID });
        return $.rest({
            'url': url,
            'method': 'POST',
            'data': data
        });
    }

    // làm mới license
    refreshLicense(licenseID, data) {
        var url = App.url('/:siteID/rest/license/:id', { siteID: App.siteID, id: licenseID })
        return $.rest({
            'url': url,
            'method': 'POST',
            'data': data
        });
    }

    // tải hardwareID
    downloadHardWareID() {
        var url = App.url('/:siteID/rest/license/downloadHardWareID', { siteID: App.siteID })
        return $.rest({
            'url': url,
            'method': 'POST'
        });
    }

    // dowloadFile
    downloadFile(filename, data) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(data));
        element.setAttribute('download', filename);
        element.style.display = 'none';
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }

    // trả license
    returnLicense(licenseID, data) {
        var url = App.url('/:siteID/rest/license/:id', { siteID: App.siteID, id: licenseID })
        return $.rest({
            'url': url,
            'method': 'DELETE',
            'data': data
        });
    }

    // tự động kiểm tra license
    autoCheckLicense() {
        var url = App.url('/:siteID/rest/licenses/autoCheckLicense', { siteID: App.siteID})
        return $.rest({
            'url': url
        });
    }
}