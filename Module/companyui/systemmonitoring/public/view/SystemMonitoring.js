class SystemMonitoring extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            'modules': [],
            'modulesDisplay': []
        }
        this.statuses = ["Healthy","Warning","Error", "Unknown"];
        this.model = new SystemMonitoringModel();
    }

    componentDidMount() {
        App.requireLogin();
        this.getModules();
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'SystemMonitoring');
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    getTextAndColor(status) {
        let text = "Unknown";
        let color = "gray"

        if (status !== false) {
            if (status === 0) {
                text = "Healthy";
                color = "green";
            } else if (1 <= status && status <= 9) {
                text = "Warning";
                color = "yellow";
            } else {
                text = "Error";
                color = "red";
            }
        }

        return [text, color];
    }

    renderStatus(status) {
        const [text, color] = this.getTextAndColor(status);

        return (
            <React.Fragment>
                <div style={{width: "65px", display:'inline-block'}}>{text}</div>
                <i className="fa fa-circle" style={{color: color}}></i>
            </React.Fragment>
        )
    }

    getModules() {
        return new Promise((done) => {

            this.model.getModules().then((resp) => {
                this.setState({'modules': resp, 'modulesDisplay': resp});
            });
        });
    }

    handleChangeStatus(values) {
        if (values.length === 0) {
            this.setState({'modulesDisplay': this.state.modules});
            return;
        }

        let sysModules = this.state.modules.filter((module) => {
            const [text, _] = this.getTextAndColor(module.status);
            return values.includes(text);
        })

        this.setState({'modulesDisplay': sysModules});
    }

    render() {
        return (
            <AdminLayout>
                <div>
                    <PageHeader>{Lang.t('systemmonitoring.header')}</PageHeader>
                    <div className="d-flex justify-content-between mb-3">
                        <div className="input-group" style={{width: "30%"}}>
                            <div className="input-group-prepend">
                                <button className="btn btn-outline-secondary" type="button"><i
                                    className="fa fa-search" aria-hidden="true"></i></button>
                            </div>
                            <input type="text" className="form-control" placeholder="" aria-label=""
                                   aria-describedby="basic-addon1"/>
                        </div>
                        <div style={{width: "30%"}}>
                            <Chosen multiple
                                    placeholder="status"
                                    onChange={(values) => {this.handleChangeStatus(values)}}>
                                {this.statuses.map(status =>
                                    <option key={status}>{status}</option>
                                )}
                            </Chosen>
                        </div>
                    </div>
                    <table className="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">{Lang.t("systemmonitoring.moduleName")}</th>
                            <th scope="col">{Lang.t("systemmonitoring.status")}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {this.state.modulesDisplay.map((module, index) =>
                            <tr key={index}>
                                <th scope="row">{index+1}</th>
                                <td>{module.name}</td>
                                <td>
                                    {this.renderStatus(module.status)}
                                </td>
                            </tr>
                        )}
                        </tbody>
                    </table>
                </div>
            </AdminLayout>
        );
    }
}