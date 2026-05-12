@extends('layouts.app')

@section('title', 'Task Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Task Management</h1>
            <p class="text-muted mb-0">Create, assign, and track tasks for teachers and staff</p>
        </div>
        <div class="text-end">
            <button class="btn btn-primary" onclick="openCreateTaskModal()">
                <i class="fas fa-plus me-2"></i>Create Task
            </button>
        </div>
    </div>

    <!-- Task Statistics -->
    <div class="row mb-4">
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-primary">
                <div class="card-body">
                    <h4 class="mb-0 text-primary">{{ $tasks->total() }}</h4>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-secondary">
                <div class="card-body">
                    <h4 class="mb-0 text-secondary">{{ $tasks->where('status', 'pending')->count() }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-info">
                <div class="card-body">
                    <h4 class="mb-0 text-info">{{ $tasks->where('status', 'in_progress')->count() }}</h4>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-success">
                <div class="card-body">
                    <h4 class="mb-0 text-success">{{ $tasks->where('status', 'completed')->count() }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-danger">
                <div class="card-body">
                    <h4 class="mb-0 text-danger">{{ $tasks->where('status', 'overdue')->count() }}</h4>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-4 mb-3">
            <div class="card h-100 text-center border-left-warning">
                <div class="card-body">
                    <h4 class="mb-0 text-warning">{{ $tasks->where('priority', 'urgent')->count() }}</h4>
                    <small class="text-muted">Urgent</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('principal.tasks.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="priority" class="form-label">Priority</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="all" {{ request('priority') === 'all' || !request('priority') ? 'selected' : '' }}>All Priorities</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="assignee" class="form-label">Assignee</label>
                        <select class="form-select" id="assignee" name="assignee">
                            <option value="all" {{ request('assignee') === 'all' || !request('assignee') ? 'selected' : '' }}>All Assignees</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}" {{ request('assignee') == $assignee->id ? 'selected' : '' }}>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Task Board (Kanban Style) -->
    <div class="row">
        <!-- Pending Tasks -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Pending
                        <span class="badge bg-white text-secondary float-end">{{ $tasks->where('status', 'pending')->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2" id="pending-tasks">
                    @foreach($tasks->where('status', 'pending') as $task)
                        @include('principal.tasks.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- In Progress Tasks -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-spinner me-2"></i>In Progress
                        <span class="badge bg-white text-info float-end">{{ $tasks->where('status', 'in_progress')->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2" id="in-progress-tasks">
                    @foreach($tasks->where('status', 'in_progress') as $task)
                        @include('principal.tasks.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Completed
                        <span class="badge bg-white text-success float-end">{{ $tasks->where('status', 'completed')->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2" id="completed-tasks">
                    @foreach($tasks->where('status', 'completed') as $task)
                        @include('principal.tasks.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Overdue
                        <span class="badge bg-white text-danger float-end">{{ $tasks->where('status', 'overdue')->count() }}</span>
                    </h6>
                </div>
                <div class="card-body p-2" id="overdue-tasks">
                    @foreach($tasks->where('status', 'overdue') as $task)
                        @include('principal.tasks.task-card', ['task' => $task])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Table View (Alternative) -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Task List
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Assignee</th>
                            <th>Due Date</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $task->title }}</div>
                                    @if($task->description)
                                        <small class="text-muted">{{ Str::limit($task->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                            {{ substr($task->assignee->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $task->assignee->name }}</div>
                                            <small class="text-muted">{{ $task->assignee->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $task->getFormattedDueDate() }}</div>
                                    <small class="text-{{ $task->isOverdue() ? 'danger' : 'muted' }}">
                                        {{ $task->getDueDateStatus() }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->getPriorityColor() }}">
                                        {{ $task->getPriorityDisplay() }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->getStatusColor() }}">
                                        {{ $task->getStatusDisplay() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="editTask({{ $task->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="updateTaskStatus({{ $task->id }}, 'completed')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @if($task->canBeUpdatedBy(Auth::user()))
                                            <button class="btn btn-outline-danger" onclick="deleteTask({{ $task->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">No tasks found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTaskForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskTitle" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="taskTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskPriority" class="form-label">Priority *</label>
                                <select class="form-select" id="taskPriority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskAssignee" class="form-label">Assignee *</label>
                                <select class="form-select" id="taskAssignee" name="assigned_to" required>
                                    <option value="">Select Assignee</option>
                                    @foreach($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="taskDueDate" class="form-label">Due Date *</label>
                                <input type="date" class="form-control" id="taskDueDate" name="due_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="taskRemarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="taskRemarks" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm">
                <input type="hidden" id="editTaskId" name="task_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editTaskTitle" class="form-label">Title *</label>
                                <input type="text" class="form-control" id="editTaskTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editTaskPriority" class="form-label">Priority *</label>
                                <select class="form-select" id="editTaskPriority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTaskDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editTaskAssignee" class="form-label">Assignee *</label>
                                <select class="form-select" id="editTaskAssignee" name="assigned_to" required>
                                    <option value="">Select Assignee</option>
                                    @foreach($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="editTaskDueDate" class="form-label">Due Date *</label>
                                <input type="date" class="form-control" id="editTaskDueDate" name="due_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editTaskRemarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="editTaskRemarks" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #0d6efd !important; }
.border-left-secondary { border-left: 4px solid #6c757d !important; }
.border-left-info { border-left: 4px solid #0dcaf0 !important; }
.border-left-success { border-left: 4px solid #198754 !important; }
.border-left-danger { border-left: 4px solid #dc3545 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }

.task-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.task-card .task-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 8px;
}

.task-card .task-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.task-card .task-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
}

.task-card .task-assignee {
    display: flex;
    align-items: center;
    gap: 6px;
}

.task-card .task-actions {
    display: flex;
    gap: 4px;
}

.task-card .task-actions button {
    padding: 2px 6px;
    font-size: 0.75rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.task-card .task-actions button:hover {
    opacity: 0.8;
}

.avatar-sm {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

@media (max-width: 768px) {
    .task-card {
        padding: 8px;
        margin-bottom: 8px;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeTaskManagement();
});

function initializeTaskManagement() {
    // Initialize date inputs with minimum date as today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('taskDueDate').min = today;
    document.getElementById('editTaskDueDate').min = today;
    
    // Auto-refresh every 5 minutes
    setInterval(refreshTasks, 300000);
}

function openCreateTaskModal() {
    const modal = new bootstrap.Modal(document.getElementById('createTaskModal'));
    modal.show();
}

function editTask(taskId) {
    fetch(`/principal/tasks/${taskId}/edit`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate edit form
            document.getElementById('editTaskId').value = data.task.id;
            document.getElementById('editTaskTitle').value = data.task.title;
            document.getElementById('editTaskDescription').value = data.task.description || '';
            document.getElementById('editTaskAssignee').value = data.task.assigned_to;
            document.getElementById('editTaskDueDate').value = data.task.due_date;
            document.getElementById('editTaskPriority').value = data.task.priority;
            document.getElementById('editTaskRemarks').value = data.task.remarks || '';
            
            const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
            modal.show();
        } else {
            showToast('Error', data.message || 'Failed to load task', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to load task', 'error');
    });
}

function updateTaskStatus(taskId, status) {
    fetch(`/principal/tasks/${taskId}/status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Move task card to appropriate column
            moveTaskCard(taskId, status);
            // Update task data
            updateTaskCardData(taskId, data.task);
        } else {
            showToast('Error', data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to update status', 'error');
    });
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    fetch(`/principal/tasks/${taskId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Remove task card
            const taskCard = document.getElementById(`task-${taskId}`);
            if (taskCard) {
                taskCard.remove();
            }
        } else {
            showToast('Error', data.message || 'Failed to delete task', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to delete task', 'error');
    });
}

function moveTaskCard(taskId, newStatus) {
    const taskCard = document.getElementById(`task-${taskId}`);
    if (!taskCard) return;
    
    // Remove from current column
    taskCard.remove();
    
    // Add to new column
    const targetColumn = document.getElementById(`${newStatus}-tasks`);
    if (targetColumn) {
        targetColumn.appendChild(taskCard);
    }
}

function updateTaskCardData(taskId, taskData) {
    const taskCard = document.getElementById(`task-${taskId}`);
    if (!taskCard) return;
    
    // Update task card content
    // This would update the task card display with new data
    console.log('Task updated:', taskData);
}

function refreshTasks() {
    window.location.reload();
}

function showToast(title, message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <strong>${title}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Form submissions
document.getElementById('createTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/principal/tasks', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('createTaskModal')).hide();
            this.reset();
            // Reload to show new task
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error', data.message || 'Failed to create task', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to create task', 'error');
    });
});

document.getElementById('editTaskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const taskId = document.getElementById('editTaskId').value;
    const formData = new FormData(this);
    
    fetch(`/principal/tasks/${taskId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editTaskModal')).hide();
            // Reload to show updated task
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast('Error', data.message || 'Failed to update task', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to update task', 'error');
    });
});
</script>
@endpush
