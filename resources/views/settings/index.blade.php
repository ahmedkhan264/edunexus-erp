@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">System Settings</h1>
            <p class="text-muted mb-0">Manage system configuration and preferences</p>
        </div>
        <div class="text-end">
            <div class="btn-group" role="group">
                <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportSettings('all')">
                        <i class="fas fa-file-export me-2"></i>Export All Settings
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportSettings('{{ $category }}')">
                        <i class="fas fa-file-export me-2"></i>Export Current Category
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-outline-info" onclick="showImportModal()">
                <i class="fas fa-file-import me-2"></i>Import
            </button>
            <button class="btn btn-outline-warning" onclick="resetToDefaults()">
                <i class="fas fa-undo me-2"></i>Reset to Defaults
            </button>
            <button class="btn btn-outline-danger" onclick="clearCache()">
                <i class="fas fa-trash me-2"></i>Clear Cache
            </button>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="categoryTabs" role="tablist">
                @foreach($categories as $cat)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $cat === $category ? 'active' : '' }}" 
                                id="{{ $cat }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $cat }}" 
                                type="button" 
                                role="tab"
                                onclick="loadCategory('{{ $cat }}')">
                            <i class="fas fa-{{ getCategoryIcon($cat) }} me-2"></i>{{ ucfirst($cat) }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="tab-content" id="categoryContent">
        @foreach($categories as $cat)
            <div class="tab-pane fade {{ $cat === $category ? 'show active' : '' }}" 
                 id="{{ $cat }}" 
                 role="tabpanel">
                <form id="settingsForm_{{ $cat }}">
                    @csrf
                    <input type="hidden" name="category" value="{{ $cat }}">
                    
                    @if(isset($groupedSettings[$cat]))
                        @foreach($groupedSettings[$cat] as $type => $typeSettings)
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-{{ getTypeIcon($type) }} me-2"></i>
                                        {{ ucfirst($type) }} Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($typeSettings as $setting)
                                            <div class="col-lg-6 mb-3">
                                                <div class="form-group">
                                                    <label for="setting_{{ $setting->key }}" class="form-label">
                                                        {{ $setting->title }}
                                                        @if($setting->is_public)
                                                            <span class="badge bg-success ms-1">Public</span>
                                                        @else
                                                            <span class="badge bg-secondary ms-1">Private</span>
                                                        @endif
                                                    </label>
                                                    
                                                    @if($setting->type === 'boolean')
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   id="setting_{{ $setting->key }}" 
                                                                   name="settings[{{ $setting->key }}]"
                                                                   value="1"
                                                                   {{ $setting->getTypedValue() ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="setting_{{ $setting->key }}">
                                                                {{ $setting->getTypedValue() ? 'Enabled' : 'Disabled' }}
                                                            </label>
                                                        </div>
                                                    @elseif($setting->type === 'number')
                                                        <input type="number" 
                                                               class="form-control" 
                                                               id="setting_{{ $setting->key }}" 
                                                               name="settings[{{ $setting->key }}]"
                                                               value="{{ $setting->getTypedValue() }}"
                                                               step="any">
                                                    @elseif($setting->type === 'json' || $setting->type === 'array')
                                                        <textarea class="form-control" 
                                                                  id="setting_{{ $setting->key }}" 
                                                                  name="settings[{{ $setting->key }}]"
                                                                  rows="4">{{ json_encode($setting->getTypedValue(), JSON_PRETTY_PRINT) }}</textarea>
                                                    @else
                                                        <input type="{{ str_contains($setting->key, 'password') ? 'password' : 'text' }}" 
                                                               class="form-control" 
                                                               id="setting_{{ $setting->key }}" 
                                                               name="settings[{{ $setting->key }}]"
                                                               value="{{ $setting->getTypedValue() }}">
                                                    @endif
                                                    
                                                    @if($setting->description)
                                                        <small class="form-text text-muted">{{ $setting->description }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No settings found in this category</h5>
                                <p class="text-muted">There are no settings configured for the {{ ucfirst($cat) }} category.</p>
                            </div>
                        </div>
                    @endif
                    
                    @if(isset($groupedSettings[$cat]) && $groupedSettings[$cat]->count() > 0)
                        <div class="text-end">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="resetCategory('{{ $cat }}')">
                                <i class="fas fa-undo me-2"></i>Reset Category
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        @endforeach
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="importForm">
                    @csrf
                    <div class="mb-3">
                        <label for="importData" class="form-label">Settings Data (JSON)</label>
                        <textarea class="form-control" 
                                  id="importData" 
                                  name="settings" 
                                  rows="10" 
                                  placeholder='[{"key": "setting_key", "value": "setting_value", ...}]'></textarea>
                        <div class="form-text">
                            Paste your JSON settings data here. Format: [{"key": "setting_key", "value": "setting_value", ...}]
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overwrite" name="overwrite">
                            <label class="form-check-label" for="overwrite">
                                Overwrite existing settings
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="importSettings()">
                    <i class="fas fa-file-import me-2"></i>Import Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Operation Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultsContent">
                <!-- Results will be displayed here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    border: 1px solid transparent;
    border-bottom: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #dee2e6 #dee2e6 #fff;
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.form-switch .form-check-input {
    width: 3em;
}

.card-header-tabs .nav-link {
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .nav-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
    }
    
    .nav-tabs .nav-link {
        white-space: nowrap;
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form submissions for each category
    @foreach($categories as $cat)
        initializeForm('{{ $cat }}');
    @endforeach
});

function initializeForm(category) {
    const form = document.getElementById(`settingsForm_${category}`);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            saveSettings(category);
        });
    }
}

