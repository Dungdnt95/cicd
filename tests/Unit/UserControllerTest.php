<?php

namespace Tests\Unit;

use App\Enums\StatusCode;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class UserControllerTest extends TestCase
{
    /**
     * @group examplegroup
     *
     */
    use DatabaseTransactions;
    // use WithoutMiddleware;

    private $faker;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@gmail.com')->first();
        $this->faker = Faker::create();
        Session::start();
    }

    //no permission
    public function test_store_2()
    {
        $password = Str::random(8);
        $response = $this->json(
            'post',
            "admin/user",
            [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $password,
                "password_confirmation" => $password
            ]
        );
        $response->assertStatus(302);
        $response->assertRedirectContains(route('login.index'));
    }

    //max length
    public function test_store_3()
    {
        $this->withoutMiddleware();
        $password = Str::random(16);
        $response = $this->json(
            'post',
            "admin/user",
            [
                'name' => Str::random(256),
                'email' => Str::random(246) . '@gmail.com',
                'password' => $password,
                "password_confirmation" => $password
            ]
        );

        $response->assertInvalid(['name']);
        $response->assertInvalid(['email']);
        $response->assertInvalid(['password']);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonValidationErrors('email');
        $response->assertJsonValidationErrors('password');

        $response->assertStatus(422);
    }

    //password is not same
    public function test_store_4()
    {
        $this->withoutMiddleware();
        $password = Str::random(8);
        $response = $this->json(
            'post',
            "admin/user",
            [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $password,
                "password_confirmation" => Str::random(9)
            ]
        );

        $response->assertInvalid(['password']);
        $response->assertJsonValidationErrors('password');

        $response->assertStatus(422);
    }


    //empty data
    public function test_store_7()
    {
        $this->withoutMiddleware();
        $response = $this->json(
            'post',
            "admin/user",
            [
                'name' => '',
                'email' => '',
                'password' => '',
                "password_confirmation" => ''
            ]
        );
        $response->assertInvalid(['name']);
        $response->assertInvalid(['email']);
        $response->assertInvalid(['password']);
        $response->assertInvalid(['password_confirmation']);
        $response->assertJsonValidationErrors('name');
        $response->assertJsonValidationErrors('email');
        $response->assertJsonValidationErrors('password');
        $response->assertJsonValidationErrors('password_confirmation');
        $response->assertStatus(422);
    }


    //no permission
    public function test_update_2()
    {
        $password = Str::random(8);
        $user = User::factory()->create();
        $response = $this->put(
            route('admin.user.update', $user->id),
            [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $password,
                "password_confirmation" => $password
            ]
        );
        $response->assertStatus(302);
        $response->assertRedirectContains(route('login.index'));
    }

    //max length
    public function test_update_3()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $password = Str::random(16);
        $response = $this->json(
            'put',
            route('admin.user.update', $user->id),
            [
                'name' => Str::random(256),
                'email' => Str::random(246) . '@gmail.com',
                'password' => $password,
                "password_confirmation" => $password
            ]
        );

        $response->assertInvalid(['name']);
        $response->assertInvalid(['email']);
        $response->assertInvalid(['password']);
        $response->assertJsonValidationErrors('name');
        $response->assertJsonValidationErrors('email');
        $response->assertJsonValidationErrors('password');

        $response->assertStatus(422);
    }
    //password is not same
    public function test_update_4()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $password = Str::random(8);
        $response = $this->json(
            'put',
            route('admin.user.update', $user->id),
            [
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $password,
                "password_confirmation" => Str::random(9)
            ]
        );
        $response->assertInvalid(['password']);
        $response->assertJsonValidationErrors('password');

        $response->assertStatus(422);
    }

    //empty data
    public function test_update_7()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $response = $this->json(
            'put',
            route('admin.user.update', $user->id),
            [
                'name' => '',
                'email' => '',
                'password' => '',
                "password_confirmation" => ''
            ]
        );
        $response->assertInvalid(['name']);
        $response->assertInvalid(['email']);
        $response->assertJsonValidationErrors('name');
        $response->assertJsonValidationErrors('email');

        $response->assertStatus(422);
    }
    //user not found
    public function test_update_8()
    {
        $this->withoutMiddleware();
        $response = $this->json(
            'put',
            route('admin.user.update', -1),
            [
                'name' => '',
                'email' => '',
                'password' => '',
                "password_confirmation" => ''
            ]
        );

        $response->assertStatus(422);
    }
    //has permission
    public function test_destroy_1()
    {
        $this->withoutMiddleware();
        $user = User::factory()->create();
        $response = $this->json(
            'delete',
            route('admin.user.destroy', $user->id),
        );

        $response->assertStatus(StatusCode::OK);
    }
    //no permission
    public function test_destroy_2()
    {
        $user = User::factory()->create();
        $response = $this->json(
            'delete',
            route('admin.user.destroy', $user->id),
        );

        $response->assertStatus(302);
        $response->assertRedirectContains(route('login.index'));
    }
    //user not found
    public function test_destroy_3()
    {
        $this->withoutMiddleware();
        $response = $this->json(
            'delete',
            route('admin.user.destroy', -1),
        );

        $response->assertStatus(StatusCode::INTERNAL_ERR);
    }


    public function providerTestStore()
    {
        $this->faker = Faker::create();
        $password = Str::random(8);
        return [
            [[
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => $password,
                "password_confirmation" => $password
            ], 200],
            [[
                'name' => Str::random(256),
                'email' => Str::random(246) . '@gmail.com',
                'password' => '1234567890123456',
                "password_confirmation" => '1234567890123456'
            ], 422],
            [[
                'name' => $this->faker->name,
                'email' => $this->faker->email,
                'password' => '12345678',
                "password_confirmation" => '123456789'
            ], 422],
            [[
                'name' => $this->faker->name,
                'email' => Str::random(16),
                'password' => $password,
                "password_confirmation" => $password
            ], 422],
            [[
                'name' => '',
                'email' => '',
                'password' => '',
                "password_confirmation" => ''
            ], 422],
        ];
    }
}
