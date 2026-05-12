<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'title',
        'description',
        'is_public',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'string',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who updated the setting.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include settings of a given category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include private settings.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Get the typed value of the setting.
     */
    public function getTypedValue()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($this->value) ? (float) $this->value : 0,
            'json' => json_decode($this->value, true) ?? [],
            'array' => json_decode($this->value, true) ?? [],
            default => $this->value,
        };
    }

    /**
     * Set the typed value of the setting.
     */
    public function setTypedValue($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->value = json_encode($value);
            $this->type = 'json';
        } elseif (is_bool($value)) {
            $this->value = $value ? '1' : '0';
            $this->type = 'boolean';
        } elseif (is_numeric($value)) {
            $this->value = (string) $value;
            $this->type = 'number';
        } else {
            $this->value = (string) $value;
            $this->type = 'text';
        }
    }

    /**
     * Get a setting value by key with caching.
     */
    public static function getValue(string $key, $default = null)
    {
        $cacheKey = "setting_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            return $setting->getTypedValue();
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, ?string $title = null, ?string $description = null, string $category = 'general', bool $isPublic = false)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            $setting = new static(['key' => $key]);
        }
        
        $setting->setTypedValue($value);
        $setting->title = $title ?: ucwords(str_replace('_', ' ', $key));
        $setting->description = $description;
        $setting->category = $category;
        $setting->is_public = $isPublic;
        $setting->save();
        
        // Clear cache
        Cache::forget("setting_{$key}");
        
        return $setting;
    }

    /**
     * Get all settings by category.
     */
    public static function getByCategory(string $category)
    {
        return static::category($category)->get()->pluck('typed_value', 'key');
    }

    /**
     * Get all public settings.
     */
    public static function getPublicSettings()
    {
        return static::public()->get()->pluck('typed_value', 'key');
    }

    /**
     * Get all settings as key-value pairs.
     */
    public static function getAllSettings()
    {
        return static::all()->pluck('typed_value', 'key');
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete a setting by key.
     */
    public static function remove(string $key): bool
    {
        $deleted = static::where('key', $key)->delete();
        
        if ($deleted) {
            Cache::forget("setting_{$key}");
        }
        
        return $deleted;
    }

    /**
     * Get all categories.
     */
    public static function getCategories(): array
    {
        return static::distinct('category')
                    ->pluck('category')
                    ->sort()
                    ->toArray();
    }

    /**
     * Get settings by type.
     */
    public static function getByType(string $type)
    {
        return static::where('type', $type)->get();
    }

    /**
     * Bulk update settings.
     */
    public static function bulkUpdate(array $settings, ?int $updatedBy = null): array
    {
        $results = [];
        
        foreach ($settings as $key => $value) {
            try {
                $setting = static::setValue($key, $value);
                if ($updatedBy) {
                    $setting->updated_by = $updatedBy;
                    $setting->save();
                }
                $results[$key] = ['success' => true, 'message' => 'Updated successfully'];
            } catch (\Exception $e) {
                $results[$key] = ['success' => false, 'message' => $e->getMessage()];
            }
        }
        
        return $results;
    }

    /**
     * Get default system settings.
     */
    public static function getDefaults(): array
    {
        return [
            // General Settings
            'school_name' => 'EduNexus School',
            'school_address' => '123 Education Street',
            'school_phone' => '+1-234-567-8900',
            'school_email' => 'info@edunexus.com',
            'school_website' => 'https://edunexus.com',
            
            // Academic Settings
            'academic_year' => '2024-2025',
            'semester_start' => '2024-09-01',
            'semester_end' => '2025-06-30',
            'max_students_per_class' => 30,
            'attendance_threshold' => 75,
            
            // Library Settings
            'library_loan_period' => 14,
            'library_max_books' => 3,
            'library_fine_per_day' => 10,
            'library_notification_days' => 3,
            
            // HR Settings
            'working_hours' => '9:00 AM - 5:00 PM',
            'working_days' => 'Monday-Friday',
            'leave_approval_required' => true,
            'payroll_processing_day' => 25,
            
            // System Settings
            'session_timeout' => 30,
            'max_file_size' => 2048,
            'allowed_file_types' => 'jpg,jpeg,png,pdf,doc,docx',
            'maintenance_mode' => false,
            
            // Email Settings
            'mail_driver' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@edunexus.com',
            'mail_from_name' => 'EduNexus',
            
            // Security Settings
            'password_min_length' => 8,
            'password_require_special' => true,
            'password_require_number' => true,
            'session_timeout_warning' => 5,
            'max_login_attempts' => 5,
        ];
    }

    /**
     * Seed default settings.
     */
    public static function seedDefaults()
    {
        $defaults = static::getDefaults();
        
        foreach ($defaults as $key => $value) {
            if (!static::has($key)) {
                $category = 'general';
                
                // Determine category based on key
                if (str_contains($key, 'academic') || str_contains($key, 'students') || str_contains($key, 'attendance')) {
                    $category = 'academic';
                } elseif (str_contains($key, 'library')) {
                    $category = 'library';
                } elseif (str_contains($key, 'hr') || str_contains($key, 'payroll') || str_contains($key, 'leave') || str_contains($key, 'working')) {
                    $category = 'hr';
                } elseif (str_contains($key, 'mail') || str_contains($key, 'email')) {
                    $category = 'email';
                } elseif (str_contains($key, 'password') || str_contains($key, 'session') || str_contains($key, 'login') || str_contains($key, 'security')) {
                    $category = 'security';
                } elseif (str_contains($key, 'file') || str_contains($key, 'maintenance')) {
                    $category = 'system';
                } elseif (str_contains($key, 'school')) {
                    $category = 'school';
                }
                
                static::setValue(
                    $key,
                    $value,
                    ucwords(str_replace('_', ' ', $key)),
                    null,
                    $category,
                    in_array($category, ['general', 'school'])
                );
            }
        }
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache(): void
    {
        $keys = static::all()->pluck('key');
        
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
    }

    /**
     * Get formatted value for display.
     */
    public function getFormattedValue(): string
    {
        $value = $this->getTypedValue();
        
        return match($this->type) {
            'boolean' => $value ? 'Enabled' : 'Disabled',
            'json', 'array' => json_encode($value, JSON_PRETTY_PRINT),
            'number' => number_format($value, 2),
            default => (string) $value,
        };
    }

    /**
     * Get input type for form field.
     */
    public function getInputType(): string
    {
        return match($this->type) {
            'boolean' => 'checkbox',
            'number' => 'number',
            'json', 'array' => 'textarea',
            'text' => str_contains($this->key, 'password') ? 'password' : 'text',
            default => 'text',
        };
    }

    /**
     * Get validation rules for the setting.
     */
    public function getValidationRules(): array
    {
        return match($this->type) {
            'boolean' => ['boolean'],
            'number' => ['numeric'],
            'email' => ['email'],
            'url' => ['url'],
            'json' => ['json'],
            default => ['string'],
        };
    }
}
