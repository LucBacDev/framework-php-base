class LeftNav extends PureComponent {

    constructor(props) {
        super(props);

        this.state = {
            'navs': [],
            'activeNav': '',
            'version': 1,
            'modules': []
        }
        this.groupRefs = {};

        let navs = App.Component.getEventState('PageNavigator') || [];
        this.state.modules = App.Component.getEventState('Module') || [];
        navs.map((nav) => {
            this.addNav(nav);
        });

        this.state.navs = this.sortNav(this.state.navs);
        // this.state.navs = this.state.navs.map(item => {
        //     item.navs = this.sortNav(item.navs);
        //     return item;
        // });

        App.Component.trigger('leftNav.show', false);
    }

    static NavItem = class NavItem extends PureComponent {
        render() {
            return (<li key={this.props.id} className={this.props.id == this.props.activeNav ? 'active' : ''}>
                <a href={this.props.href}>{this.props.name}</a>
            </li>);
        }
    }

    componentDidMount() {
        App.Component.on('PageNavigator', (navs) => {
            navs.map((nav) => {
                this.addNav(nav);
            });
            this.state.navs = this.sortNav(this.state.navs);
            // this.state.navs = this.state.navs.map(item => {
            //     item.navs = this.sortNav(item.navs);
            //     return item;
            // });
            // console.log(JSON.parse(JSON.stringify(this.state.navs)));
            this.setPureState({ 'navs': this.state.navs });
        });

        let activeNav = App.Component.getEventState('leftNav.active');
        if (activeNav) {
            this.setActiveNav(activeNav);
        }
        App.Component.on('leftNav.active', (id) => {
            this.setActiveNav(id);
        })
    }

    addNav(opts) {
        let group = this.getOrAddNavGroup(opts);
        opts.navs.map((nav) => {
            nav.module = opts.module;
            this.addNavToGroup(group, nav);
        })
    }

    addNavToGroup(group, nav) {
        if (!group.refs)
            group.refs = {};
        if (!group.refs[nav.id]) {
            group.refs[nav.id] = nav;
            group.navs.push(nav);
        }
    }

    getOrAddNavGroup(group) {
        var group = $.extend({}, group);
        if (this.groupRefs[group.name])
            return this.groupRefs[group.name];

        group.navs = [];
        this.state.navs.push(group);
        this.groupRefs[group.name] = group; //cache lại theo key value để tăng tốc truy cập

        return group;
    }

    setActiveNav(navID) {
        this.state.navs.map((navGroup) => {
            navGroup.navs.map((nav) => {
                if (nav.id == navID && !navGroup.open) {
                    navGroup.open = true;
                }
            });
        });
        this.setState({ 'activeNav': navID });
    }

    sortNav(items) {
        let result = [];
        this.state.modules.forEach(function(key) {
            var found = false;
            items = items.filter(function(item) {
                if(!found && item['module'] == key) {
                    result.push(item);
                    found = true;
                    return false;
                } else
                    return true;
            })
        })

        return result;
    }

    toggleNavGroup(groupIdx) {
        this.state.navs[groupIdx].open = !this.state.navs[groupIdx].open;
        this.setPureState({ 'navs': this.state.navs });
    }

    render() {
        //merge
        for(let i in this.state.navs) {
            let navA = this.state.navs[i]
            for(let j in this.state.navs) {
                let navB = this.state.navs[j]
                if(i == j)
                    continue;
                if(navA.name == navB.name) {
                    navA.navs = navA.navs.concat(navB.navs)
                    this.state.navs.splice(j, 1)
                }
            }
        }
        return (
            <div className="side-nav expand-lg">
                <div className="side-nav-inner">
                    <ul className="side-nav-menu scrollable">
                        {this.state.navs.map((navGroup, groupIdx) =>
                            <li className={'nav-item dropdown ' + (navGroup.open ? 'open' : '')} key={navGroup.name}>
                                <a className="" href="javascript:void(0);" onClick={() => { this.toggleNavGroup(groupIdx); }}>
                                    <span className="icon-holder">
                                        <i className={navGroup.icon}></i>
                                    </span>
                                    <span className="title">{navGroup.name}</span>
                                    <span className="arrow">
                                        <i className="mdi mdi-chevron-right"></i>
                                    </span>
                                </a>

                                <ul className="dropdown-menu">
                                    {navGroup.navs.map((nav) =>
                                        <LeftNav.NavItem
                                            key={nav.id}
                                            id={nav.id}
                                            name={nav.name}
                                            activeNav={this.state.activeNav}
                                            href={nav.href}
                                        />
                                    )}
                                </ul>
                            </li>
                        )}
                    </ul>
                    {/*<img className="logo-icon" src={App.themeUrl + '/images/logo-text.png'}/>*/}
                </div>
            </div>
        )
    }
}