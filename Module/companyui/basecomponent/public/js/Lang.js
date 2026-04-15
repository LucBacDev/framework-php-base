class Lang {
    static instance;
    static messages = {};
    static langDefault = 'vi';

    static getInstance() {
        if (!Lang.instance) {
            Lang.instance = new Lang;
        }
        return Lang.instance;
    }

    static setLang(lang) {
        Lang.lang = lang;
    }

    /**
     * Download file language từ server 
     * @param {*} vendor 
     * @param {*} module 
     * @param {*} lang nếu không chỉ định sẽ lấy giá trị mặc định
     */
    static load(vendor, module, lang = Lang.langDefault) {
        lang = lang || localStorage.getItem("lang") || Lang.langDefault;

        return new Promise((done, err) => {
            let url = App.siteUrl + '/modules/' + vendor + '/' + module + '/langs/' + lang;
            if (!localStorage.getItem(url)) {
                $.rest({
                    'url': url,
                }).then((resp) => {
                    for (var key in resp) {
                        Lang.messages[key] = resp[key];
                    }
                    localStorage.setItem(url, JSON.stringify(resp));
                    done(true);
                }).catch(err => {
                    console.error(err);
                });
            } else {
                let resp = JSON.parse(localStorage.getItem(url))
                for(let key in resp)
                    Lang.messages[key] = resp[key];
                done(true);
            }

        });
    }

    static translate(key, params) {
        var text = (key in Lang.messages ? Lang.messages[key] : "[" + key + "]");
        if (!params)
            return text;

        for (var i in params) {
            var rex = new RegExp("{" + i + "}", "g");
            console.log(rex);
            text = text.replace(rex, params[i]);
        }

        return text;
    }

    /**
     * shortcut of Lang.translate
     * @param {*} key
     * @param params
     */
    static t(key, params = null) {
        key = key.replace(new RegExp(" ", "g"), ".");
        return Lang.translate(key, params);
    }


}