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
                'FilePath' => 'models/ann_model.keras',
                'LibType' => 'keras',
                'IsActive' => true,
            ]);
        }

        // You can add more models here as needed
        // MLModel::create([
        //     'MLMName' => 'XGBoost Model',
        //     'FilePath' => 'models/xgb_model.json',
        //     'LibType' => 'xgboost',
        //     'IsActive' => true,
        // ]);
        
        // MLModel::create([
        //     'MLMName' => 'PyTorch Model',
        //     'FilePath' => 'models/pytorch_model.pt',
        //     'LibType' => 'pytorch',
        //     'IsActive' => true,
        // ]);
        
        // MLModel::create([
        //     'MLMName' => 'Sklearn Model',
        //     'FilePath' => 'models/sklearn_model.pkl',
        //     'LibType' => 'sklearn',
        //     'IsActive' => true,
        // ]);
    }
}
