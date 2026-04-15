Dict.Collection.Model = class DictModel {
    // api lấy danh sách dict trong quản trị
    getCollections(filter) {
        var url = App.siteUrl + '/rest/collections';
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    getCollection(dictID, filter) {
        var url = App.url('/rest/collections/:id', {id: dictID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    // cập nhật dict
    updateCollection(dictID, updateData) {
        var url = App.siteUrl + '/rest/collections';
        if (dictID) {
            url += '/' + dictID;
        }
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

    // xóa dict
    deleteDict(dictID) {
        var url = App.siteUrl + '/rest/collections';
        if (dictID) {
            url += '/' + dictID;
        }
        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }


}