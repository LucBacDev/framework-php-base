class AdminLayout extends Component {

    constructor(props) {
        super(props);

        this.state = {
            'navShow': App.Component.getEventState('leftNav.show') || false,
        };

    }

    getAppClass() {
        var className = "app header-success-gradient";
        if (this.state.navShow) {
            className += " side-nav-folded";
        }

        return className;
    }
    getBackDropClass() {
        if (!this.state.navShow) {
            var className = "side-nav-backdrop";
        }
        return className;
    }

    componentDidMount() {
        App.Component.on('leftNav.show', (show) => {
            this.setState({ 'navShow': show });
        })
    }

    toggleSideBar() {
        var currentState = App.Component.getEventState('leftNav.show');
        App.Component.trigger('leftNav.show', !currentState);
    }

    render() {
        return (
            <div className={this.getAppClass()}>
                <div className="layout">
                    <TopNav />

                    <LeftNav />

                    <div className="page-container">
                        <div className="main-content">
                            <div className="container-fluid">
                                {this.props.children}
                            </div>
                        </div>

                        <footer className="content-footer">
                            <div className="footer">
                                <div className="copyright">
                                    <span>Copyright © 2018
                            <b className="text-dark">Theme_Nate</b>. All rights reserved.</span>
                                    <span className="go-right">
                                        <a href="#" className="text-gray m-r-15">Term &amp; Conditions</a>
                                        <a href="#" className="text-gray">Privacy &amp; Policy</a>
                                    </span>
                                </div>
                            </div>
                        </footer>
                    </div>

                </div>
                <div onClick={() => {this.toggleSideBar()}} className={this.getBackDropClass()}></div>
            </div>
        );
    }
}
