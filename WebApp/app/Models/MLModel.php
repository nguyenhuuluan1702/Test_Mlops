<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MLModel extends Model
{
    use HasFactory;

    protected $table = 'ml_models';

    protected $fillable = ['MLMName', 'FilePath', 'LibType', 'IsActive', 'MSEValue', 'MAEValue'];

    protected $casts = [
        'IsActive' => 'boolean',
    ];

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'ml_model_id');
    }

    /**
     * Get the absolute file path for the model
     */
    public function getAbsolutePathAttribute()
    {
        return public_path($this->FilePath);
    }

    /**
     * Check if model file exists
     */
    public function fileExists()
    {
        return file_exists($this->getAbsolutePathAttribute());
    }

    /**
     * Get file size in MB
     */
    public function getFileSizeAttribute()
    {
        if ($this->fileExists()) {
            $sizeInBytes = filesize($this->getAbsolutePathAttribute());
            return round($sizeInBytes / 1024 / 1024, 2); // Convert to MB
        }
        return 0;
    }

    /**
     * Scope for active models
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    /**
     * Get default active model
     */
    public static function getDefaultModel()
    {
        return static::active()->first();
    }
}
