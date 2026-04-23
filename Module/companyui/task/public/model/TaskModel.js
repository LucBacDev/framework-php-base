class TaskModel {

    constructor() {
        this.apiBase = App.url('/:siteID/rest/task/tasks', {siteID: App.siteID});
    }

    getTasks(filter = {}) {
        return $.rest({
            url: this.apiBase,
            data: filter
        });
    }

    createTask(data) {
        return $.rest({
            url: this.apiBase,
            method: 'POST',
            data: data
        });
    }

    assignIndividual(taskId, assigneeId) {
        return $.rest({
            url: App.url('/:siteID/rest/task/tasks/:taskID/assign/individual', { siteID: App.siteID, taskID: taskId }),
            method: 'POST',
            data: { assigneeID: assigneeId }
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
            data: { note: 'Bắt đầu từ Kanban' }
        });
    }

    submitTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/submit',
            method: 'POST',
            data: { note: 'Gửi duyệt từ Kanban' }
        });
    }

    approveTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/approve',
            method: 'POST',
            data: { note: 'Phê duyệt từ Kanban' }
        });
    }

    reworkTask(id) {
        return $.rest({
            url: this.apiBase + '/' + id + '/rework',
            method: 'POST',
            data: { note: 'Yêu cầu làm lại từ Kanban' }
        });
    }

    updateProgress(id, progress) {
        return $.rest({
            url: this.apiBase + '/' + id + '/progress',
            method: 'POST',
            data: { progress: progress, note: 'Cập nhật từ Kanban' }
        });
    }

    updateDeadline(id, dueTime) {
        return $.rest({
            url: this.apiBase + '/' + id + '/deadline',
            method: 'PATCH',
            data: { dueTime: dueTime }
        });
    }
}
