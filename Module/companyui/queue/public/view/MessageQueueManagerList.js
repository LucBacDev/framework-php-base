class MessageQueueManager extends PureComponent {

    constructor(props) {
        super(props);
        this.model = new MessageQueueModel();
        this.state = {
            'modalOptions': this.handleModalOptions(props.modal),
            'isModal': (props && 'modal' in props) ? true : false,
            "messages": [],
            filter: {
                pageNo: 1,
                pageSize: 100
            },
            'pageCount': 1,
            'recordCount': 0,
        };

    }

    isModal() {
        return this.state && this.state.isModal;
    }

    handleModalOptions(opts) {
        //default
        return $.extend({
            'multiple': false,
            'type': 'department'
        }, opts);
    }

    componentWillReceiveProps(nexProps) {
        if (!nexProps)
            return;
        if ('modal' in nexProps) {
            this.setState({'modalOptions': this.handleModalOptions(nexProps.modal)});
        } else {
            this.setState({'modalOptions': {}});
        }
    }

    componentDidMount() {
        App.requireLogin();
        this.getMessageQueue();
    }

    componentWillMount() {
        if (!this.isModal()) {
            App.Component.trigger('leftNav.active', 'MessageQueueManager');
        }
    }

    componentDidUpdate() {
        this.trigger('update');
    }

    getMessageQueue() {
        return new Promise((done) => {
            this.model.getMessageQueueManager(this.state.filter).then(resp => {
                this.setPureState({messages: resp.rows});
            });
        });
    }

    openDetail(message) {
        MessageDetail.open(message).then(() => {
            this.getMessageQueue();
        });
    }

    applyFilter() {
        this.setPureState({filter: this.state.filter}, () => {
            this.getMessageQueue();
        });
    }

    retryAll() {
        let message_ids = this.state.messages.map(message => message.message_id);
        this.model.retry(message_ids).then((resp) => {
            $.toast({
                text: Lang.t("queue.success"),
                icon: 'success',
                position: 'top-right'
            });

            this.getMessageQueue();
        });
    }

    pageContent() {
        return (
            <div>
                {!this.isModal() && <PageHeader>{Lang.t('queue.header')}</PageHeader>}
                <div className="row">
                    <div className="col">
                        <input type="text" className="form-control" placeholder={Lang.t('queue.message.topic')}
                               onChange={(ev) => {
                                    this.state.filter.topic = ev.target.value;
                                    this.setPureState({filter: this.state.filter});
                               }}
                        />
                    </div>
                    <div className="col">
                        <input type="text" className="form-control" placeholder={Lang.t('queue.message.message_id')}
                               onChange={(ev) => {
                                   this.state.filter.message_id = ev.target.value;
                                   this.setPureState({filter: this.state.filter});
                               }}/>
                    </div>
                    <div className="col">
                        <button type="button" className="btn btn-primary" onClick={() => {this.getMessageQueue()}}>{Lang.t('queue.search')}</button>
                        <button type="button" className="btn btn-primary" onClick={() => {this.retryAll()}}>{Lang.t('queue.retryAll')}</button>
                    </div>
                </div>
                <div className="card bg-light study-list">
                    <div className="card-body">
                        <table className="table">
                            <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">{Lang.t('queue.message.message_id')}</th>
                                <th scope="col">{Lang.t('queue.message.topic')}</th>
                                <th scope="col">{Lang.t('queue.message.created_time')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {
                                this.state.messages.map((message, index) =>
                                    <tr key={message["message_id"]}>
                                        <th scope="row">{index + 1}</th>
                                        <td><a style={{color: "#04a1f4", cursor: "pointer"}} onClick={(ev) => {
                                            this.openDetail(message);
                                        }}>{message["message_id"]}</a></td>
                                        <td>{message["topic"]}</td>
                                        <td>{message["created_time"]}</td>
                                    </tr>
                                )
                            }

                            </tbody>
                        </table>
                    </div>
                    <div className="card-footer">
                        <Pagination
                            onChange={(pageSize, pageNo) => {
                                this.state.filter.pageSize = pageSize;
                                this.state.filter.pageNo = pageNo;
                                this.applyFilter()
                            }}
                            pageCount={this.state.pageCount}
                            recordCount={this.state.recordCount}
                            pageNo={this.state.filter.pageNo}
                        />
                    </div>
                </div>
            </div>
        );
    }

    render() {
        if (this.isModal())
            return this.pageContent();
        else
            return (
                <AdminLayout>
                    {this.pageContent()}
                </AdminLayout>
            );
    }

}