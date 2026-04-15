(function($) {
    $.fn.ajaxUpload = function(a) {
        var $this = $(this);
        if (typeof a === 'object') {
            a = $.extend(a, {
            });
            construct(a);
        } else if (a === 'upload') {
            $this.trigger('__upload');
        } else if (a === 'abort') {
            $this.trigger('__abort');
        }

        function construct(options) {
            var xhr;
            function beginUpload() {
                var file = $this[0].files[0];
                // fd dung de luu gia tri goi len
                var fd = new FormData();
                fd.append('file', file);
                xhr = new XMLHttpRequest();
                xhr.open('POST', options.url, true);
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        var percentValue = (e.loaded / e.total) * 100 + '%';
                        if (options.progress)
                            options.progress.apply($this, [xhr, percentValue]);
                        $this.trigger('progress.upload', [xhr, percentValue]);
                    }
                };
                xhr.onload = function() {
                    if (this.status === 200) {
                        if (options.complete)
                            options.complete.apply($this, [xhr.responseText]);
                        $this.trigger('complete.upload', [xhr.responseText]);
                    }
                    xhr = null;
                    $this.val('');
                };
                xhr.send(fd);
                if (options.begin) {
                    options.begin.apply($this, [xhr]);
                    $this.trigger('begin.upload', [xhr]);
                }
            }

            function abort() {
                if (xhr) {
                    xhr.abort();
                    $this.val('');
                }
            }

            $this.on('__upload', beginUpload);
            $this.on('__abort', abort);
        }
        return $this;
    };
})(jQuery);