class UploadAIAttributes extends Component{
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
                <tr key="removePatientInformation">
                    <td>{Lang.t("service.uploadAIAttributes.field.removePatientInformation")}</td>
                    <td>
                        <div className="row">
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline1" name="radio-ffm"
                                           className="custom-control-input" defaultChecked={this.state.attrs["removePatientInformation"] == 1}
                                           onClick={(ev) => {
                                               this.state.attrs["removePatientInformation"] = 1
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline1">{Lang.t("service.uploadAIAttributes.yes")}</label>
                                </div>
                            </div>
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline2" name="radio-ffm"
                                           className="custom-control-input" defaultChecked={this.state.attrs["removePatientInformation"] != 1}
                                           onClick={(ev) => {
                                               this.state.attrs["removePatientInformation"] = 0
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline2">{Lang.t("service.uploadAIAttributes.no")}</label>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td/>
                </tr>
            </React.Fragment>

        )
    }
}