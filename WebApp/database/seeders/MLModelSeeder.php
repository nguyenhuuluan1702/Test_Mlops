<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MLModel;

class MLModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create default model if it doesn't exist
        if (MLModel::count() == 0) {
            MLModel::create([
                'MLMName' => 'Default ANN Model',
                'FilePath' => 'models/default_ann_model.keras',
                'LibType' => 'keras',
                'IsActive' => true,
                'MSEValue' => 0.0281,
                'MAEValue' => 0.1144,
            ]);

            MLModel::create([
                'MLMName' => 'Customize ANN Model',
                'FilePath' => 'models/custom_ann_model.keras',
                'LibType' => 'keras',
                'IsActive' => true,
                'MSEValue' => 0.02,
                'MAEValue' => 0.0896,
            ]);

            MLModel::create([
                'MLMName' => 'Preview Linear Regression Model',
                'FilePath' => 'models/lr_augmented_model.pkl',
                'LibType' => 'sklearn',
                'IsActive' => true,
                'MSEValue' => 0.0265,
                'MAEValue' => 0.131,
            ]);
            
            MLModel::create([
                'MLMName' => 'Preview Random Forest Model',
                'FilePath' => 'models/rf_augmented_model.pkl',
                'LibType' => 'sklearn',
                'IsActive' => true,
                'MSEValue' => 0.0093,
                'MAEValue' => 0.0709,
            ]);

            MLModel::create([
                'MLMName' => 'Preview XGBoost Model',
                'FilePath' => 'models/xgb_augmented_model.json',
                'LibType' => 'xgboost',
                'IsActive' => true,
                'MSEValue' => 0.0095,
                'MAEValue' => 0.0725,
            ]);
        }

        
    }
}
