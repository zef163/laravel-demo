<?php

namespace Tests\Feature;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test API method: Display a listing of the resource.
     */
    public function testIndexSuccess()
    {
        User::factory(15)->create();

        $this->getJson('api/users')
            ->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ]
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => [
                        'url',
                        'label',
                        'active',
                    ]
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
    }

    /**
     * Test API method: Store a newly created resource in storage.
     * Success action (Should create a user)
     */
    public function testStoreSuccess()
    {
        // POST data
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Str::random(6),
        ];

        // Create a user
        $nextId = $this->postJson('api/users', $data)
            ->assertStatus(200)
            ->assertJsonStructure(['id'])
            ->json('id');

        // Get created user
        $user = User::find($nextId);

        // Check database
        $this->assertDatabaseHas(app(User::class)->getTable(), $user->only(['name', 'email', 'password']));

        // Check password
        $this->assertTrue(Hash::check($data['password'], $user->password));
    }

    /**
     * Test API method: Store a newly created resource in storage.
     * Failed action
     *
     * @dataProvider storeFailedProvider
     * @param array $data POST data.
     * @param array $errors Available validation errors.
     */
    public function testStoreFailed(array $data, array $errors)
    {
        $this->postJson('api/users', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Data provider for the "testStoreFailed" test
     *
     * @return array
     */
    public function storeFailedProvider()
    {
        return [
            'Empty POST data' => [
                [],
                ['name', 'email', 'password'],
            ],

            'Incorrect all POST data' => [
                ['name' => '', 'email' => 'foo', 'password' => '1'],
                ['name', 'email', 'password'],
            ],

            'Incorrect password' => [
                ['name' => 'John Doe', 'email' => 'email@example.com', 'password' => '1'],
                ['password'],
            ],
        ];
    }

    /**
     * Test API method: Display the specified resource.
     * Success action (Should return user data)
     */
    public function testShowSuccess()
    {
        // Create test user
        $user = User::factory()->create();

        $this->getJson('api/users/' . $user->id)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->assertJson($user->toArray());
    }

    /**
     * Test API method: Display the specified resource.
     * Failed action
     */
    public function testShowFailed()
    {
        // User not found
        $this->getJson('api/users/0')
            ->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\User] 0']);
    }

    /**
     * Test API method: Update the specified resource in storage.
     * Success action
     */
    public function testUpdateSuccess()
    {
        // Create test user
        $user = User::factory()->create();

        // New user data
        $password = uniqid();
        $factoryUser = User::factory()->make(['password' => bcrypt($password)]);

        // POST data
        $data = [
            'name' => $factoryUser->name,
            'email' => $factoryUser->email,
            'password' => $password,
        ];

        // Update the user data
        $this->putJson('api/users/' . $user->id, $data)
            ->assertStatus(200)
            ->assertJson(['result' => true]);

        // Check database
        $this->assertDatabaseMissing(app(User::class)->getTable(), $user->toArray());
        $this->assertDatabaseHas(app(User::class)->getTable(), $factoryUser->toArray());

        // Check password
        $this->assertFalse(Hash::check($password, $user->password));
        $this->assertTrue(Hash::check($password, $factoryUser->password));
    }

    /**
     * Test API method: Update the specified resource in storage.
     * Failed action
     *
     * @dataProvider updateFailedProvider
     * @param Closure $callback Callback function for prepare test data
     */
    public function testUpdateFailed(Closure $callback)
    {
        [$data, $errors] = $callback();

        $user = User::factory()->create();

        $this->putJson('api/users/' . $user->id, $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors($errors);
    }

    /**
     * Data provider for the "testUpdateFailed" test
     *
     * @return array
     */
    public function updateFailedProvider()
    {
        return [
            'Empty POST data' => [fn() => [
                [],
                ['name', 'email', 'password'],
            ]],

            'Incorrect all POST data' => [fn() => [
                ['name' => '', 'email' => 'foo', 'password' => '1'],
                ['name', 'email', 'password'],
            ]],

            'Incorrect password' => [fn() => [
                ['name' => 'John Doe', 'email' => 'email@example.com', 'password' => '1'],
                ['password'],
            ]],

            'Duplicate email' => [function() {
                $user = User::factory()->create();
                return [
                    ['name' => 'John Doe', 'email' => $user->email, 'password' => '1'],
                    ['email', 'password'],
                ];
            }]
        ];
    }

    /**
     * Test API method: Remove the specified resource from storage.
     * Success action
     */
    public function testDestroySuccess()
    {
        $user = User::factory()->create();

        // Delete the user
        $this->deleteJson('api/users/' . $user->id)
            ->assertStatus(200)
            ->assertJson(['result' => true]);

        // Check database
        $this->assertDatabaseMissing(app(User::class)->getTable(), $user->only(['id']));
    }

    /**
     * Test API method: Remove the specified resource from storage.
     * Failed action
     */
    public function testDestroyFailed()
    {
        // User not found
        $this->deleteJson('api/users/0')
            ->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\User] 0']);
    }
}
