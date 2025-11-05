<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\Turbine;
use \Database\Seeders\TurbineTestSeeder;

class APIAuthTest extends TestCase
{
    use RefreshDatabase;


    public function test_get_all_turbines()
    {
        $response = $this->getJson("/api/turbines");
        $response->assertStatus(200);
    }
        


}
