<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'logo' => null,
            'settings' => json_encode([
                'timezone' => 'Asia/Kuala_Lumpur',
                'currency' => 'MYR',
                'date_format' => 'd/m/Y',
            ]),
            'is_active' => true,
        ];
    }
}