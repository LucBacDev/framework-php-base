Dict.Collection.List = class DictCollectionList extends PureComponent {
    constructor(props) {
        super(props);
        this.dictModel = new Dict.Collection.Model;
        this.state = {
            'collections': [],
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
        if (!this.state.isModal) {
            App.Component.trigger('leftNav.active', 'dict.collection');
        }
    }
    componentDidMount() {
        App.requireLogin();
        this.getCollections();
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    isModal() {
        return this.state && this.state.isModal;
    }

    isModalDictObject() {
        return this.state && this.state.isModalDictObject;
    }

    getCollections() {
        return new Promise((done) => {
            var filter = {
                'name': this.state.filter.nameSearch,
                'pageSize': this.state.filter.pageSize,
                'pageNo': this.state.filter.pageNo,
            };

            this.dictModel.getCollections(filter).then((resp) => {
                if (!resp.length || !resp[0].id) {
                    resp.rows = [];
                }
                this.setState({ 'collections': resp }, () => {
                    // dựa vào tổng các item và số lượng các item được show để hiện số lượng trang tương ứng
                    this.setPureState({ pageCount: 1, recordCount: resp.recordCount });
                    done();
                });
            });
        });
    }

    getSelectedCollection() {
        var selected = "";
        this.state.collections.map((collection) => {
            if (collection.checked){
                selected = collection;
            }
        });
        return selected;
    }

    editDictCollection(dict) {
        if (this.isModal()) {
            if (this.props.modal == 'modalDictCollection') {
                Dict.Collection.DictEdit.open();
            } else {
                // xử lí chọn danh mục trong DictItemList
                this.state.collections.map((collection, idx) => {
                    if (dict.id == collection.id){
                        this.state.collections[idx].checked = true;
                    }else{
                        this.state.collections[idx].checked = false;
                    }
                });
                this.setPureState({ 'collections': this.state.collections });
            }
        } else {
            console.log('Dict.Collection.AllDictEdit----', Dict.Collection.AllDictEdit);
            Dict.Collection.AllDictEdit.open(dict).then((resp) => {
                if (resp.status) {
                    this.getCollections();
                } else {
                    Alert.open(Lang.t('update.error'));
                }
            });
        }
    }

    deleteDictCollection(dict) {
        Confirm.open('Xác nhận xóa danh mục ' + dict.name + '?').then((resp) => {
            if (resp) {
                this.dictModel.deleteDict(dict.id).then((res) => {
                    if (res.status) {
                        // Thông báo thành công
                        this.getCollections();
                    } else {
                        Alert.open(Lang.t('update.error'));
                    }
                });
            }
        });
    }



    handleChangeFilter() {
        this.getCollections();
    }

    pageContent() {
        return (
            <div>
                {!this.isModal() &&
                    <div className="btn-and-search">
                        <div className="left">
                            <button type="button" className="btn btn-primary" onClick={() => { this.editDictCollection() }}>{Lang.t('dict.add')}</button>
                        </div>
                        <div className="right searchBox">
                            <div className="input-group">
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control" placeholder={Lang.t('dict.placeSearch')} onChange={(ev) => { this.state.filter.nameSearch = ev.target.value; this.handleChangeFilter(); }} />
                            </div>
                        </div>
                    </div>
                    ||
                    <div className="form-group row">
                        <label className="col-sm-7 col-form-label control-label">{Lang.t('dict.suggestion')}</label>
                        <div className="col-sm-5 right searchBox">
                            <div className="input-group">
                                <div className="input-group-prepend">
                                    <span className="input-group-text"><i className="ti-search"></i></span>
                                </div>
                                <input type="text" className="form-control" placeholder={Lang.t('dict.placeSearch')} onChange={(ev) => { this.state.filter.nameSearch = ev.target.value; this.handleChangeFilter(); }} />
                            </div>
                        </div>
                    </div>
                }

                <h4></h4>
                <table className="table table-striped table-hover" ref={(elm) => { this.table = elm; }}>
                    <thead>
                        <tr>
                            <th style={{ 'minWidth': '100px' }}>ID</th>
                            {!this.isModal() && <th style={{ 'minWidth': '20px' }}>&nbsp;</th>}
                            <th style={{ 'width': '100%' }}>Tên</th>
                        </tr>
                    </thead>
                    <tbody>
                        {this.state.collections.map((collection) =>
                            <tr key={collection.id}>
                                <td>{collection.id}</td>
                                {!this.isModal() &&
                                    <td>
                                        <div className="dropdown">
                                            <a href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i className="ti ti-menu"></i>
                                            </a>
                                            <div className="dropdown-menu">
                                                <button className={'dropdown-item '} type="button"
                                                    onClick={() => { this.editDictCollection(collection); }} >{Lang.t('dict.btnEdit')}</button>
                                                <button className={'dropdown-item '} type="button"
                                                    onClick={() => { this.deleteDictCollection(collection); }} >{Lang.t('dict.btnDelete')}</button>
                                            </div>
                                        </div>
                                    </td>
                                }
                                <td><a href="javascript:;" onClick={() => { this.editDictCollection(collection); }}>{collection.name}</a></td>
                            </tr>
                        )}

                    </tbody>
                </table>
            </div>
        );
    }

    render() {
        if (this.isModal()) {
            return (
                this.pageContent()
            );
        } else {
            return (
                <AdminLayout>
                    <PageHeader>{Lang.t('dict.header')}</PageHeader>
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
}