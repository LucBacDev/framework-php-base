class TaskModel {

    constructor() {
        this.apiBase = App.url('/:siteID/rest/task/tasks', {siteID: App.siteID});
    }

    getTasks(filter = {}) {
        let a = $.rest({
            url: this.apiBase,
            data: filter
        });
         console.log('apiBase: ', this.apiBase);
        console.log('filter: ', filter);
        console.log('a: ', a);
        return a;
    }

    createTask(data) {
        return $.rest({
            url: this.apiBase,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json'
        });
    }

    deleteTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id,
            method: 'DELETE'
        });
    }

    startTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/start',
            method: 'POST',
            data: JSON.stringify({ note: 'Bắt đầu từ Kanban' }),
            contentType: 'application/json'
        });
    }

    submitTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/submit',
            method: 'POST',
            data: JSON.stringify({ note: 'Gửi duyệt từ Kanban' }),
            contentType: 'application/json'
        });
    }

    approveTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/approve',
            method: 'POST',
            data: JSON.stringify({ note: 'Phê duyệt từ Kanban' }),
            contentType: 'application/json'
        });
    }

    reworkTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/rework',
            method: 'POST',
            data: JSON.stringify({ note: 'Yêu cầu làm lại từ Kanban' }),
            contentType: 'application/json'
        });
    }

    updateProgress(id, progress) {
        return $.rest({
            url: this.apiBase + '/' + id + '/progress',
            method: 'POST',
            data: JSON.stringify({ progress: progress, note: 'Cập nhật từ Kanban' }),
            contentType: 'application/json'
        });
    }

    updateDeadline(id, dueTime) {
        return $.rest({
            url: this.apiBase + '/' + id + '/deadline',
            method: 'PATCH',
            data: JSON.stringify({ dueTime: dueTime }),
            contentType: 'application/json'
        });
    }
}
