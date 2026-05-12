<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'order'
    ];

    protected $casts = [
        'size' => 'integer',
        'order' => 'integer'
    ];

    /**
     * Get the lesson that owns the file.
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the file type category.
     */
    public function getFileTypeCategory(): string
    {
        $mime = $this->mime_type;
        
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mime, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mime, 'audio/')) {
            return 'audio';
        } elseif ($mime === 'application/pdf') {
            return 'pdf';
        } elseif (in_array($mime, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            return 'document';
        } elseif (in_array($mime, [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ])) {
            return 'presentation';
        } elseif (in_array($mime, [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])) {
            return 'spreadsheet';
        } else {
            return 'other';
        }
    }

    /**
     * Get the file icon based on mime type.
     */
    public function getFileIcon(): string
    {
        $category = $this->getFileTypeCategory();
        
        return match($category) {
            'image' => 'fas fa-image',
            'video' => 'fas fa-video',
            'audio' => 'fas fa-music',
            'pdf' => 'fas fa-file-pdf',
            'document' => 'fas fa-file-word',
            'presentation' => 'fas fa-file-powerpoint',
            'spreadsheet' => 'fas fa-file-excel',
            'other' => 'fas fa-file'
        };
    }

    /**
     * Get the file color based on mime type.
     */
    public function getFileColor(): string
    {
        $category = $this->getFileTypeCategory();
        
        return match($category) {
            'image' => 'success',
            'video' => 'danger',
            'audio' => 'info',
            'pdf' => 'danger',
            'document' => 'primary',
            'presentation' => 'warning',
            'spreadsheet' => 'success',
            'other' => 'secondary'
        };
    }

    /**
     * Get formatted file size.
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
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return $this->getFileTypeCategory() === 'image';
    }

    /**
     * Check if file is a video.
     */
    public function isVideo(): bool
    {
        return $this->getFileTypeCategory() === 'video';
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->getFileTypeCategory() === 'pdf';
    }

    /**
     * Get the download URL for the file.
     */
    public function getDownloadUrl(): string
    {
        return route('lessons.files.download', $this->id);
    }

    /**
     * Get the preview URL for the file (if applicable).
     */
    public function getPreviewUrl(): string
    {
        if ($this->isImage()) {
            return route('lessons.files.preview', $this->id);
        }
        
        return $this->getDownloadUrl();
    }
}
