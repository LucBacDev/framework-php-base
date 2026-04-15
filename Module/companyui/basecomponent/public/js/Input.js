class Input extends PureComponent {
    constructor(props) {
        super(props);

        this.state = {
            isValid: null
        };

        this.bindThis(['onChange']);
    }

    onChange(event) {
        if (this.props.type == 'number') {
            //khong cho nhap
            return;
        }
        if(this.props.type == 'code'){
            //không cho nhập các ký tự đặc biệt
            event.target.value = event.target.value.replace(/[.,=><*+?^${}&!%#()@|[\]\\`~:;\"\'\/ ]/g, '');
        }
        if (this.props.onChange) {
            this.props.onChange(event);
        }
    }

    setValid(bool) {
        return new Promise((done) => {
            this.setState({
                isValid: bool
            }, done);
        });
    }

    getValid() {
        return this.state.isValid;
    }

    render() {
        const props = this.props;
        var className = props.className || '';
        if (this.state.isValid !== null) {
            className += this.state.isValid ? ' is-valid' : ' is-invalid';
        }
        var inputProps = {
            readOnly: props.readOnly || false,
            pattern: props.pattern || null,
            disabled: props.disabled || null,
            onClick: props.onClick || new Function,
            onChange: this.onChange,
            type: props.type || 'text',
            value: props.value || '',
            checked: props.checked || '',
            className: className,
            id: props.id || null,
            style: props.style || null,
            required: props.required || false,
            name: props.name || null,
            placeholder: props.placeholder || null
        };
        var input = React.createElement('input', inputProps);
        return input;
    };
}