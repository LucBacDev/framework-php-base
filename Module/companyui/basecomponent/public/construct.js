//File dành cho tất cả theme để khởi tạo các component cơ bản nhất

App = window.App || {};

if($.fn.datepicker) {
    $.fn.datepicker.defaults = $.extend($.fn.datepicker.defaults, {
        'format': "dd/mm/yyyy",
        'todayBtn': true,
        'todayHighlight': true,
        'language': 'vi'
    });

    $.fn.datepicker.dates['vi'] = {
        days: ["Chủ nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7"],
        daysShort: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
        daysMin: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
        months: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"],
        monthsShort: ["Thg1", "Thg2", "Thg3", "Thg4", "Thg5", "Thg6", "Thg7", "Thg8", "Thg9", "Thg10", "Thg11", "Thg12"],
        today: "Hôm nay",
        clear: "Xóa",
        format: "dd/mm/yyyy",
        titleFormat: "dd/mm/yyyy", /* Leverages same syntax as 'format' */
        weekStart: 0
    };
}



App.alert = function (message, callback) {
    callback = callback || new Function();
    var modal = '<div class="modal fade" id="modal-alert" tabindex="-1" role="dialog" aria-hidden="true">'
        + '<div class="modal-dialog modal-sm" role="document">'
        + '<div class="modal-content">'
        + '<div class="modal-header">'
        + '<h5 class="modal-title">Thông báo</h5>'
        + '<button type="button" class="close close-modal">'
        + '   <span aria-hidden="true">&times;</span>'
        + '   </button>'
        + '</div>'
        + '<div class="modal-body">'
        + message
        + '</div>'
        + '<div class="modal-footer">'
        + '<input type="button" class="btn btn-primary close-modal"  autofocus onclick="App.alert.callback()" value="Đóng thông báo">'
        + '</div></div></div></div>';

    App.alert.callback = callback;
    $('#modal-alert').remove();
    $('body').append(modal);
    $('#modal-alert').on('shown.bs.modal', function () {
        $('#modal-alert .btn').focus();
    });
    $('#modal-alert').modal('show');

    $('#modal-alert').on('hidden.bs.modal', function () {
        callback();
    });
};

App.confirm = function (message) {
    return new Promise(function (ok, cancel) {
        var modal = '<div class="modal fade" id="modal-confirm" tabindex="-1" role="dialog" aria-hidden="true">'
            + '<div class="modal-dialog modal-sm" role="document">'
            + '<div class="modal-content">'
            + '<div class="modal-header">'
            + '<h5 class="modal-title">Xác nhận</h5>'
            + '<button type="button" class="close close-modal" aria-label="Close">'
            + '   <span aria-hidden="true">&times;</span>'
            + '   </button>'
            + '</div>'
            + '<div class="modal-body">'
            + message
            + '</div>'
            + '<div class="modal-footer">'
            + '<input type="button" class="btn btn-primary close-modal" autofocus onclick="App.confirm.setResult(true)" value="Đồng ý">'
            + '<input type="button" class="btn btn-secondary close-modal" autofocus onclick="App.confirm.setResult(false)" value="Hủy bỏ">'
            + '</div></div></div></div>';

        App.confirm.result = false;
        App.confirm.setResult = function (bool) {
            App.confirm.result = bool;
        }

        App.confirm.ok = ok || new Function();
        App.confirm.cancel = cancel || new Function();
        $('#modal-confirm').remove();
        $('body').append(modal);
        $('#modal-confirm').modal('show');
        $('#modal-confirm').on('shown.bs.modal', function () {
            $('#modal-confirm .btn-secondary').focus();
        });

        $('#modal-confirm').on('hidden.bs.modal', function () {
            if (App.confirm.result) {
                App.confirm.ok();
            } else {
                App.confirm.cancel();
            }
        });
    });
};

//nút <- -> trong modal dialog
$('body').on('keyup', '.modal-footer', function (event) {
    if (event.keyCode == 37) {
        $(event.target).prev().focus(); //sang trai
        event.preventDefault();
        event.stopPropagation();
    } else if (event.keyCode == 39) {
        $(event.target).next().focus(); //sang phai
        event.preventDefault();
        event.stopPropagation();
    }

});

$.rest = function (param) {
    if (param.method && param.method.toUpperCase() != 'GET')
        param.data = JSON.stringify(param.data);
    param.dataType = 'json';
    param.contentType = param.contentType ? param.contentType : 'application/json';
    return new Promise(function (done, fail) {
        $.ajax(param).done(function (resp) {
            //lưu version để các component dễ cache hơn
            resp.version = new Date().getTime();
            done(resp);
        }).fail(fail);
    });
};

Date.prototype.toLocaleDateString = function() {
    return this.getDate() + '/' + (this.getMonth() + 1) + '/' + this.getFullYear()
}

Date.prototype.toLocaleTimeString = function() {
    return this.getHours() + ':' + this.getMinutes()
}



Lang.load('companyui', 'basecomponent');