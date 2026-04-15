class HeaderNav extends PureComponent {
    constructor(props) {
        super(props);
    }

    componentDidMount() {
       // App.Component.trigger('TopNav/load', [this]);
    }

    render() {
        return (
            <nav className="navbar navbar-expand-lg navbar-light bg-light navbar-miner">
                <a className="navbar-brand logo mr-auto" href="#" />
                <button className="btn btn-default btn-sm btn-left active" data-original-title="Một màn hình" data-placement="bottom" data-html="true" data-toggle="tooltip">
                    <div className="icon-single-screen-active"/>
                </button>

                <button className="btn btn-default btn-sm btn-nav">
                  <img src="images/light/icons/folder-close.png" />
                  {/* <i class="fa fa-bar-chart" aria-hidden="true"></i> */}
                </button>
                <button className="btn btn-default btn-sm btn-nav btn-screen active">
                  <img src="images/dark/icons/screen.png" />
                  {/* <i class="fa fa-bar-chart" aria-hidden="true"></i> */}
                </button>
                <button className="btn btn-default btn-sm btn-nav">
                  <img src="images/light/icons/splipScreen.png" />
                  {/* <i class="fa fa-bar-chart" aria-hidden="true"></i> */}
                </button>
                <button className="btn btn-default btn-sm btn-nav">
                  <img src="images/light/icons/chart.png" />
                  {/* <i class="fa fa-bar-chart" aria-hidden="true"></i> */}
                </button>
                <div className="float-right div-slt-site">
                  <select className="slt-site">
                    <option>Đại học Y</option>
                    <option>Bạch mai</option>
                  </select>
                </div>
                <div className="float-right">
                  <img src="images/light/icons/account.png" className="icon-account" />
                  <button className="btn btn-default btn-sm dropdown-toggle btn-account" data-toggle="dropdown">
                    <i className="glyphicon glyphicon-user" /><span className="hidden-sm hidden-xs"> Bs. Nguyễn Mạnh Hà</span>
                    <span className="caret" />
                  </button>
                </div>
            </nav>
        );
    }
}