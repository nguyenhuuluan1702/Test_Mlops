<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MLModel extends Model
{
    use HasFactory;

    protected $table = 'ml_models';

    protected $fillable = [
        'MLMName',
        'FilePath',
        'LibType',
        'IsActive',
        'MSEValue',
        'MAEValue',
        'MlflowRunId',
        'ZenmlPipelineId',
        'TrainedBy',
        'DatasetId',
        'CreatedDate',
        'UpdatedDate',
    ];

    protected $casts = [
        'IsActive' => 'boolean',
        'CreatedDate' => 'datetime',
        'UpdatedDate' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'ml_model_id');
    }

    public function dataset()
    {
        return $this->belongsTo(Dataset::class, 'DatasetId');
    }

    public function trainedByUser()
    {
        return $this->belongsTo(User::class, 'TrainedBy');
    }

    /**
     * Accessors & Utility Functions
     */
    public function getAbsolutePathAttribute()
    {
        return public_path($this->FilePath);
    }

    public function fileExists()
    {
        return file_exists($this->getAbsolutePathAttribute());
    }

    public function getFileSizeAttribute()
    {
        if ($this->fileExists()) {
            $sizeInBytes = filesize($this->getAbsolutePathAttribute());
            return round($sizeInBytes / 1024 / 1024, 2); // MB
        }
        return 0;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('IsActive', true);
    }

    /**
     * Static helper: get default active model
     */
    public static function getDefaultModel()
    {
        return static::active()->first();
    }
}
