class TxtArea extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            value: props.defaultValue
        };

        this.handleChange = this.handleChange.bind(this);
    }

    componentWillReceiveProps(nexProps) {
        if (!nexProps)
            return;
        this.setState({ value: nexProps.defaultValue });
    }

    handleChange(event) {
        this.setState({ value: event.target.value }, (resp) => {
            if (this.props.onChange) {
                this.props.onChange(this.state.value);
            }
        });
    }

    render() {
        const props = this.props;
        const inputProps = {
            readOnly: props.readOnly || false,
            disabled: props.disabled || null,
            onClick: props.onClick || new Function,
            onChange: this.handleChange,
            value: this.state.value || '',
            className: props.className || '',
            id: props.id || null,
            style: props.style || null,
            name: props.name || null,
            placeholder: props.placeholder || null,
            rows: props.rows || 3,
            cols: props.cols || ''
        };

        return React.createElement('textarea', inputProps);
    }
}