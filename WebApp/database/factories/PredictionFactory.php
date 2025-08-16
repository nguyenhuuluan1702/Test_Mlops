<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\MLModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prediction>
 */
class PredictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ml_model_id' => MLModel::factory(),
            'MXene' => fake()->randomFloat(3, 0, 0.3), // 0 to 0.3 with 3 decimal places
            'Peptide' => fake()->randomFloat(1, 0, 150), // 0 to 150 with 1 decimal place
            'Stimulation' => fake()->randomFloat(1, 0, 3), // 0 to 3 with 1 decimal place
            'Voltage' => fake()->randomFloat(1, 0, 3), // 0 to 3 with 1 decimal place
            'Result' => fake()->randomFloat(2, 40, 100), // Viability score 40-100% with 2 decimal places
            'PredictionDateTime' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Create a prediction for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a prediction with a specific model.
     */
    public function withModel(MLModel $model): static
    {
        return $this->state(fn (array $attributes) => [
            'ml_model_id' => $model->id,
        ]);
    }

    /**
     * Create a recent prediction (within last 30 days).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'PredictionDateTime' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create an old prediction (older than 30 days).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'PredictionDateTime' => fake()->dateTimeBetween('-1 year', '-31 days'),
        ]);
    }

    /**
     * Create a prediction with high viability result.
     */
    public function highViability(): static
    {
        return $this->state(fn (array $attributes) => [
            'Result' => fake()->randomFloat(2, 80, 100),
        ]);
    }

    /**
     * Create a prediction with low viability result.
     */
    public function lowViability(): static
    {
        return $this->state(fn (array $attributes) => [
            'Result' => fake()->randomFloat(2, 40, 60),
        ]);
    }

    /**
     * Create a prediction with specific parameters.
     */
    public function withParameters(float $mxene, float $peptide, float $stimulation, float $voltage): static
    {
        return $this->state(fn (array $attributes) => [
            'MXene' => $mxene,
            'Peptide' => $peptide,
            'Stimulation' => $stimulation,
            'Voltage' => $voltage,
        ]);
    }

    /**
     * Create a prediction with specific result.
     */
    public function withResult(float $result): static
    {
        return $this->state(fn (array $attributes) => [
            'Result' => $result,
        ]);
    }

    /**
     * Create a prediction from today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'PredictionDateTime' => fake()->dateTimeBetween('today', 'now'),
        ]);
    }

    /**
     * Create a prediction with extreme parameter values.
     */
    public function extremeParameters(): static
    {
        return $this->state(fn (array $attributes) => [
            'MXene' => fake()->randomElement([0.0, 0.3]), // Min or max
            'Peptide' => fake()->randomElement([0.0, 150.0]), // Min or max
            'Stimulation' => fake()->randomElement([0.0, 3.0]), // Min or max
            'Voltage' => fake()->randomElement([0.0, 3.0]), // Min or max
        ]);
    }
}
