VersionInfo.Model = class VersionInfoModel {
    // api lấy danh sách modality trong quản trị
    getVersionInfo() {
        var url = App.url('/:siteID/rest/versionInfo', { siteID: App.siteID });
        return $.rest({
            'url': url
        });
    }
}