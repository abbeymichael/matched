<?php

namespace Database\Factories;

use App\Enums\FieldType;
use App\Models\FieldDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldDefinition>
 */
class FieldDefinitionFactory extends Factory
{
    protected $model = FieldDefinition::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'label' => $this->faker->words(2, true),
            'description' => null,
            'category' => 'lifestyle',
            'field_type' => FieldType::SingleSelect,
            'is_active' => true,
            'is_hard_filter' => false,
            'is_required' => false,
            'is_core' => false,
            'weight' => 1.00,
            'sort_order' => 0,
            'config' => null,
        ];
    }

    public function singleSelect(): static
    {
        return $this->state(fn () => ['field_type' => FieldType::SingleSelect]);
    }

    public function multiSelect(): static
    {
        return $this->state(fn () => ['field_type' => FieldType::MultiSelect]);
    }

    public function scale(array $config = []): static
    {
        return $this->state(fn () => [
            'field_type' => FieldType::Scale,
            'config' => $config ?: ['scale_length' => 5],
        ]);
    }

    public function range(array $config = []): static
    {
        return $this->state(fn () => [
            'field_type' => FieldType::Range,
            'config' => $config,
        ]);
    }

    public function hardFilter(): static
    {
        return $this->state(fn () => ['is_hard_filter' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
