class CleanDataAttributes extends Component{
    constructor(props) {
        super(props);

        this.state = {
            attrs: this.props.attrs,
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            attrs: nextProps.attrs
        }, () => {
            console.log(this.state)
        });
    }

    changeValue(ev, key) {
        this.state.attrs[key] = ev.target.value;
        this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
    }

    render() {
        return (
            <React.Fragment>
                <tr key="worklistTime">
                    <td>{Lang.t("service.cleanData.field.worklistTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["worklistTime"]} onChange={(ev) => this.changeValue(ev, "worklistTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.worklistTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
                <tr key="logTime">
                    <td>{Lang.t("service.cleanData.field.logTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["logTime"]} onChange={(ev) => this.changeValue(ev, "logTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.logTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
                <tr key="logStudyRisTime">
                    <td>{Lang.t("service.cleanData.field.logStudyRisTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["logStudyRisTime"]} onChange={(ev) => this.changeValue(ev, "logStudyRisTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.logStudyRisTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
                <tr key="cacheInsMetadata">
                    <td>{Lang.t("service.cleanData.field.cacheInsMetadata")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["cacheInsMetadata"]} onChange={(ev) => this.changeValue(ev, "cacheInsMetadata")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.cacheInsMetadata").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
            </React.Fragment>

        )
    }
}