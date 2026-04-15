window.multiSelectCheckBoxID = 0;

class MultipleSelectCheckbox extends PureComponent {
    constructor(props) {
        super(props);

        this.eleID = this.props.id ? this.props.id : 'msc-' + window.multiSelectCheckBoxID++;
        this.placeholder = this.props.placeholder ? this.props.placeholder : 'Chọn ..';
        this.state = {
            'show': false,
            'value': !this.props.value ? [] : this.props.value
        };

        this.bindThis([
            'renderClass', 'toggleList', 'renderValue'
        ]);

        this.mounted = false;
    }

    componentWillReceiveProps(nextProps) {
        if ('value' in nextProps) {
            this.setPureState({
                value: nextProps.value,
            });
        }
    }

    componentWillMount(){
        this.mounted = false;
        document.removeEventListener('mousedown', (e) => {});
        this.setState({ show: false });
    }

    componentDidMount() {
        this.$el = $(this.el);
        this.mounted = true;
        document.addEventListener('mousedown', (e) => {
            var $trigger = $("#" + this.eleID + ".dropdown");
            if ($trigger !== event.target && !$trigger.has(event.target).length && this.mounted) {
                this.setState({ show: false });
            }
        });

    }

    componentWillUnmount() {
        this.mounted = false;
        document.removeEventListener('mousedown', (e) => {});
        this.setState({ show: false });
    }

    toggleList() {
        this.setState(prevState => ({ show: !prevState.show }))
    }


    handleChange(e) {
        this.props.onChange(e.target.value);
    }

    renderValue() {
        if (!this.state.value.length)
            return this.placeholder;

        return this.state.value.join(",");
    }

    renderClass() {
        var _class = "dropdown-menu ";

        if (this.state.show)
            _class += "show";

        return _class;
    }

    render() {
        return (
            <div className="dropdown mutiple-select-checkbox" id={this.eleID}>
                <select
                    className="form-control"
                    ref={el => this.el = el}
                    href="javascript:;"
                    onClick={this.toggleList.bind(this)}>
                    <option value="" className="no-display">{this.renderValue()}</option>
                </select>
                <div className={this.renderClass()}>
                    {this.props.children}
                </div>
            </div>
        );
    }
}