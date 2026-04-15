class TopNav extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            langLoaded: false
        }

        Lang.load("companyui", "defaulttheme").then(() => {
            this.setState({
                langLoaded: true
            });
        });
    }

    componentDidMount() {
       // App.Component.trigger('TopNav/load', [this]);
    }
    toggleSideBar() {
        var currentState = App.Component.getEventState('leftNav.show');
        App.Component.trigger('leftNav.show', !currentState);
    }

    handleLogout() {
        let url = App.url('/rest/auth');
        $.ajax({
            'url': url,
            'method': "DELETE"
        }).done(r => {
            window.location.href = App.url("/auth/login");
        });
    }

    handleChangePassword() {
        EditPasswordModal.open().then(() => {});
    }

    render() {
        if (!this.state.langLoaded)
            return null;

        return (
            <div className="header navbar">
                <div className="header-container">
                    <div className="nav-logo">
                        <a href="index.html">
                            
                        </a>
                    </div>
                    <ul className="nav-left">
                        <li>
                            <a className="sidenav-fold-toggler" href="javascript:void(0);" onClick={() => {this.toggleSideBar()}}>
                                <i className="mdi mdi-menu"></i>
                            </a>
                            <a className="sidenav-expand-toggler" href="javascript:void(0);">
                                <i className="mdi mdi-menu"></i>
                            </a>
                        </li>
                        <li>
                            <a href={App.siteUrl + '/' + App.siteID + '/users/sites'}>{Lang.t("topNav.selectSite")}</a>
                        </li>
                       
                    </ul>
                    <ul className="nav-right">
                        <li className="user-profile dropdown dropdown-animated scale-left">
                            <a href="#" className="dropdown-toggle" data-toggle="dropdown">
                                <img className="profile-img img-fluid" alt="" src={App.themeUrl + '/images/anonymous.png'} />
                                &nbsp;
                                        <span className="user-name">{App.getUser().fullname}</span>
                            </a>
                            <ul className="dropdown-menu dropdown-md p-v-0">
                                <li>
                                    <ul className="list-media">
                                        <li className="list-item p-15">
                                            <div className="media-img">
                                                <img src={App.themeUrl + '/images/anonymous.png'} alt="" />
                                            </div>
                                            <div className="info">
                                                <span className="title text-semibold">{App.getUser().fullname}</span>
                                                {App.getUser().jobTitle
                                                    && <span className="sub-title">{App.getUser().jobTitle}</span>}
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li role="separator" className="divider"></li>
                                <li>
                                    <a href="#" onClick={() => this.handleChangePassword()}>
                                        <i className="ti-key p-r-10"></i>
                                        <span>{Lang.t("topNav.changePassword")}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href={App.siteUrl + '/' + App.siteID + '/users/sites/merge'}>
                                        <i className="fa fa-retweet p-r-10"></i>
                                        <span>{Lang.t("topNav.mergeAccount")}</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" onClick={() => this.handleLogout()}>
                                        <i className="ti-power-off p-r-10"></i>
                                        <span>{Lang.t("topNav.signOut")}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        );
    }
}