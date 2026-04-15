class ModuleList extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            modules: []
        };

        this.getModules().then((modules) => {
            this.state.modules = modules;
        });
    }

    getModules() {
        return new Promise((done) => {
            done([
                {id: 'test/test', version: '1.0.0', 'desc': 'Mockup ui, must rewrite'}
            ]);
        });
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'modules');
    }

    render() {
        return <AdminLayout>

            <PageHeader>{Lang.t("module.header")}</PageHeader>
            <div className="card">
                <div className="card-body">
                    <div className="input-group" style={{maxWidth: '300px'}}>
                        <div className="input-group-prepend">
                            <span className="input-group-text"><i className="ti-search"></i></span>
                        </div>
                        <input type="text" className="form-control" placeholder={Lang.t("module.placeholder.search")}/>
                    </div>
                    <h4></h4>
                    <Datagrid
                        dataset={this.state.modules}
                        rowKey={(row) => {
                            return row.id;
                        }}
                    >
                        <Datagrid.Col
                            id="id"
                            thead={Lang.t("module.id")}
                            render={(row) => {
                                return row.id;
                            }}
                        />
                        <Datagrid.Col
                            id="version"
                            thead={Lang.t("module.version")}
                            render={(row) => {
                                return row.version;
                            }}
                        />
                        <Datagrid.Col
                            id="desc"
                            thead={Lang.t("module.desc")}
                            render={(row) => {
                                return row.desc;
                            }}
                        />
                    </Datagrid>
                </div>
            </div>


        </AdminLayout>;
    }
}