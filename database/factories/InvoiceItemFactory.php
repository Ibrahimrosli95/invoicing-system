<?php

namespace Database\Factories;

use App\Models\InvoiceItem;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $totalPrice = $quantity * $unitPrice;

        return [
            'invoice_id' => Invoice::factory(),
            'description' => $this->faker->sentence(3),
            'item_code' => $this->faker->optional()->regexify('[A-Z]{3}-[0-9]{4}'),
            'specifications' => $this->faker->optional()->sentence(),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'meter', 'liter', 'hour', 'day']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'is_locked' => false,
        ];
    }

    public function locked(): static
    {
        return $this->state(['is_locked' => true]);
    }
}