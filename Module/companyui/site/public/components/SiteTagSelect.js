class SiteTagSelect extends PureComponent {
    constructor(props){
        super(props);
        this.state = {
            'tags': [],
            'selected' : this.props.selected || [],
            'forceRender': 0
        }
        this.siteModel = new SiteModel;
    }

    componentDidMount(){
        this.siteModel.getAllTags().then((resp) => {
            this.setPureState({'tags' : resp})
        })
        this.containDiv = this.chosen.nextElementSibling
        this.select = this.chosen.el
        $('input.default', this.containDiv).keyup((ev) => {
            if (ev.keyCode != 13)
                return
            if (ev.target.value.length == 0)
                return
            this.state.tags.push(ev.target.value)
            // console.log(ev.target.value)
            this.state.selected.push(ev.target.value)
            ev.target.value = ''
            this.setState({'forceRender': this.state.forceRender+1})
        })
    }

    componentWillReceiveProps(nextProps) {
        if(JSON.stringify(this.state.selected) != JSON.stringify(nextProps.selected))
        {
            this.setPureState({"selected" : nextProps.selected || []})
        }
    }

    handleChange(values){
        if(this.props.onChange)
            this.props.onChange(values)
    }

    render() {
        return <Chosen multiple ref={(elm)=> {this.chosen = elm; }}
                       placeholder="search tags"
                       onChange={(values) => {this.handleChange(values)}} forceRender={this.state.forceRender}>
            {this.state.tags.map((tag) => <option key={tag} defaultValue={this.state.selected.indexOf(tag) != -1}>{tag}</option>)}
        </Chosen>
    }
}