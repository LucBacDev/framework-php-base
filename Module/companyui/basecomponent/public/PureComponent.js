//không tự render nếu so sánh shallow state và props không đổi
class PureComponent extends Component {
    shouldComponentUpdate(nextProps, nextState) {
        return (
            this.shallowDiffers(this.props, nextProps) ||
            this.shallowDiffers(this.state, nextState)
        )
    }

    shallowDiffers(a, b) {
        for (let i in a) if (!(i in b)) return true
        for (let i in b) if (a[i] !== b[i]) return true
        return false
    }

    /**
     * tự động extend để tạo object mới, chống lỗi inmutation
     * @param {*} nextState 
     * @param {*} callback 
     */
    setPureState(nextState, callback) {
        callback = callback || new Function;
        for (var i in nextState) {
            if (this.state[i] != nextState[i])
                continue; //nếu đã khác thì không cần xử lý nữa
            if (Array.isArray(nextState[i]))
                nextState[i] = $.extend([], nextState[i]);
            else if (typeof nextState[i] == 'object')
                nextState[i] = $.extend({}, nextState[i]);
        }
        super.setState(nextState, callback);
    }
}