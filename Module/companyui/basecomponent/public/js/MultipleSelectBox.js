class MultipleSelectBox extends PureComponent {
  constructor(props) {
    super(props);
    this.selectGroup = this.props.selectGroup ? true : false;
    this.state = {
      'list': this.props.list ? this.props.list : [],
      'listSelected': this.props.listSelected ? this.props.listSelected : [],
      'listSelectedID': this.props.listSelected ? this.getIDSelected(this.props.listSelected) : []
    };
  }

  // theo dõi thay đổi của props
  componentWillReceiveProps(nextProps) {
    if (nextProps.listSelected) {
      this.setPureState({
        listSelected: nextProps.listSelected,
        listSelectedID: this.getIDSelected(nextProps.listSelected)
      });
    }
  }

  handleChange() {
    this.setPureState({
      listSelected: this.state.listSelected,
      listSelectedID: this.state.listSelectedID
    });
    // callback truyền giá trị
    if (this.props.onChange) {
      this.props.onChange(this.state.listSelected);
    }
  }

  // lấy danh sách ID đã selected
  getIDSelected(selecteds) {
    if (selecteds.length == 0)
      return [];

    var idSelecteds = [];
    for (var i in selecteds) {
      idSelecteds.push(selecteds[i].id);
    }
    return idSelecteds;
  }

  inArray(key, arr) {
    return jQuery.inArray(key, arr) !== -1 ? true : false;
  };

  // chọn item
  moveRight() {
    var selecteds = $('#multiselectBoxLeft').val();
    if (selecteds.length == 0) return;
    // danh sách đã được lựa chọn
    var arraySelected = [];
    // danh sách ID đã được lựa chọn
    var arraySelectedID = [];
    for (var i in selecteds) {
      arraySelected.push(JSON.parse(selecteds[i]));
      arraySelectedID.push(JSON.parse(selecteds[i]).id);
    }
    this.state.listSelected = this.state.listSelected.concat(arraySelected);
    this.state.listSelectedID = this.state.listSelectedID.concat(arraySelectedID);
    this.handleChange();
  }

  // bỏ chọn item
  moveLeft() {
    var selecteds = $('#multiselectBoxRight').val();
    if (selecteds.length == 0) return;
    for (var i in selecteds) {
      // format về json
      var selected = JSON.parse(selecteds[i]);
      for (var j in this.state.listSelected) {
        if (selected.id == this.state.listSelected[j].id) {
          // xóa phần tử trong mảng dữ liệu
          this.state.listSelected.splice(j, 1);
          this.state.listSelectedID.splice(j, 1);
        }
      }
    }
    this.handleChange();
  }

  // chọn tất cả item
  moveAllRight() {
    // get All ID 
    var ids = [];
    var slected = [];
    // trường hợp khi option là selectGroup
    if (this.selectGroup) {
      for (var i in this.state.list) {
        for (var j in this.state.list[i].list) {
          ids.push(this.state.list[i].list[j].id);
          slected.push(this.state.list[i].list[j]);
        }
      }
    } else {
      for (var i in this.state.list) {
        ids.push(this.state.list[i].id);
        slected.push(this.state.list[i]);
      }
    }
    this.state.listSelected = slected;
    this.state.listSelectedID = ids;
    this.handleChange();
  }

  // bỏ chọn tất cả item
  moveAllLeft() {
    this.state.listSelected = [];
    this.state.listSelectedID = [];
    this.handleChange();
  }


  render() {
    return (
      <div className="row">
        <div className="col-md-5">
          <select name="from" id="multiselectBoxLeft" className="form-control" size="8" multiple="multiple">
            {!this.selectGroup && this.state.list.map((val, idx) =>
              <option key={idx} value={JSON.stringify(val)} disabled={this.inArray(val.id, this.state.listSelectedID)}>{val.name}</option>
            )}

            {this.selectGroup && this.state.list.map((val, idx) =>
              <optgroup key={idx} label={val.name}>
                {val.list.map((list, key) =>
                  <option key={key} value={JSON.stringify(list)} disabled={this.inArray(list.id, this.state.listSelectedID)}>{list.name}</option>
                )}
              </optgroup>
            )}

          </select>
        </div>
        <div className="col-md-2">
          <button
            type="button"
            className="btn btn-block btn-default"
            onClick={() => { this.moveRight() }}
          >
            <i className="fa fa-angle-right"></i>
          </button>
          <button
            type="button"
            className="btn btn-block btn-default"
            onClick={() => { this.moveLeft() }}
          >
            <i className="fa fa-angle-left"></i>
          </button>
          <button
            type="button"
            className="btn btn-block btn-default"
            onClick={() => { this.moveAllRight() }}
          >
            <i className="fa fa-angle-double-right"></i>
          </button>
          <button
            type="button"
            className="btn btn-block btn-default"
            onClick={() => { this.moveAllLeft() }}
          >
            <i className="fa fa-angle-double-left"></i>
          </button>
        </div>
        <div className="col-md-5">
          <select name="to" id="multiselectBoxRight" className="form-control" size="8" multiple="multiple">
            {this.state.listSelected.map((val, idx) =>
              <option key={idx} value={JSON.stringify(val)}>{val.name}</option>
            )}
          </select>
        </div>
      </div>
    );
  }
}