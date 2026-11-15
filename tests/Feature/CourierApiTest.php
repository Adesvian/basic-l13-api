<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Courier;
use App\Models\Level;
use Database\Seeders\LevelSeeder;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(LevelSeeder::class);
    }

    private function createCourier(array $attributes = []): Courier
    {
        return Courier::create(array_merge([
            'first_name' => 'Don Pablo',
            'last_name' => 'Escobar',
            'phone' => '081234567890',
            'email' => 'DonPablo@example.com',
            'address' => 'Medellin, Colombia',
            'level_id' => 1,
            'is_active' => true,
        ], $attributes));
    }

    public function test_can_get_courier_list(): void
    {
        $this->createCourier();

        $response = $this->getJson('/api/couriers');

        $response->assertOk()
            ->assertJsonFragment([
                'first_name' => 'Don Pablo',
                'last_name' => 'Escobar',
            ]);
    }

    public function test_can_search_courier_by_multiple_keywords(): void
    {
        $this->createCourier([
            'first_name' => 'Don Pablo',
            'last_name' => 'Gaviria',
        ]);

        $response = $this->getJson('/api/couriers?search=don%20pablo');

        $response->assertOk()
            ->assertJsonFragment([
                'first_name' => 'Don Pablo',
                'last_name' => 'Gaviria',
            ]);
    }

    public function test_can_filter_couriers_by_level(): void
    {
        $this->createCourier([
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'level_id' => 1,
        ]);

        $courierLevelTwo = $this->createCourier([
            'first_name' => 'Andy',
            'last_name' => 'Smith',
            'level_id' => 2,
            'email' => 'andysss@example.com',
        ]);

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $courierLevelTwo->id,
                'first_name' => 'Andy',
            ])
            ->assertJsonMissing([
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
            ]);
    }

    public function test_can_paginate_courier_list(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->createCourier([
                'first_name' => "Courier {$i}",
                'email' => "courier{$i}@example.com",
            ]);
        }

        $response = $this->getJson('/api/couriers?per_page=5');

        $response->assertOk()
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonPath('data.total', 12)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_can_get_courier_detail(): void
    {
        $courier = $this->createCourier();

        $response = $this->getJson("/api/couriers/{$courier->id}");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $courier->id,
                'first_name' => 'Don Pablo',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'level',
                ],
            ]);
    }

    public function test_can_create_courier(): void
    {
        $payload = [
            'first_name' => 'Geralt',
            'last_name' => 'Freeman',
            'phone' => '081234567891',
            'email' => 'geralt@example.com',
            'address' => 'Sidoarjo',
            'level_id' => 2,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/couriers', $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'first_name' => 'Geralt',
                'last_name' => 'Freeman',
            ]);

        $this->assertDatabaseHas('couriers', [
            'email' => 'geralt@example.com',
            'level_id' => 2,
        ]);
    }

    public function test_cannot_create_courier_with_invalid_data(): void
    {
        $response = $this->postJson('/api/couriers', [
            'first_name' => '',
            'email' => 'invalid-email',
            'level_id' => 999,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'level_id',
                'is_active',
            ]);
    }

    public function test_can_update_courier(): void
    {
        $courier = $this->createCourier();

        $payload = [
            'first_name' => 'Don Pablo Updated',
            'last_name' => 'Gaviria Updated',
            'phone' => '081111111111',
            'email' => 'budi.updated@example.com',
            'address' => 'Malang',
            'level_id' => 3,
            'is_active' => false,
        ];

        $response = $this->putJson(
            "/api/couriers/{$courier->id}",
            $payload
        );

        $response->assertOk()
            ->assertJsonFragment([
                'first_name' => 'Don Pablo Updated',
                'last_name' => 'Gaviria Updated',
            ]);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'email' => 'budi.updated@example.com',
            'level_id' => 3,
            'is_active' => 0,
        ]);
    }

    public function test_can_delete_courier(): void
    {
        $courier = $this->createCourier();

        $response = $this->deleteJson(
            "/api/couriers/{$courier->id}"
        );

        $response->assertOk();

        $this->assertSoftDeleted('couriers', [
            'id' => $courier->id,
        ]);
    }
}
