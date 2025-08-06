<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ml_model_id',
        'MXene',
        'Peptide',
        'Stimulation',
        'Voltage',
        'Result',
        'PredictionDateTime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mlModel()
    {
        return $this->belongsTo(MLModel::class, 'ml_model_id');
    }
}
