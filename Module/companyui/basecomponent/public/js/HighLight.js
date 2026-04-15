class Highlight extends PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      'list': this.props.list ? this.props.list : [],
      'listHighLightSelected': this.props.listHighLightSelected ? this.props.listHighLightSelected : [],
      'listHighLightSelectedID': this.getListHighLightSelectedID(this.props.listHighLightSelected),
      'colors': [
        {
          'value': '#CCC',
          'name': 'Mặc định(#CCC)'
        },
        {
          'value': '#0000FF',
          'name': 'Màu 1(#0000FF)'
        },
        {
          'value': '#FF0000',
          'name': 'Màu 2(#FF0000)'
        },
        {
          'value': '#007BA7',
          'name': 'Màu 3(#007BA7)'
        },
        {
          'value': '#00A86B',
          'name': 'Màu 4(#00A86B)'
        }
      ]
    };
    // set giá trị mặc định của select
    var select = this.state.list.length ? this.state.list[0].list[0] : {};
    var highLight = typeof (select.highLight) == 'undefined' ? { 'color': '#CCC' } : select.highLight;
    select.highLight = highLight
    this.select = select;
  }

  getListHighLightSelectedID(selected) {
    var IDs = [];
    for (var i in selected) {
      IDs.push(selected[i].id);
    }
    return IDs;
  }

  // theo dõi thay đổi của props
  componentWillReceiveProps(nextProps) {
    if (nextProps.listHighLightSelected) {
      this.setPureState({
        listHighLightSelected: nextProps.listHighLightSelected,
        listHighLightSelectedID: this.getListHighLightSelectedID(nextProps.listHighLightSelected)
      });
    }
  }

  handleChange() {
    this.setPureState({
      listHighLightSelected: this.state.listHighLightSelected,
      listHighLightSelectedID: this.state.listHighLightSelectedID
    });
    // callback truyền giá trị
    if (this.props.onChange) {
      this.props.onChange(this.state.listHighLightSelected);
    }
  }

  chooseHighlight(custom) {
    if (this.inArray(custom.id, this.state.listHighLightSelectedID)) {
      for (var i in this.state.listHighLightSelected) {
        if (this.state.listHighLightSelected[i].id == custom.id) {
          this.select = this.state.listHighLightSelected[i];
          break;
        }
      }
    } else {
      // set giá trị mặc định của highlight
      custom.highLight = {
        color: '#CCC'
      }
      this.select = custom;
    }
    this.handleChange();
  }

  changeHighlight(ev) {
    this.select.highLight = {
      'color': ev.target.value
    };

    if (!this.inArray(this.select.id, this.state.listHighLightSelectedID)) {
      // danh sách đã chọn
      this.state.listHighLightSelected.push(this.select);
      // danh sách ID đã chọn
      this.state.listHighLightSelectedID.push(this.select.id);
    } else {
      // cập nhật nội dung highlight
      for (var i in this.state.listHighLightSelected) {
        var selected = this.state.listHighLightSelected[i];
        if (selected.id == this.select.id) {
          selected = this.select;
          break;
        }
      }
    }

    this.handleChange();
  }

  inArray(key, arr) {
    return jQuery.inArray(key, arr) !== -1 ? true : false;
  };

  render() {
    return (
      <div className="row">
        <div className="col-md-8">
          <select name="from" id="multiselectBoxLeft" className="form-control" size="8">
            {this.state.list.map((val, idx) =>
              <optgroup key={idx} label={val.name}>
                {val.list.map((list, key) =>
                  <option key={key} onClick={() => { this.chooseHighlight(list); }}>{list.name}</option>
                )}
              </optgroup>
            )}

          </select>
        </div>
        <div className="col-md-4">
          <label className="col-form-label control-label">Màu chữ</label>
          <select className="form-control"
            style={{'color': this.select.highLight.color}}
            onChange={(ev) => { this.changeHighlight(ev) }}
            value={this.select.highLight.color}
          >
            {this.state.colors.map((color, idx) =>
              <option 
              style={{'color': color.value}}
              key={idx} 
              value={color.value}>
                {color.name}
              </option>
            )}
          </select>
        </div>
      </div>
    );
  }
}