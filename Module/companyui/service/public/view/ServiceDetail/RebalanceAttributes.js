class RebalanceAttributes extends PureComponent{
    constructor(props) {
        super(props);

        this.state = {
            attrs: props.attrs
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({attrs: nextProps.attrs});
    }

    changeValue(ev, key) {
        this.state.attrs[key] = ev.target.value;
        this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
    }

    render() {
        return (
            <React.Fragment>
                <tr key="startTime">
                    <td>{Lang.t("service.compressionAttributes.field.startTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["startTime"]} onChange={(ev) => this.changeValue(ev, "startTime")}/>
                    </td>
                    <td>{Lang.t("service.description.startTime")}</td>
                </tr>
                <tr key="endTime">
                    <td>{Lang.t("service.compressionAttributes.field.endTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["endTime"]} onChange={(ev) => this.changeValue(ev, "endTime")}/>
                    </td>
                    <td>{Lang.t("service.description.endTime")}</td>
                </tr>

            </React.Fragment>

        )
    }
}