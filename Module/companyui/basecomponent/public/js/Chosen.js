class Chosen extends PureComponent {
  constructor(props) {
    super(props);
    this.multiple = this.props.multiple ? true : false;
  }

  componentDidMount() {
    // khởi tạo chosen
    this.$el = $(this.el);
    this.$el.chosen();

    // xét gía trị mặc định khi là multiple select
    if (this.multiple) {
      // console.log('multiple+--------');
      this.$el.val([]);
      this.$el.trigger("chosen:updated");
    }
    // thay đổi chọn dữ liệu
    this.handleChange = this.handleChange.bind(this);
    this.$el.on('change', this.handleChange);
  }

  componentWillUnmount() {
    this.$el.off('change', this.handleChange);
    this.$el.chosen('destroy');
  }

  // theo dõi thay đổi của props
  componentWillReceiveProps(nextProps) {
    if (nextProps.value) {
      this.$el.val(nextProps.value);
    }
    // setTimeout(()=> {
    //   this.$el.trigger("chosen:updated").trigger('change');
    // })

  }

  handleChange(e) {
    // callback lại component
    if (this.props.onChange) {
      // trường hợp multiple select dữ liệu là 1 array
      if (this.multiple) {
        var options = e.target.options;
        var value = [];
        for (var i = 0, l = options.length; i < l; i++) {
          if (options[i].selected) {
            value.push(options[i].value);
          }
        }
        this.props.onChange(value);
      }
      // trường hợp select 1 dữ liệu
      else {
        this.props.onChange(e.target.value);
      }
    }
  }

  render() {
    return (
      <div className="Chosen">
        <select
          className="Chosen-select"
          ref={el => this.el = el}
          multiple={this.multiple}
          data-placeholder={this.props.placeholder}
        >
          {this.props.children}
        </select>
      </div>
    );
  }
}