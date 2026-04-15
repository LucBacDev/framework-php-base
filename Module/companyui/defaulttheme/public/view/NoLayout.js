class NoLayout extends Component {
    render() {
        return (
            <div className="app">
                {this.props.children}
            </div>
        );
    }
}