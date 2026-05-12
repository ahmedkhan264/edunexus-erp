<div class="task-card" id="task-{{ $task->id }}">
    <div class="task-header">
        <div class="task-title">{{ $task->title }}</div>
        <div class="task-actions">
            <button class="btn btn-sm btn-outline-primary" onclick="editTask({{ $task->id }})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            @if($task->status !== 'completed')
                <button class="btn btn-sm btn-outline-success" onclick="updateTaskStatus({{ $task->id }}, 'completed')" title="Complete">
                    <i class="fas fa-check"></i>
                </button>
            @endif
            @if($task->canBeUpdatedBy(Auth::user()))
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTask({{ $task->id }})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            @endif
        </div>
    </div>
    
    @if($task->description)
        <div class="task-description text-muted small mb-2">
            {{ Str::limit($task->description, 80) }}
        </div>
    @endif
    
    <div class="task-meta">
        <div class="task-assignee">
            <div class="avatar-sm bg-primary text-white" style="background-color: {{ $task->assignee->getAvatarColor() }};">
                {{ substr($task->assignee->name, 0, 1) }}
            </div>
            <span class="small">{{ $task->assignee->name }}</span>
        </div>
        <div class="task-priority">
            <span class="badge bg-{{ $task->getPriorityColor() }} badge-sm">
                {{ $task->getPriorityDisplay() }}
            </span>
        </div>
    </div>
    
    <div class="task-due mt-2">
        <div class="small text-muted">
            <i class="fas fa-calendar-alt me-1"></i>
            {{ $task->getFormattedDueDate() }}
        </div>
        <div class="small text-{{ $task->isOverdue() ? 'danger' : 'muted' }}">
            {{ $task->getDueDateStatus() }}
        </div>
    </div>
    
    @if($task->remarks)
        <div class="task-remarks mt-2">
            <div class="small text-info">
                <i class="fas fa-sticky-note me-1"></i>
                {{ Str::limit($task->remarks, 50) }}
            </div>
        </div>
    @endif
</div>
