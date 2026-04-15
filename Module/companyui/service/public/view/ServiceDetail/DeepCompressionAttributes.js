class DeepCompressionAttributes extends Component{
    constructor(props) {
        super(props);

        this.state = {
            attrs: this.props.attrs,
            dicomCompressionType: this.props.dicomCompressionType
        }
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            attrs: nextProps.attrs,
            dicomCompressionType: nextProps.dicomCompressionType
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
            <React.Fragment key={this.props.attrs}>
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
                <tr key="dicomCompression">
                    <td>{Lang.t("service.compressionAttributes.field.dicomCompression")}</td>
                    <td>
                        <div className="row">
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline1" name="radio-ffm"
                                           className="custom-control-input" defaultChecked={this.state.attrs["dicomCompression"] == 1}
                                           onClick={(ev) => {
                                               this.state.attrs["dicomCompression"] = 1
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline1">{Lang.t("service.compressionAttributes.ok")}</label>
                                </div>
                            </div>
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline2" name="radio-ffm"
                                           className="custom-control-input" defaultChecked={this.state.attrs["dicomCompression"] != 1}
                                           onClick={(ev) => {
                                               this.state.attrs["dicomCompression"] = 0
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline2">{Lang.t("service.compressionAttributes.cancel")}</label>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td>{Lang.t("service.description.dicomCompression")}</td>
                </tr>
                <tr key="limitTime">
                    <td>{Lang.t("service.compressionAttributes.field.limitTime")}</td>
                    <td>
                        <input type="text" defaultValue={this.state.attrs["limitTime"]} onChange={(ev) => this.changeValue(ev, "limitTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.limitTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
                <tr key="dicomCompressionType">
                    <td>{Lang.t("service.compressionAttributes.field.dicomCompressionType")}</td>
                    <td>
                        <select className="form-control" id="sel-type" value={this.state.attrs.dicomCompressionType} onChange={(ev) => {this.state.attrs.dicomCompressionType = ev.target.value; this.setState({ attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});}}>
                            { this.state.dicomCompressionType.map( (el) =>
                                <option key={el} value={el}>{el}</option>
                            )}
                        </select>
                    </td>
                    <td>{Lang.t("service.description.dicomCompressionType")}</td>
                </tr>
            </React.Fragment>

        )
    }
}