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
            ]);

            MLModel::create([
                'MLMName' => 'Customize ANN Model',
                'FilePath' => 'models/custom_ann_model.keras',
                'LibType' => 'keras',
                'IsActive' => true,
            ]);

            MLModel::create([
                'MLMName' => 'Preview Linear Regression Model',
                'FilePath' => 'models/lr_augmented_model.pkl',
                'LibType' => 'sklearn',
                'IsActive' => true,
            ]);
            
            MLModel::create([
                'MLMName' => 'Preview Random Forest Model',
                'FilePath' => 'models/rf_augmented_model.pkl',
                'LibType' => 'sklearn',
                'IsActive' => true,
            ]);

            MLModel::create([
                'MLMName' => 'Preview XGBoost Model',
                'FilePath' => 'models/xgb_augmented_model.json',
                'LibType' => 'xgboost',
                'IsActive' => true,
            ]);
        }

        
    }
}
