Dict.Item.Model = class DictItemModel {
    // api lấy danh sách item trong quản trị
    getItems(collectionID, filter) {
        var url = App.url('/master/rest/collections/:collectionID/items', {collectionID: collectionID});
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    // cập nhật item
    updateItem(collectionID, itemID, updateData) {
        var url = App.url('/master//rest/collections/:collectionID/items', {collectionID: collectionID});
        if (itemID) {
            url += '/' + itemID;
        }
        return $.rest({
            'url': url,
            'dataType': 'json',
            'method': 'put',
            'data': updateData
        });
    }

    // xóa item
    deleteItem(collectionID, itemID) {
        var url = App.url('/master/rest/collections/:collectionID/items', {collectionID: collectionID});
        if (itemID) {
            url += '/' + itemID;
        }
        return $.rest({
            'url': url,
            'method': 'delete'
        });
    }


}