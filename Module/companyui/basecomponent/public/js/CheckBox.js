window.checkBoxID = 0;

class CheckBox extends PureComponent {
    constructor(props) {
        super(props);
        this.elmID = this.props.id ? this.props.id : 'chk-' + window.checkBoxID++;

        const checked = 'checked' in props ? props.checked : props.defaultChecked;
        const disabled = props.disabled == 'true' || props.disabled && (props.disabled != 'false') ? true : false;

        this.state = {
            'checked': checked,
            'disabled': disabled,
            'label': this.props.label
        };

    }

    isChecked() {
        return this.props.checked;
    }

    static defaultProps = {
        className: '',
        style: {},
        type: 'checkbox',
        onFocus() { },
        onBlur() { },
        onChange() { }
    };

    componentWillReceiveProps(nextProps) {
        if ('checked' in nextProps) {
            this.setState({
                checked: nextProps.checked,
            });
        }
        if ('disabled' in nextProps) {
            this.setState({
                disabled: nextProps.disabled,
            });
        }
        if ('label' in nextProps) {
            this.setState({
                label: nextProps.label,
            });
        }
    }

    handleChange() {
        var that = this;
        var chk = $('#' + that.elmID);

        if (this.props.onChange) {
            this.props.onChange(chk.prop('checked') ? true : false);
        }
    }

    render() {
        return (
            <div className="checkbox">
                <input id={this.elmID} type="checkbox" className={this.props.className}
                    ref={(elm) => { this.checkbox = elm; }}
                    onChange={() => { this.handleChange(); }}
                    checked={this.props.checked}
                    disabled={this.state.disabled}
                />
                <label htmlFor={this.elmID}>{this.state.label}</label>
            </div>
        );
    }
}