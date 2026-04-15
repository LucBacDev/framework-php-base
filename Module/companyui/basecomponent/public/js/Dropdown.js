class Dropdown extends PureComponent {

    constructor(props) {
        super(props);

        this.state = {'className': 'dropdown-menu'}
    }

    getClassName() {
        var c = "dropdown-menu"
        if (this.state.className)
            c += " " + this.state.className
        console.log(this.state.className)
        return c
    }

    componentWillReceiveProps(nextProps) {
        console.log(nextProps)
        this.setState({'className': nextProps.className})
    }

    render() {
        if (this.props.children && this.props.children.map)
            return (<div className={this.getClassName()} container={this.props.container}>
                {this.props.children.map((item) => item)}
            </div>);
        else
            return (<div className={this.getClassName()}  container={this.props.container}>
                {this.props.children}
            </div>);
    }
}

