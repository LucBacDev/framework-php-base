class DateRangeInline extends PureComponent{
    constructor(props) {
        super(props);

        this.state = {
            'from': null,
            'to': null
        }

        this.bindThis(['setDate'])
    }

    setDate(event) {
        let val = $(event.target).attr('setval')
        switch(val) {
            case '0':
                this.fromElm.setDate(new Date)
                this.toElm.setDate(new Date)
                break;
            case '-1':
            case '-7':
            case '-30':
                this.toElm.setDate(new Date)
                let from = new Date
                from.setDate(from.getDate() + parseInt(val))
                this.fromElm.setDate(from)
                break
            case '9999':
                this.fromElm.setDate(null)
                this.toElm.setDate(null)
                break
        }
        if(val != 9999) {
            this.setState({
                'from': this.fromElm.getDate(),
                'to': this.toElm.getDate()
            })
        } else {
            this.setState({
                'from': null,
                'to': null
            })
        }


    }

    onBeginChange(d) {
        this.setState({'from': d}, ()=>{
            if(this.props.onChange)
                this.props.onChange(this.state)
        })

    }

    onEndChange(d) {
        this.setState({'to': d}, ()=> {
            if(this.props.onChange)
                this.props.onChange(this.state)
        })
    }

    render() {
        return (<div className="date-range-inline">
            <div className="dateRangeGuide">{Lang.t('date.dateRangeGuide')}</div>
            <div className="btn-group">
                <button type="button" className="btn btn-default" setval="0" onClick={this.setDate}>{Lang.t('date.dateRangeToday')}</button>
                <button type="button" className="btn btn-default" setval="-1" onClick={this.setDate}>{Lang.t('date.dateRangeYesterday')}</button>
                <button type="button" className="btn btn-default" setval="-7" onClick={this.setDate}>{Lang.t('date.dateRange7day')}</button>
                <button type="button" className="btn btn-default" setval="-30" onClick={this.setDate}>{Lang.t('date.dateRange30day')}</button>
                <button type="button" className="btn btn-default" setval="9999" onClick={this.setDate}>{Lang.t('date.dateRangeAll')}</button>
            </div>

            <DatepickerInline
                defaultValue={this.props.from}
                onChange={(e) => {this.onBeginChange(e.date); }}
                ref={(elm) => {this.fromElm = elm; }}
            />
            <DatepickerInline
                defaultValue={this.props.to}
                onChange={(e) => {this.onEndChange(e.date); }}
                ref={(elm) => {this.toElm = elm; }}
            />
        </div>)
    }
}