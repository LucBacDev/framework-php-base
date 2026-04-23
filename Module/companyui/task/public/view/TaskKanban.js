class TaskKanban extends PureComponent {

    constructor(props) {
        super(props);
        this.taskModel = new TaskModel();
        
        this.INITIAL_COLUMNS = {
            'Mới': { id: 'Mới', title: 'TO DO', items: [] },
            'Đang thực hiện': { id: 'Đang thực hiện', title: 'IN PROGRESS', items: [] },
            'Chờ duyệt': { id: 'Chờ duyệt', title: 'PENDING APPROVAL', items: [] },
            'Hoàn thành': { id: 'Hoàn thành', title: 'DONE', items: [] }
        };

        this.state = {
            boardData: JSON.parse(JSON.stringify(this.INITIAL_COLUMNS)),
            isModalOpen: false,
            editingTask: null,
            filter: {
                search: ''
            }
        };

        this.columnsRefs = {};
        this.sortables = [];
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.active', 'taskboard');
        this.fetchTasks();
    }

    componentDidUpdate(prevProps, prevState) {
        if (prevState.boardData !== this.state.boardData) {
            this.initSortable();
        }
    }

    componentWillUnmount() {
        this.destroySortable();
    }

    initSortable() {
        if (typeof Sortable === 'undefined') {
            setTimeout(() => this.initSortable(), 200);
            return;
        }
        this.destroySortable();
        Object.keys(this.columnsRefs).forEach(colId => {
            const listEl = this.columnsRefs[colId];
            if (listEl) {
                const sortable = new Sortable(listEl, {
                    group: 'kanban',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    chosenClass: 'cursor-grabbing',
                    dragClass: 'cursor-grabbing',
                    handle: '.drag-handle',
                    onEnd: (evt) => this.handleDragEnd(evt)
                });
                this.sortables.push(sortable);
            }
        });
    }

    destroySortable() {
        this.sortables.forEach(s => s.destroy());
        this.sortables = [];
    }

    fetchTasks() {
        this.taskModel.getTasks(this.state.filter).then((data) => {
            const newBoard = JSON.parse(JSON.stringify(this.INITIAL_COLUMNS));
            const items = Array.isArray(data) ? data : (data.items || []);
            items.forEach(task => {
                const status = task.status || 'Mới';
                if (newBoard[status]) {
                    newBoard[status].items.push(task);
                } else {
                    newBoard['Mới'].items.push(task);
                }
            });
            this.setState({ boardData: newBoard });
        }).catch(err => {
            Alert.open('Lỗi tải danh sách công việc');
        });
    }

    handleDragEnd(evt) {
        const { from, to, item } = evt;
        const fromColId = from.getAttribute('data-column-id');
        const toColId = to.getAttribute('data-column-id');

        if (fromColId === toColId) {
            return this.fetchTasks();
        }

        const taskId = item.getAttribute('data-id');
        let apiCall = null;

        if (fromColId === 'Mới' && toColId === 'Đang thực hiện') {
            apiCall = this.taskModel.startTask(taskId);
        } else if (fromColId === 'Đang thực hiện' && toColId === 'Chờ duyệt') {
            apiCall = this.taskModel.submitTask(taskId);
        } else if (fromColId === 'Chờ duyệt' && toColId === 'Hoàn thành') {
            apiCall = this.taskModel.approveTask(taskId);
        } else if (fromColId === 'Chờ duyệt' && toColId === 'Đang thực hiện') {
            apiCall = this.taskModel.reworkTask(taskId);
        } else {
            Alert.open('Chuyển trạng thái không hợp lệ!');
            return this.fetchTasks();
        }

        if (apiCall) {
            apiCall.then(() => {
                this.fetchTasks();
            }).catch(xhr => {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Lỗi chuyển trạng thái';
                Alert.open(msg);
                this.fetchTasks();
            });
        }
    }

    openCreateModal() {
        this.setState({ editingTask: null, isModalOpen: true });
    }

    openEditModal(task) {
        this.setState({ editingTask: task, isModalOpen: true });
    }

    closeModal() {
        this.setState({ isModalOpen: false });
    }

    saveTask(taskData) {
        if (this.state.editingTask) {
            let promises = [];
            const t = this.state.editingTask;
            if (taskData.progress !== t.progress) {
                promises.push(this.taskModel.updateProgress(t.id, taskData.progress));
            }
            if (taskData.due_time !== t.dueTime) {
                promises.push(this.taskModel.updateDeadline(t.id, taskData.due_time));
            }
            if (promises.length > 0) {
                Promise.all(promises).then(() => {
                    this.closeModal();
                    this.fetchTasks();
                }).catch(err => {
                    Alert.open('Lỗi cập nhật');
                });
            } else {
                this.closeModal();
            }
        } else {
            this.taskModel.createTask(taskData).then(() => {
                this.closeModal();
                this.fetchTasks();
            }).catch(xhr => {
                Alert.open('Lỗi tạo mới');
            });
        }
    }

    deleteTask(id) {
        Confirm.open('Xác nhận xóa công việc?').then((resp) => {
            if (resp) {
                this.taskModel.deleteTask(id).then(() => {
                    this.closeModal();
                    this.fetchTasks();
                }).catch(xhr => Alert.open('Lỗi xóa công việc'));
            }
        });
    }

    renderCard(item, columnId) {
        const isDone = columnId === 'Hoàn thành';
        const priorityColorMap = {
            'Cao': 'red', 'Vừa': 'amber', 'Thấp': 'slate'
        };
        const priorityColor = priorityColorMap[item.priority] || 'blue';

        return (
            <div 
                key={item.id}
                data-id={item.id}
                className={`${isDone ? 'bg-slate-50 border-slate-100' : 'bg-white border-slate-200'} border p-3 rounded-lg shadow-sm hover:shadow-md transition-all cursor-grab active:cursor-grabbing group`}
                onClick={() => this.openEditModal(item)}
            >
                <div className="flex justify-between items-start mb-2">
                    <span className={`px-2 py-0.5 bg-${priorityColor}-50 text-${priorityColor}-600 rounded text-[10px] font-bold uppercase tracking-wider`}>
                        {item.priority || 'Vừa'}
                    </span>
                    {isDone ? (
                        <span className="material-symbols-outlined text-teal-600 text-sm">check_circle</span>
                    ) : (
                        <span className="material-symbols-outlined text-slate-300 transition-opacity drag-handle cursor-grab">drag_indicator</span>
                    )}
                </div>
                <h3 className={`font-bold mb-1 text-sm ${isDone ? 'text-slate-500 line-through' : 'text-slate-900'}`}>
                    {item.title}
                </h3>
                {item.description && (
                    <p className="text-slate-500 text-xs line-clamp-2 mb-3">{item.description}</p>
                )}
                <div className="flex items-center justify-between mt-4">
                    <div className="flex items-center gap-2">
                        {item.priority === 'Cao' && <span className="material-symbols-outlined text-red-500 text-sm">priority_high</span>}
                        {item.priority === 'Thấp' && <span className="material-symbols-outlined text-slate-400 text-sm">low_priority</span>}
                        {item.priority === 'Vừa' && <span className="material-symbols-outlined text-amber-500 text-sm">schedule</span>}
                        <span className="text-slate-400 text-[11px] font-medium">
                            {item.dueTime ? new Date(item.dueTime).toLocaleDateString() : 'No deadline'}
                        </span>
                    </div>
                    <div className="flex -space-x-1.5 items-center">
                        {item.progress !== undefined && (
                            <span className="text-xs font-bold text-teal-600 mr-2">{item.progress}%</span>
                        )}
                        {item.assignee && (
                            <div className="w-6 h-6 rounded-full bg-teal-100 flex items-center justify-center text-[10px] font-bold text-teal-700">
                                {item.assignee.substring(0, 2).toUpperCase()}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }

    renderColumn(column) {
        return (
            <div key={column.id} className="flex-shrink-0 flex flex-col" style={{ width: '280px' }}>
                <div className="flex items-center justify-between mb-4 bg-slate-50 py-2 z-10 rounded">
                    <div className="flex items-center gap-2 px-2">
                        <span className="text-xs text-slate-500 uppercase font-bold tracking-widest">{column.title}</span>
                        <span className={`w-5 h-5 ${column.id === 'Đang thực hiện' ? 'bg-teal-100 text-teal-700' : 'bg-slate-200 text-slate-600'} rounded-full flex items-center justify-center text-[10px] font-bold`}>
                            {column.items.length}
                        </span>
                    </div>
                </div>
                <div 
                    ref={el => this.columnsRefs[column.id] = el}
                    className={`flex flex-col gap-3 min-h-[100px] ${column.id === 'Hoàn thành' ? 'opacity-75' : ''}`} 
                    data-column-id={column.id}
                >
                    {column.items.map(item => this.renderCard(item, column.id))}
                </div>
            </div>
        );
    }

    renderModal() {
        if (!this.state.isModalOpen) return null;
        const task = this.state.editingTask || {};
        const isEdit = !!this.state.editingTask;

        return (
            <div className="fixed inset-0 z-[1050] modal-overlay flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm">
                <div className="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden border border-slate-200 flex flex-col max-h-full">
                    {/* Modal Header */}
                    <div className="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                        <div>
                            <h2 className="text-[30px] font-bold text-slate-900">{isEdit ? 'Edit Task' : 'Create New Task'}</h2>
                            <p className="text-[13px] text-slate-500 mt-1">{isEdit ? 'Update task details and progress.' : 'Assign a new task to your team workspace.'}</p>
                        </div>
                        <button onClick={() => this.closeModal()} className="text-slate-400 hover:text-slate-600 transition-colors p-2 hover:bg-slate-50 rounded-full">
                            <span className="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    {/* Form Content */}
                    <form id="taskForm" className="flex flex-col overflow-hidden" onSubmit={(e) => {
                            e.preventDefault();
                            this.saveTask({
                                title: e.target.title.value,
                                description: e.target.description.value,
                                priority: e.target.priority ? e.target.priority.value : 'Vừa',
                                progress: isEdit && e.target.progress ? parseInt(e.target.progress.value) : 0,
                                due_time: e.target.due_time.value
                            });
                        }}>
                        <div className="px-8 py-8 space-y-8 overflow-y-auto">
                            {/* Task Title */}
                            <div className="space-y-2">
                                <label className="block text-slate-700 text-xs font-semibold">Task Title</label>
                                <input required name="title" defaultValue={task.title} className="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all placeholder:text-slate-400 outline-none" placeholder="e.g. Implement User Permissions" type="text"/>
                            </div>

                            {/* Task Metadata Grid */}
                            <div className="grid grid-cols-2 gap-6">
                                {/* Task Priority Selection */}
                                <div className="space-y-2">
                                    <label className="block text-slate-700 text-xs font-semibold">Độ ưu tiên</label>
                                    <div className="relative">
                                        <select name="priority" defaultValue={task.priority || 'Vừa'} className="w-full appearance-none px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all cursor-pointer outline-none">
                                            <option value="Thấp">Thấp</option>
                                            <option value="Vừa">Vừa</option>
                                            <option value="Cao">Cao</option>
                                        </select>
                                        <div className="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                            <span className="material-symbols-outlined">expand_more</span>
                                        </div>
                                    </div>
                                </div>
                                {/* Due Date Picker */}
                                <div className="space-y-2">
                                    <label className="block text-slate-700 text-xs font-semibold">Due Date</label>
                                    <div className="relative">
                                        <input name="due_time" defaultValue={task.dueTime ? task.dueTime.substring(0, 16) : ''} className="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all cursor-pointer outline-none" type="datetime-local"/>
                                    </div>
                                </div>

                                {isEdit && (
                                    <div className="space-y-2">
                                        <label className="block text-slate-700 text-xs font-semibold">Progress (%)</label>
                                        <input name="progress" type="number" min="0" max="100" defaultValue={task.progress || 0} className="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all outline-none" />
                                    </div>
                                )}
                            </div>

                            {/* Assignee Selection (Custom Grid) */}
                            <div className="space-y-3">
                                <label className="block text-slate-700 text-xs font-semibold">Assign To</label>
                                <div className="grid grid-cols-4 gap-3">
                                    <label className="cursor-pointer group">
                                        <input className="hidden peer" name="assignee" type="radio" value="John D."/>
                                        <div className="flex flex-col items-center gap-2 p-3 border border-slate-200 rounded-lg peer-checked:border-teal-600 peer-checked:bg-teal-50 hover:bg-slate-50 transition-all">
                                            <img className="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=John+D&background=E5EEFF&color=00685F"/>
                                            <span className="text-[11px] text-slate-600 font-semibold">John D.</span>
                                        </div>
                                    </label>
                                    <label className="cursor-pointer group">
                                        <input defaultChecked className="hidden peer" name="assignee" type="radio" value="Sarah A."/>
                                        <div className="flex flex-col items-center gap-2 p-3 border border-slate-200 rounded-lg peer-checked:border-teal-600 peer-checked:bg-teal-50 hover:bg-slate-50 transition-all">
                                            <img className="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Sarah+A&background=FFDAD6&color=93000A"/>
                                            <span className="text-[11px] text-slate-600 font-semibold">Sarah A.</span>
                                        </div>
                                    </label>
                                    <label className="cursor-pointer group">
                                        <input className="hidden peer" name="assignee" type="radio" value="Mike K."/>
                                        <div className="flex flex-col items-center gap-2 p-3 border border-slate-200 rounded-lg peer-checked:border-teal-600 peer-checked:bg-teal-50 hover:bg-slate-50 transition-all">
                                            <img className="w-10 h-10 rounded-full" src="https://ui-avatars.com/api/?name=Mike+K&background=CBDBF5&color=0B1C30"/>
                                            <span className="text-[11px] text-slate-600 font-semibold">Mike K.</span>
                                        </div>
                                    </label>
                                    <div className="flex flex-col items-center justify-center gap-2 p-3 border border-dashed border-slate-300 rounded-lg hover:bg-slate-50 cursor-pointer">
                                        <div className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                            <span className="material-symbols-outlined">add</span>
                                        </div>
                                        <span className="text-[11px] text-slate-400 font-semibold">More</span>
                                    </div>
                                </div>
                            </div>

                            {/* Description */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <label className="block text-slate-700 text-xs font-semibold">Description</label>
                                    <div className="flex gap-2 text-slate-400">
                                        <span className="material-symbols-outlined text-lg cursor-pointer hover:text-slate-600">format_bold</span>
                                        <span className="material-symbols-outlined text-lg cursor-pointer hover:text-slate-600">format_italic</span>
                                        <span className="material-symbols-outlined text-lg cursor-pointer hover:text-slate-600">link</span>
                                        <span className="material-symbols-outlined text-lg cursor-pointer hover:text-slate-600">format_list_bulleted</span>
                                    </div>
                                </div>
                                <textarea name="description" defaultValue={task.description} className="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all resize-none placeholder:text-slate-400 outline-none" placeholder="Describe the task requirements and acceptance criteria..." rows="4"></textarea>
                            </div>
                        </div>

                        {/* Modal Footer */}
                        <div className="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
                            {isEdit ? (
                                <button type="button" onClick={() => this.deleteTask(task.id)} className="px-6 py-2.5 rounded-lg text-sm font-bold text-red-600 hover:bg-red-50 transition-all active:scale-95">
                                    Delete Task
                                </button>
                            ) : <div></div>}
                            <div className="flex items-center gap-3">
                                <button type="button" onClick={() => this.closeModal()} className="px-6 py-2.5 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-200 transition-all active:scale-95">
                                    Cancel
                                </button>
                                <button type="submit" className="px-8 py-2.5 rounded-lg text-sm font-bold bg-teal-600 text-white hover:bg-teal-700 transition-all active:scale-95 shadow-lg shadow-teal-600/10">
                                    {isEdit ? 'Save Changes' : 'Create Task'}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        );
    }

    render() {
        // Render inside AdminLayout
        return (
            <AdminLayout>
                <div className="p-4" style={{ fontFamily: "'Inter', sans-serif" }}>
                    <div className="mb-6 flex justify-between items-end">
                        <div>
                            <h1 className="text-2xl font-bold text-slate-800">Kanban Board</h1>
                        </div>
                        <div className="flex gap-2">
                            <button onClick={() => this.fetchTasks()} className="flex items-center gap-1 text-sm font-semibold text-slate-600 hover:text-slate-800 border border-slate-300 px-3 py-1.5 rounded-lg bg-white">
                                <span className="material-symbols-outlined text-sm">refresh</span> Tải lại
                            </button>
                            <button onClick={() => this.openCreateModal()} className="flex items-center gap-1 text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 px-3 py-1.5 rounded-lg">
                                <span className="material-symbols-outlined text-sm">add</span> Thêm mới
                            </button>
                        </div>
                    </div>

                    <div className="flex gap-4 overflow-x-auto pb-8 items-start min-h-[60vh]">
                        {Object.values(this.state.boardData).map(column => this.renderColumn(column))}
                    </div>
                </div>
                {this.renderModal()}
            </AdminLayout>
        );
    }
}
