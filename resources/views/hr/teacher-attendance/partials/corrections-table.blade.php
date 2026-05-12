<div class="table-responsive">
    <table class="table table-hover">
        <thead class="table-light">
            <tr>
                <th>Teacher Name</th>
                <th>Date</th>
                <th>Current Status</th>
                <th>Requested Status</th>
                <th>Reason</th>
                <th>Requested By</th>
                <th>Requested At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($corrections as $correction)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                {{ strtoupper(substr($correction->teacher->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $correction->teacher->name }}</div>
                                <small class="text-muted">{{ $correction->teacher->employee_code ?? 'N/A' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $correction->teacherAttendance->date }}</td>
                    <td>
                        <span class="badge bg-secondary">
                            {{ ucfirst($correction->current_status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-info">
                            {{ ucfirst($correction->requested_status) }}
                        </span>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip" title="{{ $correction->reason }}">
                            {{ Str::limit($correction->reason, 50) }}
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 0.75rem;">
                                {{ strtoupper(substr($correction->teacher->name, 0, 1)) }}
                            </div>
                            <span>{{ $correction->teacher->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            {{ $correction->created_at->format('M j, Y') }}
                            <br>
                            <span class="text-muted">{{ $correction->created_at->format('g:i A') }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            @if($correction->status === 'pending')
                                <button type="button" 
                                        class="btn btn-success" 
                                        onclick="approveCorrection({{ $correction->id }})"
                                        data-bs-toggle="tooltip" 
                                        title="Approve request">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="showRejectModal({{ $correction->id }})"
                                        data-bs-toggle="tooltip" 
                                        title="Reject request">
                                    <i class="fas fa-times"></i>
                                </button>
                            @else
                                <span class="badge bg-{{ $correction->getStatusBadgeColor() }}">
                                    {{ $correction->getStatusDisplay() }}
                                </span>
                                
                                @if($correction->reviewed_by)
                                    <button type="button" 
                                            class="btn btn-outline-info btn-sm ms-1" 
                                            data-bs-toggle="tooltip" 
                                            title="Reviewed by {{ $correction->reviewer->name }}">
                                        <i class="fas fa-info"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                        
                        @if($correction->status === 'rejected' && $correction->rejection_reason)
                            <button type="button" 
                                    class="btn btn-outline-warning btn-sm ms-1 mt-1" 
                                    data-bs-toggle="tooltip" 
                                    title="{{ $correction->rejection_reason }}">
                                <i class="fas fa-comment"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No correction requests found.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($corrections->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Showing {{ $corrections->firstItem() }} to {{ $corrections->lastItem() }} of {{ $corrections->total() }} entries
        </div>
        <div>
            {{ $corrections->links() }}
        </div>
    </div>
@endif
