<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Turbine;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use \Database\Seeders\TurbineTestSeeder;

class APITurbineTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_get_single_turbine() {
        $this->seed(TurbineTestSeeder::class);

        $turbine = Turbine::first();

        $response = $this->getJson('/api/turbines/' . $turbine->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $turbine->id,
                     'turbine_id' => $turbine->turbine_id,
                 ]);
    }
}
