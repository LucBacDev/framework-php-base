class TaskKanban extends PureComponent {

    constructor(props) {
        super(props);
        this.taskModel = new TaskModel();
        this.employeeModel = new EmployeeModel();
        
        this.INITIAL_COLUMNS = {
            'Mới': { id: 'Mới', title: 'Mới', items: [] },
            'Đang thực hiện': { id: 'Đang thực hiện', title: 'Đang thực hiện', items: [] },
            'Chờ duyệt': { id: 'Chờ duyệt', title: 'Chờ duyệt', items: [] },
            'Hoàn thành': { id: 'Hoàn thành', title: 'Hoàn thành', items: [] }
        };

        this.state = {
            boardData: JSON.parse(JSON.stringify(this.INITIAL_COLUMNS)),
            isModalOpen: false,
            editingTask: null,
            employees: [],
            filter: {
                search: ''
            },
            selectedAssignees: [], // Mảng chứa ID nhân viên được chọn
            assigneeSearch: '',
            showAssigneeDropdown: false
        };

        this.columnsRefs = {};
        this.sortables = [];
    }

    componentDidMount() {
        App.requireLogin();
        App.Component.trigger('leftNav.active', 'taskboard');
        this.fetchTasks();
        this.fetchEmployees();
    }

    fetchEmployees() {
        const userId = App.user ? App.user.id : null;
        
        this.employeeModel.getEmployees({ pageSize: 500, active: 1 }).then(res => {
            let emps = (res && res.data) ? (Array.isArray(res.data) ? res.data : (res.data.items || [])) : (res.items || res || []);
            
            // Kỹ thuật Tự dò tìm: Tìm chính mình trong danh sách để lấy depFK chuẩn
            const me = emps.find(e => e.id === userId);
            const myDepFK = me ? me.depFK : null;

            console.log('My discovered Department:', myDepFK);

            // Nếu không phải Admin tối cao và tìm thấy phòng ban của mình
            if (!App.isFullControl && myDepFK && myDepFK !== '0') {
                emps = emps.filter(e => e.depFK === myDepFK);
            }

            this.setState({ employees: emps });
        }).catch(err => {
            console.error('Lỗi tải danh sách nhân viên:', err);
        });
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
        this.taskModel.getTasks(this.state.filter).then((res) => {
            console.log('API Response:', res);
            const newBoard = JSON.parse(JSON.stringify(this.INITIAL_COLUMNS));
            
            // Lấy items: res có thể là { success, data: { items } } hoặc { items } hoặc mảng trực tiếp
            let items = [];
            if (res && res.data && Array.isArray(res.data.items)) {
                items = res.data.items;
            } else if (res && Array.isArray(res.items)) {
                items = res.items;
            } else if (Array.isArray(res)) {
                items = res;
            } else if (res && res.data && Array.isArray(res.data)) {
                items = res.data;
            }
            
            console.log('Extracted Items:', items);
            
            items.forEach(task => {
                // Giải mã progress từ attrs
                if (task.attrs) {
                    try {
                        const attrs = typeof task.attrs === 'string' ? JSON.parse(task.attrs) : task.attrs;
                        task.progress = attrs.progress || 0;
                    } catch(e) { task.progress = 0; }
                } else {
                    task.progress = 0;
                }

                const status = task.status || 'Mới';
                // Kiểm tra khớp tên cột (không phân biệt hoa thường, cắt khoảng trắng thừa)
                const matchedKey = Object.keys(newBoard).find(key => 
                    key.trim().toLowerCase() === status.trim().toLowerCase()
                );

                if (matchedKey) {
                    newBoard[matchedKey].items.push(task);
                } else {
                    newBoard['Mới'].items.push(task);
                }
            });
            
            console.log('New Board Data:', newBoard);
            this.setState({ boardData: newBoard });
        }).catch(err => {
            console.error('Fetch tasks error details:', err);
            let msg = 'Lỗi tải danh sách công việc';
            if (err.responseJSON && err.responseJSON.message) {
                msg = err.responseJSON.message;
            } else if (err.responseText) {
                // Nếu là lỗi HTML (Server crash), hiện 50 ký tự đầu để nhận diện
                msg = 'Lỗi Server: ' + err.responseText.substring(0, 100);
            }
            Alert.open(msg);
        });
    }

    handleDragEnd(evt) {
        const { from, to, item, oldIndex } = evt;
        const fromColId = from.getAttribute('data-column-id');
        const toColId = to.getAttribute('data-column-id');

        if (fromColId === toColId) {
            return;
        }

        // Kỹ thuật "Hoàn tác DOM": Đưa thẻ về vị trí cũ trước khi React render lại
        // Điều này giúp React không bị lỗi 'removeChild' vì nó vẫn thấy node ở chỗ cũ
        if (from !== to) {
            if (from.children[oldIndex]) {
                from.insertBefore(item, from.children[oldIndex]);
            } else {
                from.appendChild(item);
            }
        }

        const taskId = item.getAttribute('data-id');
        let apiCall = null;

        if (toColId === 'Đang thực hiện') {
            apiCall = (fromColId === 'Chờ duyệt') ? this.taskModel.reworkTask(taskId) : this.taskModel.startTask(taskId);
        } else if (toColId === 'Chờ duyệt') {
            apiCall = this.taskModel.submitTask(taskId);
        } else if (toColId === 'Hoàn thành') {
            apiCall = this.taskModel.approveTask(taskId);
        } else if (toColId === 'Mới') {
            apiCall = this.taskModel.resetTask(taskId);
        } else {
            Alert.open('Chuyển trạng thái không hợp lệ!');
            this.fetchTasks();
            return;
        }

        if (apiCall) {
            apiCall.then(() => {
                this.fetchTasks();
            }).catch(xhr => {
                // Ưu tiên lấy message từ responseJSON, nếu không có thử responseText, cuối cùng là mặc định
                let msg = 'Bạn không có quyền chuyển sang hoàn thành';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const res = JSON.parse(xhr.responseText);
                        if (res.message) msg = res.message;
                    } catch(e) {}
                }
                Alert.open(msg);
                this.fetchTasks();
            });
        }
    }

    openCreateModal() {
        this.setState({ editingTask: null, isModalOpen: true, selectedAssignees: [], assigneeSearch: '' }, () => {
            this.initEditor('');
        });
    }

    openEditModal(task) {
        const ids = task.assigneeIDs ? task.assigneeIDs.split(',').map(s => s.trim()) : [];
        this.setState({ editingTask: task, isModalOpen: true, selectedAssignees: ids, assigneeSearch: '' }, () => {
            this.initEditor(task.description || '');
        });
    }

    initEditor(content) {
        if (typeof Quill === 'undefined') {
            setTimeout(() => this.initEditor(content), 200);
            return;
        }

        const container = document.getElementById('editor-container');
        if (!container) return;

        // Xóa nội dung cũ nếu có
        container.innerHTML = '';
        
        this.quill = new Quill(container, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            },
            placeholder: 'Nhập mô tả chi tiết, chèn ảnh hoặc video tại đây...'
        });

        if (content) {
            this.quill.clipboard.dangerouslyPasteHTML(content);
        }
    }

    closeModal() {
        this.setState({ isModalOpen: false });
        this.quill = null;
    }

    saveTask(taskData) {
        // Lấy nội dung từ editor
        const description = this.quill ? this.quill.root.innerHTML : '';
        taskData.description = description;

        if (this.state.editingTask) {
            let promises = [];
            const t = this.state.editingTask;

            // Kiểm tra nếu chưa chọn ai (Jira style)
            if (!taskData.assignee || taskData.assignee.length === 0) {
                Alert.open('Vui lòng chọn ít nhất một người thực hiện');
                return;
            }

            // 1. Cập nhật thông tin chung + Tiến độ (Gộp chung để tránh xung đột ghi đè)
            const progressChanged = parseInt(taskData.progress) !== parseInt(t.progress);
            const infoChanged = taskData.title !== t.title || taskData.description !== t.description || taskData.priority !== t.priority;
            
            if (infoChanged || progressChanged) {
                promises.push(this.taskModel.updateTask(t.id, {
                    title: taskData.title,
                    description: taskData.description, // HTML content
                    priority: taskData.priority,
                    progress: taskData.progress
                }));
            }

            // 2. Cập nhật Deadline
            const oldDue = t.dueTime ? t.dueTime.substring(0, 16).replace(' ', 'T') : '';
            const newDue = taskData.due_time ? taskData.due_time.substring(0, 16) : '';
            if (newDue !== oldDue) {
                promises.push(this.taskModel.updateDeadline(t.id, taskData.due_time));
            }

            // 3. Cập nhật người thực hiện (Assign To)
            const oldAssignees = t.assigneeIDs ? t.assigneeIDs.split(',').map(s => s.trim()).sort().join(',') : '';
            const newAssignees = [...taskData.assignee].sort().join(',');
            
            if (newAssignees !== oldAssignees) {
                promises.push(this.taskModel.assignIndividual(t.id, taskData.assignee));
            }

            if (promises.length > 0) {
                Promise.all(promises).then(() => {
                    Alert.open('Cập nhật thành công');
                    this.closeModal();
                    this.fetchTasks();
                }).catch(err => {
                    console.error('Update error:', err);
                    const msg = (err && err.responseJSON) ? err.responseJSON.message : 'Lỗi cập nhật dữ liệu';
                    Alert.open(msg);
                });
            } else {
                this.closeModal();
            }
        } else {
            // Logic cho Create Task
            if (!taskData.assignee || taskData.assignee.length === 0) {
                Alert.open('Vui lòng chọn ít nhất một người thực hiện');
                return;
            }

            this.taskModel.createTask(taskData).then((res) => {
                this.taskModel.assignIndividual(res.data.id, taskData.assignee).then(() => {
                    Alert.open('Tạo công việc thành công');
                    this.closeModal();
                    this.fetchTasks();
                }).catch(() => {
                    this.closeModal();
                    this.fetchTasks();
                });
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
                }).catch(xhr => Alert.open('Bạn không có quyền xoá'));
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
                    <div className="flex items-center gap-1">
                        {isDone && (
                            <span className="material-symbols-outlined text-teal-600 text-sm">check_circle</span>
                        )}
                        <span className="material-symbols-outlined text-slate-300 transition-opacity drag-handle cursor-grab hover:text-slate-500">drag_handle</span>
                    </div>
                </div>
                <h3 className={`font-bold mb-1 text-sm ${isDone ? 'text-slate-500 line-through' : 'text-slate-900'}`}>
                    {item.title}
                </h3>
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
                        {item.assigneeIDs && item.assigneeIDs.split(',').map((id, index) => {
                            const names = item.assignees ? item.assignees.split(', ') : [];
                            const name = names[index] || '??';
                            return (
                                <div 
                                    key={id} 
                                    title={name}
                                    className="w-6 h-6 rounded-full bg-teal-100 border-2 border-white flex items-center justify-center text-[9px] font-bold text-teal-700 shadow-sm"
                                >
                                    {name.substring(0, 2).toUpperCase()}
                                </div>
                            );
                        })}
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
            <div className="fixed inset-0 z-[1050] modal-overlay flex items-center justify-center p-6 bg-black/40 backdrop-blur-sm"
            >
                <style>
                    {`
            .modal-overlay {
                transform: none !important;
            }
            .modal-overlay * {
                transform: none !important;
            }
        `}
                </style>
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
                                // description được lấy trực tiếp từ Quill trong hàm saveTask
                                priority: e.target.priority ? e.target.priority.value : 'Vừa',
                                progress: e.target.progress ? parseInt(e.target.progress.value) : 0,
                                due_time: e.target.due_time.value,
                                assignee: this.state.selectedAssignees
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
                                <div className="space-y-2" >
                                    <label className="block text-slate-700 text-xs font-semibold">Độ ưu tiên</label>
                                    <div className="relative">
                                        <div className="flex gap-4">

                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="priority"
                                                    value="Thấp"
                                                    defaultChecked={task.priority === 'Thấp'}
                                                    className="accent-teal-600"
                                                />
                                                <span>Thấp</span>
                                            </label>

                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="priority"
                                                    value="Vừa"
                                                    defaultChecked={!task.priority || task.priority === 'Vừa'}
                                                    className="accent-teal-600"
                                                />
                                                <span>Vừa</span>
                                            </label>

                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="priority"
                                                    value="Cao"
                                                    defaultChecked={task.priority === 'Cao'}
                                                    className="accent-teal-600"
                                                />
                                                <span>Cao</span>
                                            </label>

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

                                <div className="space-y-2">
                                    <label className="block text-slate-700 text-xs font-semibold">Progress (%)</label>
                                    <input name="progress" type="number" min="0" max="100" defaultValue={task.progress || 0} className="w-full px-4 py-3 bg-white border border-slate-300 rounded-lg text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/10 transition-all outline-none" />
                                </div>
                            </div>

                            {/* Assignee Selection (Jira Style Multi-select) */}
                            <div className="space-y-2">
                                <label className="block text-slate-700 text-xs font-semibold">Assign To</label>
                                <div className="relative">
                                    {/* Selected Tags Area */}
                                    <div className="min-h-[44px] p-1.5 bg-white border border-slate-300 rounded-lg flex flex-wrap gap-2 items-center focus-within:border-teal-600 focus-within:ring-2 focus-within:ring-teal-600/10 transition-all">
                                        {this.state.selectedAssignees.map(id => {
                                            const emp = this.state.employees.find(e => e.id === id);
                                            if (!emp) return null;
                                            return (
                                                <div key={id} className="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded text-sm text-slate-700 font-medium">
                                                    <span>{emp.fullname}</span>
                                                    <button 
                                                        type="button"
                                                        onClick={() => {
                                                            this.setState({ 
                                                                selectedAssignees: this.state.selectedAssignees.filter(sid => sid !== id) 
                                                            });
                                                        }}
                                                        className="hover:text-red-500 transition-colors"
                                                    >
                                                        <span className="material-symbols-outlined text-[16px]">close</span>
                                                    </button>
                                                </div>
                                            );
                                        })}
                                        <input 
                                            className="flex-1 min-w-[120px] outline-none text-sm px-2 py-1"
                                            placeholder={this.state.selectedAssignees.length === 0 ? "Tìm tên nhân sự..." : ""}
                                            value={this.state.assigneeSearch}
                                            onChange={(e) => this.setState({ assigneeSearch: e.target.value, showAssigneeDropdown: true })}
                                            onFocus={() => this.setState({ showAssigneeDropdown: true })}
                                        />
                                    </div>

                                    {/* Dropdown Results */}
                                    {this.state.showAssigneeDropdown && (this.state.assigneeSearch || this.state.showAssigneeDropdown) && (
                                        <div className="absolute z-[1100] top-full left-0 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl max-h-[200px] overflow-y-auto overflow-x-hidden">
                                            {this.state.employees
                                                .filter(emp => !this.state.selectedAssignees.includes(emp.id))
                                                .filter(emp => emp.fullname.toLowerCase().includes(this.state.assigneeSearch.toLowerCase()))
                                                .map(emp => (
                                                    <div 
                                                        key={emp.id}
                                                        onClick={() => {
                                                            this.setState({ 
                                                                selectedAssignees: [...this.state.selectedAssignees, emp.id],
                                                                assigneeSearch: '',
                                                                showAssigneeDropdown: false
                                                            });
                                                        }}
                                                        className="px-4 py-2.5 hover:bg-slate-50 cursor-pointer text-sm text-slate-700 transition-colors flex items-center justify-between group"
                                                    >
                                                        <span>{emp.fullname}</span>
                                                        <span className="material-symbols-outlined text-[18px] text-slate-300 opacity-0 group-hover:opacity-100">add</span>
                                                    </div>
                                                ))
                                            }
                                            {this.state.employees.length === 0 && (
                                                <div className="p-4 text-center text-slate-400 text-xs">Không có nhân sự</div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                {/* Backdrop to close dropdown */}
                                {this.state.showAssigneeDropdown && (
                                    <div 
                                        className="fixed inset-0 z-[1090]" 
                                        onClick={() => this.setState({ showAssigneeDropdown: false })}
                                    />
                                )}
                            </div>

                            {/* Description (Rich Text) */}
                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <label className="block text-slate-700 text-xs font-semibold">Description</label>
                                </div>
                                <div className="bg-white rounded-lg border border-slate-300 overflow-hidden">
                                    <div id="editor-container" style={{ minHeight: '200px', border: 'none' }}></div>
                                </div>
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
        // ... (phần còn lại của hàm onSubmit để xử lý dữ liệu mới)
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
