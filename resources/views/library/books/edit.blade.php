@extends('layouts.app')

@section('title', 'Edit Book - ' . $book->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Book</h1>
            <p class="text-muted mb-0">Update book information for: {{ $book->title }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('library.books.show', $book) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-eye me-2"></i>View Book
            </a>
            <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Catalog
            </a>
        </div>
    </div>

    <!-- Edit Book Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Book Information
                    </h6>
                </div>
                <div class="card-body">
                    <form id="editBookForm" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <label for="title" class="form-label">Book Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="{{ $book->title }}" required>
                                <div class="form-text">Enter the full title of the book</div>
                            </div>
                            <div class="col-md-6">
                                <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="author" name="author" 
                                       value="{{ $book->author }}" required>
                                <div class="form-text">Enter the author's full name</div>
                            </div>
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="isbn" name="isbn" 
                                       value="{{ $book->isbn }}" required>
                                <div class="form-text">10 or 13 digit ISBN number</div>
                            </div>
                            <div class="col-md-6">
                                <label for="publisher" class="form-label">Publisher</label>
                                <input type="text" class="form-control" id="publisher" name="publisher" 
                                       value="{{ $book->publisher }}">
                                <div class="form-text">Publisher name (optional)</div>
                            </div>
                            <div class="col-md-4">
                                <label for="publication_year" class="form-label">Publication Year</label>
                                <input type="number" class="form-control" id="publication_year" name="publication_year" 
                                       value="{{ $book->publication_year }}" min="1900" max="{{ date('Y') + 1 }}">
                                <div class="form-text">Year of publication</div>
                            </div>
                            <div class="col-md-4">
                                <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ $book->category == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                    <option value="Other" {{ $book->category == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="language" class="form-label">Language <span class="text-danger">*</span></label>
                                <select class="form-select" id="language" name="language" required>
                                    @foreach($languages as $language)
                                        <option value="{{ $language }}" {{ $book->language == $language ? 'selected' : '' }}>
                                            {{ $language }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="pages" class="form-label">Number of Pages</label>
                                <input type="number" class="form-control" id="pages" name="pages" 
                                       value="{{ $book->pages }}" min="1" max="10000">
                                <div class="form-text">Total pages in the book</div>
                            </div>
                            <div class="col-md-4">
                                <label for="total_copies" class="form-label">Total Copies <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="total_copies" name="total_copies" 
                                       value="{{ $book->total_copies }}" min="1" max="1000" required>
                                <div class="form-text">Number of copies available</div>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="available" {{ $book->status == 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="unavailable" {{ $book->status == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                    <option value="maintenance" {{ $book->status == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="{{ $book->location }}">
                                <div class="form-text">Physical location in library (e.g., Shelf A-1)</div>
                            </div>
                            <div class="col-md-6">
                                <label for="cover_image" class="form-label">Cover Image</label>
                                <input type="file" class="form-control" id="cover_image" name="cover_image" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif">
                                <div class="form-text">JPEG, PNG, JPG, or GIF (Max 2MB). Leave empty to keep current image.</div>
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ $book->description }}</textarea>
                                <div class="form-text">Brief description of the book (optional)</div>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2">{{ $book->notes }}</textarea>
                                <div class="form-text">Any additional notes about the book</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('library.books.show', $book) }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-warning" id="submitBtn">
                                <i class="fas fa-save me-2"></i>Update Book
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Preview Card -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Live Preview
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div id="previewImage" class="preview-image">
                            <img src="{{ $book->getCoverImageUrl() }}" alt="Book Cover" class="img-fluid rounded">
                        </div>
                    </div>
                    <h6 id="previewTitle" class="text-center mb-2">{{ $book->title }}</h6>
                    <p id="previewAuthor" class="text-muted text-center mb-2">{{ $book->author }}</p>
                    <div class="text-center">
                        <span id="previewCategory" class="badge bg-secondary">{{ $book->category }}</span>
                        <span id="previewStatus" class="badge bg-{{ $book->getStatusColor() }}">
                            {{ $book->getStatusDisplay() }}
                        </span>
                    </div>
                    <hr>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>ISBN:</span>
                            <span id="previewIsbn">{{ $book->isbn }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Language:</span>
                            <span id="previewLanguage">{{ $book->language }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Copies:</span>
                            <span id="previewCopies">{{ $book->total_copies }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Location:</span>
                            <span id="previewLocation">{{ $book->location ?: '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Current Stats -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Current Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-value">{{ $book->total_copies }}</div>
                            <div class="stat-label">Total Copies</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-success">{{ $book->available_copies }}</div>
                            <div class="stat-label">Available</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-warning">{{ $book->issued_copies }}</div>
                            <div class="stat-label">Issued</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-info">{{ $book->getTotalBorrowsCount() }}</div>
                            <div class="stat-label">Total Borrows</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.preview-image {
    width: 150px;
    height: 200px;
    margin: 0 auto;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.preview-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sticky-top {
    position: sticky;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.card-header {
    border-bottom: none;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 992px) {
    .sticky-top {
        position: relative;
        margin-top: 20px;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editBookForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Live preview updates
    const titleInput = document.getElementById('title');
    const authorInput = document.getElementById('author');
    const isbnInput = document.getElementById('isbn');
    const categorySelect = document.getElementById('category');
    const languageSelect = document.getElementById('language');
    const totalCopiesInput = document.getElementById('total_copies');
    const locationInput = document.getElementById('location');
    const statusSelect = document.getElementById('status');
    const coverImageInput = document.getElementById('cover_image');
    
    // Update preview on input change
    titleInput.addEventListener('input', function() {
        document.getElementById('previewTitle').textContent = this.value || 'Book Title';
    });
    
    authorInput.addEventListener('input', function() {
        document.getElementById('previewAuthor').textContent = this.value || 'Author Name';
    });
    
    isbnInput.addEventListener('input', function() {
        document.getElementById('previewIsbn').textContent = this.value || '-';
    });
    
    categorySelect.addEventListener('change', function() {
        const categoryBadge = document.getElementById('previewCategory');
        categoryBadge.textContent = this.value || 'Category';
        categoryBadge.className = 'badge bg-secondary';
    });
    
    languageSelect.addEventListener('change', function() {
        document.getElementById('previewLanguage').textContent = this.value || '-';
    });
    
    totalCopiesInput.addEventListener('input', function() {
        document.getElementById('previewCopies').textContent = this.value || '-';
    });
    
    locationInput.addEventListener('input', function() {
        document.getElementById('previewLocation').textContent = this.value || '-';
    });
    
    statusSelect.addEventListener('change', function() {
        const statusBadge = document.getElementById('previewStatus');
        statusBadge.textContent = this.options[this.selectedIndex].text;
        statusBadge.className = 'badge bg-' + getStatusColor(this.value);
    });
    
    // Handle cover image preview
    coverImageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.querySelector('#previewImage img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating Book...';
        submitBtn.disabled = true;
        
        const formData = new FormData(form);
        formData.append('_method', 'PUT');
        
        fetch(`{{ route('library.books.update', $book) }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route('library.books.show', $book) }}';
                }, 1500);
            } else {
                showToast('Error', data.message || 'Failed to update book', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'Failed to update book', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Update Book';
            submitBtn.disabled = false;
        });
    });
});

function getStatusColor(status) {
    const colors = {
        'available': 'success',
        'unavailable': 'danger',
        'maintenance': 'warning'
    };
    return colors[status] || 'secondary';
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
</script>
@endpush
