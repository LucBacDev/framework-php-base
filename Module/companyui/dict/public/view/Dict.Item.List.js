Dict.Item.List = class DictItemList extends PureComponent {
    constructor(props) {
        super(props);
        this.model = new Dict.Item.Model;
        this.state = {
            'totalDict': [],
            'filter': {
                'nameSearch': '',
                'pageSize': 50,
                'pageNo': 1
            },
            'pageCount': 1,
            'recordCount': 0,
            'isModal': (props && 'modal' in props) ? true : false
        };
    }

    componentWillMount() {
        App.Component.trigger('leftNav.active', 'dict.item');
    }
    componentDidMount() {
        App.requireLogin();
        this.getTotalDict();
    }
    isModal() {
        return this.state && this.state.isModal;
    }
    getTotalDict() {
        return new Promise((done) => {
            var filter = {
                'name': this.state.filter.nameSearch,
                'pageSize': this.state.filter.pageSize,
                'pageNo': this.state.filter.pageNo,
            };

            this.model.getItems(filter).then((resp) => {
                if (!resp.length || !resp[0].id) {
                    resp.rows = [];
                }
                this.setState({ 'totalDict': resp }, () => {
                    // dựa vào tổng các item và số lượng các item được show để hiện số lượng trang tương ứng
                    this.setPureState({ pageCount: 1, recordCount: resp.recordCount });
                    done();
                });
            });
        });
    }

    editDictItem(dict) {
        if (this.isModal()) {
            Dict.Item.Edit.open(dict);
        } else {
            Dict.Item.Edit.open(dict).then((resp) => {
                if (resp.status) {
                    this.getTotalDict();
                } else {
                    Alert.open(Lang.t('update.error'));
                }
            });
        }
    }
    handleChangeFilter() {

    }
    openDict() {
        Dict.Item.AllDictEdit.open().then((resp) => {
            console.log('resp---------', resp);
        });
    }

    pageContent() {
        return (
            <div>
                <div className="btn-and-search">
                    <div className="left">
                        <button type="button" className="btn btn-primary" onClick={() => { this.editDictItem() }}>Thêm mới</button>
                    </div>
                    <div className="right searchBox">
                        <div className="input-group">
                            <div className="input-group-prepend">
                                <span className="input-group-text"><i className="ti-search"></i></span>
                            </div>
                            <input type="text" className="form-control" placeholder={Lang.t('dict.placeSearch')} onChange={(event) => { this.handleChangeSearch(event); }} />
                        </div>
                    </div>
                </div>

                <h4></h4>
                <table className="table table-striped table-hover" ref={(elm) => { this.table = elm; }}>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>&nbsp;</th>
                            <th>Trường 1</th>
                            <th>Trường 2</th>
                            <th>Trường 3</th>
                            <th>Trường 4</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ID</td>
                            {!this.isModal() && <td>
                                <div className="dropdown">
                                    <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i className="ti ti-menu"></i>
                                    </a>
                                    <div className="dropdown-menu">
                                        <button className={'dropdown-item '} type="button"
                                            onClick={() => { this.editDictItem(); }} >Sửa</button>
                                        <button className={'dropdown-item '} type="button"
                                            onClick={() => { this.deletedict(); }} >Xóa</button>
                                    </div>
                                </div>
                            </td>}
                            <td>Trường 1</td>
                            <td>Trường 2</td>
                            <td>Trường 3</td>
                            <td>Trường 4</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        );
    }

    render() {
        return (
            <AdminLayout>
                <div className="page-header">
                    <div className="btn-and-search header-title" style={{ "width": "100%" }}>
                        <div className="left">
                            <label className="">{Lang.t('dict.header')}</label>
                        </div>
                        <div className="right searchBox">
                            <div className="input-group">
                                <input onClick={() => { this.openDict() }} type="text" style={{ "cursor": "pointer", "borderRadius": "0" }} className="form-control" value="Xe máy" readOnly />
                                <div className="input-group-prepend">
                                    <button className="btn btn-primary" onClick={() => { this.openDict() }} style={{ "cursor": "pointer" }} >Chọn danh mục</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="card">
                    <div className="card-body">
                        {this.pageContent()}
                    </div>
                </div>
                <Pagination
                    onChange={(pageSize, pageNo) => {
                        this.state.filter.pageSize = pageSize; this.state.filter.pageNo = pageNo;
                        this.handleChangeFilter()
                    }}
                    pageCount={this.state.pageCount}
                    recordCount={this.state.recordCount}
                />
            </AdminLayout>
        );
    }
}