class SyncElasticAttributes extends Component{
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
                <tr key="numProcessElastic">
                    <td>{Lang.t("service.compressionAttributes.field.numProcess")}</td>
                    <td>
                        <input className="w-100" type="text" defaultValue={this.state.attrs["numProcess"]} onChange={(ev) => this.changeValue(ev, "numProcess")}/>
                    </td>
                    <td>{Lang.t("service.description.numProcess")}</td>
                </tr>
            </React.Fragment>

        )
    }
}