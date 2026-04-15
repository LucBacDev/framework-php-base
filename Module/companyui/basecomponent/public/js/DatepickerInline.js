class DatepickerInline extends Component {
    componentDidMount() {
        $.fn.datepicker.call($(this.elm))
        if(this.props.onChange)
            $(this.elm).datepicker().on('changeDate', this.props.onChange)

        let value = null
        if(this.props.defaultValue)
             value = this.props.defaultValue
        if(this.props.value)
             value = this.props.value
        if(value)
            this.setDate(value)

        this.elm.setDate = this.setDate
        this.elm.getDate = this.getDate
    }

    setDate(value) {
        if(!value) {
            $(this.elm).datepicker('clearDates')
            return
        }
        value = new Date(value)
        $(this.elm).datepicker('setDate', value)
        setTimeout(() => {
            $(".day", this.elm).each(function() {
                if($(this).hasClass('old') || $(this).hasClass('new'))
                    return
                if($(this).text() == value.getDate())
                    $(this).addClass('active')
            })
        })

    }

    getDate() {
        return $(this.elm).datepicker('getDate')
    }

    render() {
        return (<div data-provide="datepicker-inline"
                     ref={(elm) => {this.elm = elm; }}></div>)
    }
}