class Modal extends PureComponent {

    constructor(props) {
        super(props);

        this.state = {
            render: false //tránh render khi chưa hiện
        };

        this.handlePropsChange(props);
        this.bindThis(['onModalReady']);
    }


    static Header = class ModalHeader extends PureComponent {
        constructor(props) {
            super(props);

        }

        componentWillReceiveProps() {
            this.setState({});
        }

        render() {
            return (
                <div className="modal-header">
                    <h5 className="modal-title" >{this.props.children}</h5>
                    <button type="button" className="close" onClick={() => { this.trigger('close'); }}>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            );
        }
    };

    static Body = class ModalBody extends Component {

        componentWillReceiveProps() {
            this.setState({});
        }

        render() {
            return (
                <div className="modal-body">{this.props.children}</div>
            );
        }
    };

    static Footer = class ModalFooter extends PureComponent {
        componentWillReceiveProps() {
            this.setState({});
        }

        render() {
            return (
                <div className="modal-footer">{this.props.children}</div>
            );
        }
    };

    componentWillReceiveProps(newProps) {
        this.handlePropsChange(newProps);
        this.setState({});
    }

    onModalReady(modal) {
        this.modal = $(modal);
        this.modal.on('shown.bs.modal', () => {
            this.onModalShown.apply(this);
        });
        this.modal.on('hidden.bs.modal', () => {
            this.onModalHidden.apply(this);
        });
    }

    hideModal() {
        this.modal.modal('hide');
    }

    showModal() {
        this.setState({
            'render': this.state.render ? this.state.render + 1 : 1
        }, () => {
            this.modal.modal('show');
        });
    }

    onModalShown() {
        this.trigger('modal.shown');
    }

    onModalHidden() {
        this.trigger('modal.hidden');
    }


    handlePropsChange(newProps) {
        //lặp từng children để tìm ra header, footer, body

        this.state = $.extend(this.state, {
            header: null,
            body: null,
            footer: null,
            className: newProps.className || '',
            id: newProps.id
        });
        newProps.children.map((item) => {
            if (!item || !item.type || !item.type.name) {
                console.error('Modal structure error', this);
                return;
            }
            switch (item.type.name) {
                case 'ModalHeader':
                    this.state.header = Component.extendsElement(item, {
                        'events': { 'close': () => { this.hideModal(); } }
                    });
                    break;
                case 'ModalBody':
                    this.state.body = item;
                    break;
                case 'ModalFooter':
                    this.state.footer = item;
                    break;
                default:
                    console.error('Children of Modal must be Modal.Header, Modal.Body or Modal.Footer');
            }
        });
    }

    render() {
        return (
            <div className={'modal fade ' + (this.state.className || '')} id={this.state.id} role="dialog" aria-hidden="true" ref={this.onModalReady}>
                {this.state.render &&
                    <div className={'modal-dialog ' + (this.props.size || '')} role="document">
                        <div className="modal-content">
                            {this.state.header}
                            {this.state.body}
                            {this.state.footer}
                        </div>
                    </div>
                }
            </div>
        );
    }
}
