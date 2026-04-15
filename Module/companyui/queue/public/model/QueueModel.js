class MessageQueueModel {
    // api lấy danh sách AI trong quản trị
    getMessageQueueManager(filter) {
        let url = App.url('/master/rest/queue/messages');
        return $.rest({
            'url': url,
            'data': filter
        });
    }

    retry(message_ids) {
        let url = App.url('/master/rest/queue/messages/retry/:ids', {ids: message_ids});
        return $.rest({
            'url': url
        });
    }

    updateMessage(message_id, body) {
        let url = App.url('/master/rest/queue/messages/:id/update', {id: message_id});

        return $.rest({
            'url': url,
            'data': JSON.parse(body),
            'method': 'post'
        });
    }
}