function loadCategory(category) {
    // Update URL without page reload
    const url = new URL(window.location);
    url.searchParams.set('category', category);
    window.history.pushState({}, '', url);
    
    // Load the category content via AJAX
    fetch(`{{ route('settings.index') }}?category=${category}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the HTML and update the content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#categoryContent').innerHTML;
        
        document.getElementById('categoryContent').innerHTML = newContent;
        
        // Reinitialize form for the new content
        initializeForm(category);
    })
    .catch(error => {
        console.error('Error loading category:', error);
        showToast('Error', 'Failed to load category', 'error');
    });
}

function saveSettings(category) {
    const form = document.getElementById(`settingsForm_${category}`);
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const settings = {};
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('settings[')) {
            const settingKey = key.replace('settings[', '').replace(']', '');
            settings[settingKey] = value;
        }
    }
    
    const btn = form.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    btn.disabled = true;
    
    fetch('{{ route('settings.update') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            settings: settings,
            category: category
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Show detailed results if there are errors
            if (data.error_count > 0) {
                showResults(data.results, data.updated_count, data.error_count);
            }
        } else {
            showToast('Error', data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to save settings', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function resetCategory(category) {
    if (!confirm(`Are you sure you want to reset all ${category} settings to defaults? This action cannot be undone.`)) {
        return;
    }
    
    fetch('{{ route('settings.reset') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            category: category
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload the page to show updated settings
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to reset settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to reset settings', 'error');
    });
}

function resetToDefaults() {
    if (!confirm('Are you sure you want to reset ALL settings to defaults? This action cannot be undone.')) {
        return;
    }
    
    fetch('{{ route('settings.reset') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            category: 'all'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload the page to show updated settings
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to reset settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to reset settings', 'error');
    });
}

function clearCache() {
    if (!confirm('Are you sure you want to clear the settings cache?')) {
        return;
    }
    
    fetch('{{ route('settings.clear-cache') }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message || 'Failed to clear cache', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to clear cache', 'error');
    });
}

function exportSettings(category) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
    btn.disabled = true;
    
    fetch('{{ route('settings.export') }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Download the data as JSON file
            const dataStr = JSON.stringify(data.data, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = `settings_${category}_${new Date().toISOString().split('T')[0]}.json`;
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
            
            showToast('Success', `Exported ${data.count} settings`, 'success');
        } else {
            showToast('Error', data.message || 'Failed to export settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export settings', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showImportModal() {
    const modal = new bootstrap.Modal(document.getElementById('importModal'));
    modal.show();
}

function importSettings() {
    const form = document.getElementById('importForm');
    const formData = new FormData(form);
    
    const settingsText = formData.get('settings');
    let settings;
    
    try {
        settings = JSON.parse(settingsText);
    } catch (e) {
        showToast('Error', 'Invalid JSON format', 'error');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Importing...';
    btn.disabled = true;
    
    fetch('{{ route('settings.import') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            settings: settings,
            overwrite: formData.get('overwrite') === 'on'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
            modal.hide();
            
            // Show detailed results
            showResults(data.results, data.import_count, data.error_count);
            
            // Clear form
            form.reset();
            
            // Reload page if there were successful imports
            if (data.import_count > 0) {
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        } else {
            showToast('Error', data.message || 'Failed to import settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to import settings', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

function showResults(results, successCount, errorCount) {
    const content = document.getElementById('resultsContent');
    
    let html = `
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h4>${successCount}</h4>
                        <small>Successful</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h4>${errorCount}</h4>
                        <small>Errors</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h4>${successCount + errorCount}</h4>
                        <small>Total</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (errorCount > 0) {
        html += '<h6>Error Details:</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Setting</th><th>Status</th><th>Message</th></tr></thead><tbody>';
        
        for (const [key, result] of Object.entries(results)) {
            const statusClass = result.success ? 'success' : 'danger';
            html += `
                <tr>
                    <td><code>${key}</code></td>
                    <td><span class="badge bg-${statusClass}">${result.success ? 'Success' : 'Error'}</span></td>
                    <td>${result.message}</td>
                </tr>
            `;
        }
        
        html += '</tbody></table></div>';
    }
    
    content.innerHTML = html;
    
    const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
    modal.show();
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

// Helper functions for icons (these would typically be defined in PHP)
function getCategoryIcon(category) {
    const icons = {
        'general': 'cog',
        'school': 'graduation-cap',
        'academic': 'book',
        'library': 'book-reader',
        'hr': 'users',
        'system': 'server',
        'email': 'envelope',
        'security': 'shield-alt'
    };
    return icons[category] || 'cog';
}

function getTypeIcon(type) {
    const icons = {
        'text': 'font',
        'number': 'hashtag',
        'boolean': 'toggle-on',
        'json': 'code',
        'array': 'list'
    };
    return icons[type] || 'cog';
}
</script>
@endpush
