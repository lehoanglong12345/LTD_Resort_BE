<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Employee;
use DateInterval;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillRoom>
 */
class BillRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $customer_model = new Customer();
        $customer_id_accounts = $customer_model->newQuery()->get('id');
        $employee_model = new Employee();
        $employee_id_accounts = $employee_model->newQuery()->get('id');
        $checkin_time = fake()->dateTimeBetween('-12 days', 'now', 'Asia/Ho_Chi_Minh');
        $checkout_time = clone $checkin_time;
        $checkout_time->add(new DateInterval('P2D'));

        $has_been_check = fake()->boolean(70);

        $startDate = \Carbon\Carbon::now()->subMonth();
        $endDate = \Carbon\Carbon::now();
        $billCode = fake()->dateTimeBetween($startDate, $endDate)->format('YmdHis');

        return [
            'total_amount' => fake()->numberBetween(440000, 1160000),
            'total_room' => fake()->numberBetween(2, 6),
            'total_people' => fake()->numberBetween(4, 8),
            'payment_method' => 'Online',
            'pay_time' => $checkin_time,
            'checkin_time' => $has_been_check ? $checkin_time : null,
            'checkout_time' => $has_been_check ? $checkout_time : null,
            'cancel_time' => null,
            'tax' => fake()->randomElement([0.1, 0.12, 0.15]),
            'discount' => 0,
            'bill_code' => $billCode,
            'customer_id' => fake()->randomElement($customer_id_accounts),
            'employee_id' => $has_been_check ? fake()->randomElement($employee_id_accounts) : null,
        ];
    }
}
