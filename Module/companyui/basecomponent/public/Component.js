class Component extends React.Component {
    constructor(props) {
        super(props);

        this._evState = {};
        this._events = {};
        this._autoTrigger = {};
        //lắng nghe event
        if (props && props.events) {
            for (var event in props.events) {
                var handler = props.events[event];
                this.on(event, handler);
            }
        }
    }

    render() {
        return (<div>Component Base Class</div>)
    }

    /**
     * Lắng nghe sự kiện
     * @param {*} event 
     * @param {*} handler 
     */
    on(event, handler) {
        if (!this._events[event])
            this._events[event] = [];

        this._events[event].push(handler);
        if (this._autoTrigger[event])
            handler.apply(this, this._evState[event] ? [this._evState[event]] : []);
        return {
            'event': event,
            'handlerIndex': this._events[event].length - 1
        }
    }

    off(handlerData) {
        if (!this._events[handlerData.event])
            return;
        //just delete, array splice will make other index incorrect
        this._events[handlerData.event][handlerData.handlerIndex] = null  
    }

    /**
     * Lấy dữ liệu mới nhất của event
     * @param {*} event 
     */
    getEventState(event) {
        return this._evState[event];
    }

    setEventState(event, params) {
        this._evState[event] = params;
    }

    mapObject(object, fn) {
        var arr = [];
        for (var key in object) {
            arr.push(fn(key, object[key]));
        }

        return arr;
    }

    /**
     * tạo một component mà không cần khai báo tĩnh, trả về instance của component đấy
     */
    static getInstance() {
        return new Promise((done) => {
            if (!this.instance) {
                var elm = React.createElement(this, {
                    ref: (instance) => {
                        this.instance = instance;
                        done(instance);
                    }
                });
                var elmID = "component-" + this.name;
                $('body').append('<div id="' + elmID + '"></div>');
                ReactDOM.render(elm, document.getElementById(elmID));
            } else {
                done(this.instance);
            }
        });


    }

    /**
     * Kích hoạt sự kiện
     * @param {*} event tên sự kiện
     * @param {*} params [] mảng các tham số
     * @param {*} autoTrigger Tự động kích hoạt nếu đã kích hoạt lần đầu
     */
    trigger(event, params, autoTrigger = false) {
        autoTrigger = autoTrigger || false;
        this._evState[event] = params;

        if (autoTrigger)
            this._autoTrigger[event] = true;
        if (!this._events[event])
            return;
        this._events[event].map((handler) => {
            handler.apply(this, typeof params != 'undefined' ? [params] : []);
        });
    }

    /**
     * sử dụng lại props và children để tạo elm mới
     * Chỉ override, nếu không có thì không xóa propsF
     */
    static extendsElement(elm, props = {}, children = null) {
        var newProps = {};
        //copy props
        $.each(elm.props, (key, val) => {
            if (key != 'children')
                newProps[key] = val;
        });
        //override props
        $.each(props, (key, val) => {
            newProps[key] = val;
        });
        //override children
        children = children === null ? elm.props.children : children;
        return React.createElement(elm.type, newProps, children);
    }

    /**
     * Fix lỗi callback không truy cập được this
     * @param {*} methods 
     */
    bindThis(methods) {
        methods.map((method) => {
            this[method] = this[method].bind(this);
        });
    }
}

App.Component = new Component;
