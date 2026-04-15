class ServiceModel {
    getServices() {
        let url = App.url('/:siteID/rest/service',{siteID: App.siteID});

        return $.rest({
            'url': url
        });
    }

    getService(serviceID) {
        let url = App.url('/:siteID/rest/service/:serviceID',{siteID: App.siteID, serviceID: serviceID});

        return $.rest({
            'url': url
        });
    }

    openService(id) {
        var url = App.url('/:siteID/services/:id/processes',{siteID: App.siteID, id: id});

        window.open(url);
    }

    editService(serviceID, attrs) {
        let url = App.url('/:siteID/rest/service/:serviceID',{siteID: App.siteID, serviceID: serviceID});

        return $.rest({
            'url': url,
            'method': 'POST',
            'data': {attrs: attrs}
        });
    }

    getProcesses(serviceID) {
        let url = App.url('/:siteID/rest/processes',{siteID: App.siteID});

        if (serviceID)
            url += "/" + serviceID;

        return $.rest({
            'url': url
        });
    }

    handleProcess(ip, serviceID, processNumber, pid, method) {
        let url = App.url('/:siteID/rest/process/handle',{siteID: App.siteID});

        return $.rest({
            'url': url,
            'data': {
                ip: ip,
                serviceID: serviceID,
                process: processNumber,
                pid: pid,
                method: method
            }
        });
    }
}