class DatagridCol extends Component {

}

class Datagrid extends Component {
    constructor(props) {
        super(props);

        if (!props.rowKey) {
            console.error("Datagrid props must have rowKey function");
        }
        this.convertPropsToState(props);
    }

    static Col = DatagridCol;

    convertPropsToState(props) {
        this.state = $.extend(this.state || {}, {
            'className': props.className || 'table table-hover table-striped',
            'style': props.style || {},
            'cols': props.children,
            'dataset': props.dataset,
            'rowKey': props.rowKey,
            'sort': props.sort
        });
    }

    compareArray(a, b) {
        a = a || [];
        b = b || [];
        if (a.length != b.length)
            return false;
        if (a.version && b.version)
            return a.version == b.version;
        //nên hạn chế so sánh toàn bộ
        return JSON.stringify(a) == JSON.stringify(b);
    }

    componentWillReceiveProps(newProps) {
        this.convertPropsToState(newProps);
        this.setState({});
    }

    toggleSort(col) {
        var sortOrder;
        if (!this.state.sort)
            sortOrder = 1;
        else
            sortOrder = this.state.sort[col.props.id] == 1 ? -1 : 1;

        this.trigger('sort', [col.props.id, sortOrder]);
    }

    render() {
        return (
            <table className={this.state.className}>
                <thead>
                    <tr>
                        {this.state.cols.map((col) =>
                            <th key={col.props.id}
                                onClick={() => { this.toggleSort(col); }}
                                className={'sortable' in col.props ? ' clickable' : ''}
                                style={col.props.thStyle || {}}
                            >
                                <div className="th-wrap">
                                    {col.props.thead || ' '}
                                    {'sortable' in col.props &&
                                        <div className="right">
                                            <a href="javascript:;" title="Sắp xếp">
                                                {(!this.state.sort || !this.state.sort[col.props.id]) && <i className="fa fa-sort"></i>}
                                                {this.state.sort && this.state.sort[col.props.id] == 1 && <i className="fa fa-sort-asc"></i>}
                                                {this.state.sort && this.state.sort[col.props.id] == -1 && <i className="fa fa-sort-desc"></i>}
                                            </a>
                                        </div>
                                    }

                                </div>
                            </th>
                        )}
                    </tr>
                </thead>
                <tbody>
                    {this.state.dataset && this.state.dataset.map((row) =>
                        <tr key={this.state.rowKey(row)}>
                            {this.state.cols.map((col) =>
                                <td key={col.props.id + this.state.rowKey(row)} style={col.tdStyle}>
                                    {col.props.render(row)}
                                </td>
                            )}
                        </tr>
                    )}
                </tbody>
            </table>
        );
    }
}

