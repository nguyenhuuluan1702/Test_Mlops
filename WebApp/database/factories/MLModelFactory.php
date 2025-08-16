<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MLModel>
 */
class MLModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $libTypes = ['keras', 'pytorch', 'sklearn', 'xgboost', 'pickle', 'joblib'];
        $extensions = [
            'keras' => 'h5',
            'pytorch' => 'pt', 
            'sklearn' => 'pkl',
            'xgboost' => 'xgb',
            'pickle' => 'pkl',
            'joblib' => 'joblib'
        ];
        
        $libType = fake()->randomElement($libTypes);
        $extension = $extensions[$libType];
        
        return [
            'MLMName' => 'Test ' . ucfirst($libType) . ' Model ' . fake()->randomNumber(3),
            'FilePath' => 'models/test_model_' . fake()->unique()->randomNumber(5) . '.' . $extension,
            'LibType' => $libType,
            'IsActive' => fake()->boolean(70), // 70% chance of being active
        ];
    }

    /**
     * Create an active model.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'IsActive' => true,
        ]);
    }

    /**
     * Create an inactive model.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'IsActive' => false,
        ]);
    }

    /**
     * Create a model with specific library type.
     */
    public function libType(string $libType): static
    {
        $extensions = [
            'keras' => 'h5',
            'pytorch' => 'pt', 
            'sklearn' => 'pkl',
            'xgboost' => 'xgb',
            'pickle' => 'pkl',
            'joblib' => 'joblib'
        ];
        
        $extension = $extensions[$libType] ?? 'pkl';
        
        return $this->state(fn (array $attributes) => [
            'LibType' => $libType,
            'FilePath' => 'models/test_' . $libType . '_model_' . fake()->randomNumber(5) . '.' . $extension,
        ]);
    }

    /**
     * Create a default model.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'MLMName' => 'Default ANN Model',
            'FilePath' => 'models/ann_model.keras',
            'LibType' => 'keras',
            'IsActive' => true,
        ]);
    }
}
