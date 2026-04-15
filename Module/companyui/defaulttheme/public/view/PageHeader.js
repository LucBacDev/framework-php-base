class PageHeader extends Component {
    render() {
        return (
            <div className="page-header">
                <h2 className="header-title">{this.props.children}</h2>
            </div>
        );
    }
}