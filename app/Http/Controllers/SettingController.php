<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display the system settings page.
     */
    public function index(Request $request): View
    {
        $category = $request->get('category', 'general');
        $categories = Setting::getCategories();
        
        // Get settings by category
        $settings = Setting::category($category)
                          ->orderBy('title', 'asc')
                          ->get();
        
        // Group settings by type for better organization
        $groupedSettings = $settings->groupBy('type');
        
        return view('settings.index', compact(
            'settings',
            'groupedSettings',
            'categories',
            'category'
        ));
    }
    
    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        // Check if user can update settings
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update system settings'
            ], 403);
        }
        
        $settings = $request->get('settings', []);
        $category = $request->get('category', 'general');
        
        if (empty($settings)) {
            return response()->json([
                'success' => false,
                'message' => 'No settings provided for update'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $results = [];
            $updatedCount = 0;
            $errorCount = 0;
            
            foreach ($settings as $key => $value) {
                try {
                    // Get the existing setting to validate
                    $setting = Setting::where('key', $key)->first();
                    
                    if (!$setting) {
                        $results[$key] = [
                            'success' => false,
                            'message' => 'Setting not found'
                        ];
                        $errorCount++;
                        continue;
                    }
                    
                    // Validate the value based on setting type
                    $validationRules = $this->getValidationRules($setting);
                    $validator = Validator::make(
                        ['value' => $value],
                        ['value' => $validationRules]
                    );
                    
                    if ($validator->fails()) {
                        $results[$key] = [
                            'success' => false,
                            'message' => $validator->errors()->first('value')
                        ];
                        $errorCount++;
                        continue;
                    }
                    
                    // Update the setting
                    $setting->setTypedValue($value);
                    $setting->updated_by = Auth::id();
                    $setting->save();
                    
                    // Clear cache
                    Cache::forget("setting_{$key}");
                    
                    $results[$key] = [
                        'success' => true,
                        'message' => 'Updated successfully'
                    ];
                    $updatedCount++;
                    
                } catch (\Exception $e) {
                    $results[$key] = [
                        'success' => false,
                        'message' => 'Failed to update: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Settings updated successfully. {$updatedCount} updated, {$errorCount} errors.",
                'results' => $results,
                'updated_count' => $updatedCount,
                'error_count' => $errorCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update a single setting.
     */
    public function updateSingle(Request $request, Setting $setting): JsonResponse
    {
        // Check if user can update settings
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update system settings'
            ], 403);
        }
        
        $request->validate([
            'value' => 'required'
        ]);
        
        try {
            // Validate the value based on setting type
            $validationRules = $this->getValidationRules($setting);
            $validator = Validator::make(
                ['value' => $request->value],
                ['value' => $validationRules]
            );
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first('value')
                ], 422);
            }
            
            // Update the setting
            $setting->setTypedValue($request->value);
            $setting->updated_by = Auth::id();
            $setting->save();
            
            // Clear cache
            Cache::forget("setting_{$setting->key}");
            
            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully',
                'setting' => $setting->load('updatedBy')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reset settings to defaults.
     */
    public function resetDefaults(Request $request): JsonResponse
    {
        // Check if user can reset settings
        if (!Auth::user()->hasRole(['super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reset system settings'
            ], 403);
        }
        
        $category = $request->get('category', 'all');
        
        try {
            DB::beginTransaction();
            
            $defaults = Setting::getDefaults();
            $resetCount = 0;
            
            foreach ($defaults as $key => $value) {
                // Skip if category is specified and doesn't match
                if ($category !== 'all') {
                    $setting = Setting::where('key', $key)->first();
                    if ($setting && $setting->category !== $category) {
                        continue;
                    }
                }
                
                Setting::setValue($key, $value);
                $resetCount++;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Settings reset to defaults successfully. {$resetCount} settings reset.",
                'reset_count' => $resetCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export settings.
     */
    public function export(Request $request): JsonResponse
    {
        // Check if user can export settings
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to export system settings'
            ], 403);
        }
        
        $category = $request->get('category', 'all');
        $format = $request->get('format', 'json');
        
        try {
            $settings = $category === 'all' 
                ? Setting::all() 
                : Setting::category($category)->get();
            
            $exportData = $settings->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => $setting->getTypedValue(),
                    'type' => $setting->type,
                    'category' => $setting->category,
                    'title' => $setting->title,
                    'description' => $setting->description,
                    'is_public' => $setting->is_public,
                    'updated_at' => $setting->updated_at->format('Y-m-d H:i:s'),
                ];
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Settings exported successfully',
                'data' => $exportData,
                'count' => $exportData->count(),
                'category' => $category,
                'format' => $format
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Import settings.
     */
    public function import(Request $request): JsonResponse
    {
        // Check if user can import settings
        if (!Auth::user()->hasRole(['super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to import system settings'
            ], 403);
        }
        
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'overwrite' => 'boolean'
        ]);
        
        $settingsData = $request->get('settings', []);
        $overwrite = $request->get('overwrite', false);
        
        try {
            DB::beginTransaction();
            
            $results = [];
            $importCount = 0;
            $errorCount = 0;
            
            foreach ($settingsData as $settingData) {
                try {
                    $key = $settingData['key'];
                    $value = $settingData['value'];
                    
                    // Check if setting exists
                    $existing = Setting::where('key', $key)->first();
                    
                    if ($existing && !$overwrite) {
                        $results[$key] = [
                            'success' => false,
                            'message' => 'Setting already exists (use overwrite option)'
                        ];
                        $errorCount++;
                        continue;
                    }
                    
                    // Create or update setting
                    $title = $settingData['title'] ?? ucwords(str_replace('_', ' ', $key));
                    $description = $settingData['description'] ?? null;
                    $category = $settingData['category'] ?? 'general';
                    $isPublic = $settingData['is_public'] ?? false;
                    
                    $setting = Setting::setValue($key, $value, $title, $description, $category, $isPublic);
                    $setting->updated_by = Auth::id();
                    $setting->save();
                    
                    // Clear cache
                    Cache::forget("setting_{$key}");
                    
                    $results[$key] = [
                        'success' => true,
                        'message' => 'Imported successfully'
                    ];
                    $importCount++;
                    
                } catch (\Exception $e) {
                    $results[$key] = [
                        'success' => false,
                        'message' => 'Failed to import: ' . $e->getMessage()
                    ];
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Settings imported successfully. {$importCount} imported, {$errorCount} errors.",
                'results' => $results,
                'import_count' => $importCount,
                'error_count' => $errorCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear settings cache.
     */
    public function clearCache(): JsonResponse
    {
        // Check if user can clear cache
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to clear settings cache'
            ], 403);
        }
        
        try {
            Setting::clearCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Settings cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear settings cache: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get setting details.
     */
    public function getSetting(Setting $setting): JsonResponse
    {
        // Check if user can view setting
        if (!$setting->is_public && !Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this setting'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'setting' => $setting->load('updatedBy')
        ]);
    }
    
    /**
     * Create a new setting.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user can create settings
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to create system settings'
            ], 403);
        }
        
        $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
            'title' => 'required|string',
            'type' => ['required', Rule::in(['text', 'number', 'boolean', 'json', 'array'])],
            'category' => 'required|string',
            'description' => 'nullable|string',
            'is_public' => 'boolean'
        ]);
        
        try {
            $setting = Setting::setValue(
                $request->key,
                $request->value,
                $request->title,
                $request->description,
                $request->category,
                $request->is_public
            );
            
            $setting->updated_by = Auth::id();
            $setting->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Setting created successfully',
                'setting' => $setting->load('updatedBy')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create setting: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a setting.
     */
    public function destroy(Setting $setting): JsonResponse
    {
        // Check if user can delete settings
        if (!Auth::user()->hasRole(['super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete system settings'
            ], 403);
        }
        
        try {
            $key = $setting->key;
            $setting->delete();
            
            // Clear cache
            Cache::forget("setting_{$key}");
            
            return response()->json([
                'success' => true,
                'message' => 'Setting deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete setting: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get validation rules for a setting.
     */
    private function getValidationRules(Setting $setting): array
    {
        return match($setting->type) {
            'boolean' => ['boolean'],
            'number' => ['numeric'],
            'email' => ['email'],
            'url' => ['url'],
            'json' => ['json'],
            'array' => ['array'],
            default => ['string'],
        };
    }
    
    /**
     * Get settings statistics.
     */
    public function statistics(): JsonResponse
    {
        // Check if user can view statistics
        if (!Auth::user()->hasRole(['super_admin', 'admin', 'principal'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view settings statistics'
            ], 403);
        }
        
        try {
            $stats = [
                'total_settings' => Setting::count(),
                'public_settings' => Setting::public()->count(),
                'private_settings' => Setting::private()->count(),
                'categories' => Setting::getCategories(),
                'settings_by_category' => [],
                'settings_by_type' => Setting::getByType()->groupBy('type')->map->count(),
                'recently_updated' => Setting::with('updatedBy')
                                        ->orderBy('updated_at', 'desc')
                                        ->limit(10)
                                        ->get(),
            ];
            
            // Get settings count by category
            foreach ($stats['categories'] as $category) {
                $stats['settings_by_category'][$category] = Setting::category($category)->count();
            }
            
            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
