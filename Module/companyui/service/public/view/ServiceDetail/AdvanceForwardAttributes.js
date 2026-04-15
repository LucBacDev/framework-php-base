class AdvanceForwardAttributes extends Component{
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
                <tr key="advanceForwardStartTime">
                    <td>{Lang.t("service.advanceForward.field.startTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["startTime"]} onChange={(ev) => this.changeValue(ev, "startTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.startTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>

                <tr key="advanceForwardEndTime">
                    <td>{Lang.t("service.advanceForward.field.endTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["endTime"]} onChange={(ev) => this.changeValue(ev, "endTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.endTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
            </React.Fragment>

        )
    }
}