class SystemMonitoringModel {
    // api lấy danh sách AI trong quản trị
    getModules() {
        let url = App.url('/master/rest/system');
        return $.rest({
            'url': url
        });
    }
}