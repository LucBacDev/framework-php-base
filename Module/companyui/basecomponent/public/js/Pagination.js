class Pagination extends PureComponent {
    static defaultPageSize = 50;
    static defaultPageNo = 1;

    constructor(props) {
        super(props);

        this.state = {
            filter: {
                pageSize: Pagination.defaultPageSize,
                pageNo: Pagination.defaultPageNo,
                pageSizeOpts: [10, 50, 100],
                pageCount: 1,
                recordCount: 0
            }
        };

        this.bindThis(['onChange']);
    }
    // set lại dữ liệu từ component cha truyền vào
    componentWillReceiveProps(nextProps) {
        this.state.filter.pageCount = nextProps.pageCount;
        this.state.filter.recordCount = nextProps.recordCount;
        this.state.filter.pageNo = nextProps.pageNo;
        this.setPureState({filter: this.state.filter});
    }

    getPageSize() {
        return this.state.filter.pageSize;
    }

    getPageNo() {
        return this.state.filter.pageNo;
    }

    // range từ số thành mảng array
    range(begin, end) {
        var length = end - begin;
        var arr = [];
        for (var i = 1; i <= length; i++) {
            arr.push(i);
        }
        return arr;
    };

    onChange() {
        this.setPureState({ filter: this.state.filter });
        // trả lại dữ liệu bên phía component cha
        if (this.props.onChange) {
            this.props.onChange(this.state.filter.pageSize, this.state.filter.pageNo);
        }
    }

    render() {
        return (
            <div className="form-group row">
                <label className="col-form-label control-label ">{Lang.t('pagination.pageSize')}</label>
                <div className="col-sm-1">
                    <select className="form-control"
                        onChange={(ev) => { this.state.filter.pageSize = ev.target.value; this.state.filter.pageNo = 1; this.onChange(); }}
                        value={this.state.filter.pageSize}
                    >
                        {this.state.filter.pageSizeOpts.map((size) =>
                            <option key={size} value={size}>{size}</option>
                        )}
                    </select>
                </div>
                <label className="col-form-label control-label ">{Lang.t('pagination.itemAndPage')}</label>
                <div className="col-sm-1">
                    <select className="form-control"
                        onChange={(ev) => { this.state.filter.pageNo = ev.target.value; this.onChange(); }}
                        value={this.state.filter.pageNo}
                    >
                        {this.range(0, this.state.filter.pageCount).map((number) =>
                            <option key={number} value={number}>{number}</option>
                        )}
                    </select>
                </div>
                <label className="col-form-label control-label ">{Lang.t('pagination.recordCount')} : <strong>{this.state.filter.recordCount}</strong>, {Lang.t('pagination.pageCount')} : <strong>{this.state.filter.pageCount}</strong></label>
            </div>
        )
    };
}