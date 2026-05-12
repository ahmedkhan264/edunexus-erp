<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmissionFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_submission_id',
        'file_path',
        'original_name',
        'mime_type',
        'size'
    ];

    protected $casts = [
        'size' => 'integer'
    ];

    /**
     * Get the assignment submission that owns the file.
     */
    public function assignmentSubmission()
    {
        return $this->belongsTo(AssignmentSubmission::class);
    }

    /**
     * Get the file extension.
     */
    public function getFileExtension(): string
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the file type category.
     */
    public function getFileType(): string
    {
        $extension = strtolower($this->getFileExtension());
        
        if (in_array($extension, ['pdf'])) {
            return 'document';
        } elseif (in_array($extension, ['doc', 'docx'])) {
            return 'document';
        } elseif (in_array($extension, ['xls', 'xlsx'])) {
            return 'spreadsheet';
        } elseif (in_array($extension, ['ppt', 'pptx'])) {
            return 'presentation';
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return 'image';
        } elseif (in_array($extension, ['mp4', 'mov', 'avi', 'wmv'])) {
            return 'video';
        } elseif (in_array($extension, ['mp3', 'wav', 'ogg'])) {
            return 'audio';
        } elseif (in_array($extension, ['zip', 'rar', '7z'])) {
            return 'archive';
        } else {
            return 'file';
        }
    }

    /**
     * Get the file icon based on type.
     */
    public function getFileIcon(): string
    {
        $type = $this->getFileType();
        
        return match($type) {
            'document' => 'fas fa-file-alt',
            'spreadsheet' => 'fas fa-file-excel',
            'presentation' => 'fas fa-file-powerpoint',
            'image' => 'fas fa-file-image',
            'video' => 'fas fa-file-video',
            'audio' => 'fas fa-file-audio',
            'archive' => 'fas fa-file-archive',
            default => 'fas fa-file'
        };
    }

    /**
     * Get the file color based on type.
     */
    public function getFileColor(): string
    {
        $type = $this->getFileType();
        
        return match($type) {
            'document' => 'primary',
            'spreadsheet' => 'success',
            'presentation' => 'warning',
            'image' => 'info',
            'video' => 'danger',
            'audio' => 'secondary',
            'archive' => 'dark',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get the download URL for the file.
     */
    public function getDownloadUrl(): string
    {
        return route('student.assignments.submissions.files.download', $this->id);
    }

    /**
     * Get the preview URL for the file (if applicable).
     */
    public function getPreviewUrl(): string
    {
        if ($this->getFileType() === 'image') {
            return route('student.assignments.submissions.files.preview', $this->id);
        }
        
        return null;
    }

    /**
     * Check if the file can be previewed.
     */
    public function canBePreviewed(): bool
    {
        return in_array($this->getFileType(), ['image']);
    }

    /**
     * Get the MIME type category.
     */
    public function getMimeTypeCategory(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) {
            return 'image';
        } elseif (str_starts_with($this->mime_type, 'video/')) {
            return 'video';
        } elseif (str_starts_with($this->mime_type, 'audio/')) {
            return 'audio';
        } elseif (str_contains($this->mime_type, 'pdf')) {
            return 'document';
        } elseif (str_contains($this->mime_type, 'word') || str_contains($this->mime_type, 'document')) {
            return 'document';
        } elseif (str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'spreadsheet')) {
            return 'spreadsheet';
        } elseif (str_contains($this->mime_type, 'powerpoint') || str_contains($this->mime_type, 'presentation')) {
            return 'presentation';
        } else {
            return 'file';
        }
    }

    /**
     * Check if the file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if the file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the file is a document.
     */
    public function isDocument(): bool
    {
        return in_array($this->getMimeTypeCategory(), ['document']);
    }

    /**
     * Get a human-readable file type description.
     */
    public function getFileTypeDescription(): string
    {
        return match($this->getFileType()) {
            'document' => 'Document',
            'spreadsheet' => 'Spreadsheet',
            'presentation' => 'Presentation',
            'image' => 'Image',
            'video' => 'Video',
            'audio' => 'Audio',
            'archive' => 'Archive',
            default => 'File'
        };
    }
}
