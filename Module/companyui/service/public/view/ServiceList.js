class ServiceList extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            "services": []
        }

        this.model = new ServiceModel();
    }

    getServices() {
        return new Promise((done) => {
            Promise.all([this.model.getServices(), this.model.getProcesses()]).then(data => {
                let services = data[0];
                let processes = data[1];
                delete processes["version"];
                let processesMap = {};
                Object.keys(processes).forEach(ip => {
                    let services = processes[ip];

                    Object.entries(services).forEach(([serviceID, serviceInfo]) => {
                        if (!(serviceID in processesMap))
                            processesMap[serviceID] = {
                                running: 0,
                                total: 0
                            }

                        for (const service of serviceInfo) {
                            if (service["pid"] > 0)
                                processesMap[serviceID].running += 1;

                            processesMap[serviceID].total += 1;
                        }
                    })

                });

                let res = [];
                services.forEach(item => {
                    let id = item["id"];
                    if (id in processesMap)
                        item["numProcess"] = `${processesMap[id].running}/${processesMap[id].total}`;
                    else
                        item["numProcess"] = "0/0"

                    res.push(item);
                });

                this.setPureState({services: res}, done);
            })
        });
    }

    openService(id) {
        this.model.openService(id);
    }

    componentDidMount() {
        App.requireLogin();
        this.getServices();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'ServiceList');
    }

    handleOpenDetail(ev, service) {
        ServiceEdit.open(service).then(resp => {
            this.getServices();
        });
    }

    pageContent() {
        return (
            <div>
                <PageHeader>{Lang.t('service.header')}</PageHeader>

                <div className="card">
                    <div className="card-body">
                        <div className="table-responsive">
                            <table className="table table-striped" ref={(elm) => {
                                this.table = elm;
                            }}>
                                <thead>
                                <tr>
                                    <th>{Lang.t("service.id")}</th>
                                    <th>{Lang.t("service.name")}</th>
                                    <th>{Lang.t("service.numProcess")}</th>
                                    {/*<th>{Lang.t("service.command")}</th>*/}
                                    <th/>
                                </tr>
                                </thead>
                                <tbody>
                                {this.state.services.map((service) =>
                                    <tr key={service.id}>
                                        <td><a href="javascript:;" onClick={() => this.openService(service.id)}>{service.id}</a></td>
                                        <td>{service.name}</td>
                                        <td>{service.numProcess}</td>
                                        {/*<td>{service.command}</td>*/}
                                        <td>
                                            {
                                                <i className="fa fa-list-ul" aria-hidden="true"
                                                   onClick={(ev) => this.handleOpenDetail(ev, service)}/>
                                            }
                                        </td>
                                    </tr>
                                )}

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    render() {

        return (
            <AdminLayout>
                {this.pageContent()}
            </AdminLayout>
        );
    }
}