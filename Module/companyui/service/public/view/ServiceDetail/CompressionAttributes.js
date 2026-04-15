class CompressionAttributes extends PureComponent{
    constructor(props) {
        super(props);

        this.storageModel = new StorageModel();
        this.zoneModel = new ZoneModel();

        this.state = {
            attrs: this.props.attrs,
            dicomCompressionType: this.props.dicomCompressionType,
            moveType: this.props.moveType,
            allStorages: [],
            storagesInZone: [],
            zones: []
        }
    }

    initStorage() {
        return new Promise((done) => {

            Promise.all([this.storageModel.getStorages(), this.zoneModel.getZones()]).then(resp => {
                let allStorages = resp[0];
                delete allStorages["version"];

                let zoneResp = resp[1];
                delete zoneResp["version"];

                this.setState({
                    'allStorages': allStorages,
                    'zones': zoneResp
                }, () => {
                    done();
                });
            })
        });
    }

    handleChangeZone(zoneID, keepStorage = false) {
        let storagesInZone = this.state.allStorages.filter(storage => storage.zoneID == zoneID);
        if (storagesInZone.length < 2) {
            $.toast({
                text: Lang.t("service.compressionAttributes.invalidZone"),
                icon: 'error',
                position: 'top-right'
            });
            return;
        }

        this.state.attrs.moveStorageZoneID = zoneID;
        if (!keepStorage) {
            this.state.attrs.moveStorageSrc = storagesInZone[0].id;
            this.state.attrs.moveStorageDst = storagesInZone[1].id;
        }
        this.setPureState({
            attrs: this.state.attrs,
            storagesInZone: storagesInZone
        }, () => {
            this.props.setAttrs(this.state.attrs)
        });
    }

    componentDidMount() {
        this.initStorage().then(resp => {
            let zoneID = this.state.attrs.moveStorageZoneID;
            this.handleChangeZone(zoneID ? zoneID : this.state.zones[0].id, true);
        });
    }

    componentWillReceiveProps(nextProps) {
        this.setState({
            attrs: nextProps.attrs,
            dicomCompressionType: nextProps.dicomCompressionType
        }, () => {

        });
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
                        <input className="w-100" type="text" defaultValue={this.state.attrs["startTime"]} onChange={(ev) => this.changeValue(ev, "startTime")}/>
                    </td>
                    <td>{Lang.t("service.description.startTime")}</td>
                </tr>
                <tr key="endTime">
                    <td>{Lang.t("service.compressionAttributes.field.endTime")}</td>
                    <td>
                        <input className="w-100" type="text" defaultValue={this.state.attrs["endTime"]} onChange={(ev) => this.changeValue(ev, "endTime")}/>
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
                        <input className="w-100" type="text" defaultValue={this.state.attrs["limitTime"]} onChange={(ev) => this.changeValue(ev, "limitTime")}/>
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
                <tr key="moveType">
                    <td>{Lang.t("service.compressionAttributes.field.moveType")}</td>
                    <td>
                        <select className="form-control" id="sel-type" value={this.state.attrs.moveType} onChange={(ev) => {this.state.attrs.moveType = ev.target.value; this.setState({ attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});}}>
                            { this.state.moveType.map( (el) =>
                                <option key={el} value={el}>{el}</option>
                            )}
                        </select>
                    </td>
                    <td>{Lang.t("service.description.moveType")}</td>
                </tr>

                {/*<tr key="numProcessMove">*/}
                {/*    <td>{Lang.t("service.compressionAttributes.field.numProcess")}</td>*/}
                {/*    <td>*/}
                {/*        <input className="w-100" type="text" defaultValue={this.state.attrs["numProcess"]} onChange={(ev) => this.changeValue(ev, "numProcess")}/>*/}
                {/*    </td>*/}
                {/*    <td>{Lang.t("service.description.numProcess")}</td>*/}
                {/*</tr>*/}

                <tr><td><h3 className="mt-1 mb-1">Move storage</h3></td></tr>
                <tr key="moveStorageActive">
                    <td>{Lang.t("service.compressionAttributes.field.moveStorageActive")}</td>
                    <td>
                        <div className="row">
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline3" name="radio-ffm1"
                                           className="custom-control-input" defaultChecked={this.state.attrs["moveStorageActive"] == 1}
                                           onClick={(ev) => {
                                               this.state.attrs["moveStorageActive"] = 1
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline3">{Lang.t("service.compressionAttributes.ok")}</label>
                                </div>
                            </div>
                            <div className="col-sm-6">
                                <div className="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="customRadioInline4" name="radio-ffm1"
                                           className="custom-control-input" defaultChecked={this.state.attrs["moveStorageActive"] != 1}
                                           onClick={(ev) => {
                                               this.state.attrs["moveStorageActive"] = 0
                                               this.setState({attrs: this.state.attrs}, () => {this.props.setAttrs(this.state.attrs)});
                                           }}/>
                                    <label className="custom-control-label" htmlFor="customRadioInline4">{Lang.t("service.compressionAttributes.cancel")}</label>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td/>
                </tr>
                <tr key="moveStorageZoneID">
                    <td>{Lang.t("service.compressionAttributes.field.moveStorageZoneName")}</td>
                    <td>
                        <select className="form-control" id="sel-zoneName"
                                value={this.state.attrs.moveStorageZoneID ? this.state.attrs.moveStorageZoneID : ""}
                                onChange={(ev) => this.handleChangeZone(ev.target.value)}>
                            {this.state.zones.map((zone) =>
                                <option key={zone.id} value={zone.id}>{zone.name}</option>
                            )}
                        </select>
                    </td>
                    <td/>
                </tr>
                <tr key="moveStorageSrc">
                    <td>{Lang.t("service.compressionAttributes.field.moveStorageSrc")}</td>
                    <td>
                        <select className="form-control" id="sel-moveStorageSrc"
                                value={this.state.attrs.moveStorageSrc ? this.state.attrs.moveStorageSrc : ""}
                                onChange={(ev) => this.changeValue(ev, "moveStorageSrc")}>
                            {this.state.storagesInZone.map((storage) =>
                                <option key={storage.id} value={storage.id}>{storage.name}</option>
                            )}
                        </select>
                    </td>
                    <td/>
                </tr>
                <tr key="moveStorageDst">
                    <td>{Lang.t("service.compressionAttributes.field.moveStorageDst")}</td>
                    <td>
                        <select className="form-control" id="sel-destStorage"
                                value={this.state.attrs.moveStorageDst ? this.state.attrs.moveStorageDst : ""}
                                onChange={(ev) => this.changeValue(ev, "moveStorageDst")}>
                            {this.state.storagesInZone.map((storage) =>
                                <option key={storage.id} value={storage.id}>{storage.name}</option>
                            )}
                        </select>
                    </td>
                    <td/>
                </tr>
                <tr key="moveStorageLimitTime">
                    <td>{Lang.t("service.compressionAttributes.field.moveStorageLimitTime")}</td>
                    <td>
                        <input className="w-100" type="text"
                               defaultValue={this.state.attrs["moveStorageLimitTime"]}
                               onChange={(ev) => this.changeValue(ev, "moveStorageLimitTime")}/>
                    </td>
                    <td>
                        {Lang.t("service.description.limitTime").split("\n").map((i,key) => {
                            return <p className="text-dark mb-0" key={key}>{i}</p>;
                        })}
                    </td>
                </tr>
            </React.Fragment>

        )
    }
}