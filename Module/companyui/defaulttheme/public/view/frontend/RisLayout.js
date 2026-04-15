class RisLayout extends Component {

    constructor(props) {
        super(props);

        this.state = {
            'navShow': App.Component.getEventState('leftNav.show') || false,
        };

    }

    componentDidMount() {
        App.Component.on('leftNav.show', (show) => {
            this.setState({'navShow': show});
        })
    }

    toggleSideBar() {
        var currentState = App.Component.getEventState('leftNav.show');
        App.Component.trigger('leftNav.show', !currentState);
    }

    render() {
        return (
                <div>
                    <HeaderNav />
                
                    {this.props.children}
                </div>
                );
    }
}
