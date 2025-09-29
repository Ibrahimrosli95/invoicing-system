<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxRate = $this->faker->randomFloat(2, 0, 15);
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        return [
            'company_id' => Company::factory(),
            'created_by' => User::factory(),
            'number' => 'INV-' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'title' => 'Invoice for ' . $this->faker->company(),
            'customer_name' => $this->faker->name(),
            'customer_company' => $this->faker->optional()->company(),
            'customer_email' => $this->faker->email(),
            'customer_phone' => $this->faker->phoneNumber(),
            'customer_address' => $this->faker->streetAddress(),
            'customer_city' => $this->faker->city(),
            'customer_state' => $this->faker->state(),
            'customer_postal_code' => $this->faker->postcode(),
            'status' => $this->faker->randomElement(['DRAFT', 'SENT', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED']),
            'issued_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+60 days'),
            'subtotal' => $subtotal,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => $taxRate,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'amount_paid' => 0,
            'amount_due' => $total,
            'payment_terms_days' => $this->faker->randomElement([15, 30, 45, 60]),
            'notes' => $this->faker->optional()->paragraph(),
            'terms_conditions' => $this->faker->optional()->paragraph(),
            'payment_instructions' => $this->faker->optional()->paragraph(),
        ];
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'PAID',
                'amount_paid' => $attributes['total'],
                'amount_due' => 0,
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'OVERDUE',
                'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            ];
        });
    }
}