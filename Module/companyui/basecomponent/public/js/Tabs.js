class Tabs extends Component {
    constructor(props) {
        super(props);

        this.state = {
            'tabs': [],
            'className': props.className || "tab-info center-tabs",
            'id': props.id,
            'tabControls': [],
            'active': props.active,
            'tabsRendered': {}
        };


        this.state.tabsRendered[props.active] = true; //tab chưa mở sẽ không được render
    }

    componentWillReceiveProps(newProps) {
        this.state.tabs = [];
        newProps.children.map((tab) => {
            this.addTab(tab);
        });
    }

    addTab(tab) {
        if (tab && tab.length) {
            //if pass array of tabs
            for (var i in tab) {
                this.addTab(tab[i])
            }
        }
        if (!tab || !tab.props || !tab.props.id) {
            return; //check for tab valid
        }

        if (!this.state.active)
            this.state.active = tab.props.id;
        this.state.tabs.push(tab);
    }

    componentDidMount() {
        this.state.tabs = [];
        this.props.children.map((tab) => {
            this.addTab(tab);
        });
        this.setState({});

        this.setState({});
    }


    setActive(tabID) {
        return new Promise((done) => {
            this.state.tabsRendered[tabID] = true;
            this.setState({ 'active': tabID }, () => {
                done();
            });
        });
    }

    getActive() {
        return this.state.active;
    }

    setActiveFirst() {
        if (!this.state.tabs.length)
            return;
        this.setActive(this.state.tabs[0].props.id);
    }

    getActiveClass(tab) {
        var className = '';
        if (tab && tab.props && tab.props.id == this.state.active)
            className = ' show active';
        return className;
    }

    getTabs() {
        return this.state.tabs;
    }

    handleTabClick(ev, tab) {
        ev.preventDefault();
        this.setActive(tab.props.id);
    }

    hasDropdown(tab) {
        return tab && tab.props.children && tab.props.children.type && tab.props.children.type.name == 'Dropdown';
    }

    renderLi(tab) {
        if (!tab || !tab.props)
            return
        var className = "nav-item";
        var hasDropdown = this.hasDropdown(tab);

        if (hasDropdown)
            className += ' dropdown'
        var linkProps = {
            className: 'nav-link' + this.getActiveClass(tab),
            href: hasDropdown ? 'javascript:;' : '#' + tab.props.id
        };
        if (hasDropdown) {
            linkProps.className += ' dropdown-toggle';
            linkProps['data-toggle'] = 'dropdown';
        } else {
            linkProps.onClick = (ev) => { this.handleTabClick(ev, tab); }
        }

        return (<li className={className} key={tab.props.id}>
            {React.createElement('a', linkProps, tab.props.label)}
            {hasDropdown && tab.props.children}
        </li>)
    }

    renderPanel(tab) {

    }

    render() {

        return (
            <div className={this.state.className}>
                <ul className="nav nav-tabs" role="tablist">
                    {this.state.tabs.map((tab) => { return this.renderLi(tab) })}
                </ul>
                <div className="tab-content">
                    {this.state.tabs.map((tab) =>
                        <div role="tabpanel" className={'tab-pane fade in' + this.getActiveClass(tab)} id={tab.props.id} key={tab.props.id}>
                            {('preRender' in this.props || this.state.tabsRendered[tab.props.id]) && <div>
                                {tab.props.children}
                            </div>}
                        </div>
                    )}
                </div>
            </div>
        );
    }

}

class Tab extends Component {
    constructor(props) {
        super(props);
    }

    render() {
        return null;
    }
}