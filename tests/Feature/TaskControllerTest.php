<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test API method: Display a listing of the resource.
     */
    public function testIndexSuccess()
    {
        Task::factory(15)->create();

        $this->getJson('api/tasks')
            ->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'owner_id',
                        'reporter_id',
                        'title',
                        'description',
                        'created_at',
                        'updated_at',
                        'deleted_at',
                        'assignee' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
                        'reporter' => [
                            'id',
                            'name',
                            'email',
                            'email_verified_at',
                            'created_at',
                            'updated_at',
                        ],
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
     * Success action (Should create the task)
     */
    public function testStoreSuccess()
    {
        [$owner, $reporter] = User::factory(2)->create();

        // POST data
        $data = [
            'owner_id' => $owner->id,
            'reporter_id' => $reporter->id,
            'title' => $this->faker->text(30),
            'description' => $this->faker->text(500),
        ];

        // Create the task
        $nextId = $this->postJson('api/tasks', $data)
            ->assertStatus(200)
            ->assertJsonStructure(['id'])
            ->json('id');

        // Get created task
        $task = Task::find($nextId);

        // Check database
        $this->assertDatabaseHas(app(Task::class)->getTable(), $task->only([
            'owner_id',
            'reporter_id',
            'title',
            'description',
        ]));
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
        $this->postJson('api/tasks', $data)
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
                ['owner_id', 'reporter_id', 'title', 'description'],
            ],

            'Incorrect all POST data' => [
                ['owner_id' => 0, 'reporter_id' => 0, 'title' => '', 'description' => ''],
                ['owner_id', 'reporter_id', 'title', 'description'],
            ],

            'Incorrect user data' => [
                ['owner_id' => 0, 'reporter_id' => 0, 'title' => 'Foo', 'description' => 'Bar'],
                ['owner_id', 'reporter_id'],
            ]
        ];
    }

    /**
     * Test API method: Display the specified resource.
     * Success action (Should return task data)
     */
    public function testShowSuccess()
    {
        // Create test task
        $task = Task::factory()->create();

        $this->getJson('api/tasks/' . $task->id)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'owner_id',
                'reporter_id',
                'title',
                'description',
                'created_at',
                'updated_at',
                'deleted_at',
            ])
            ->assertJson($task->toArray());
    }

    /**
     * Test API method: Display the specified resource.
     * Failed action
     */
    public function testShowFailed()
    {
        // Task not found
        $this->getJson('api/tasks/0')
            ->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\Task] 0']);
    }

    /**
     * Test API method: Update the specified resource in storage.
     * Success action
     */
    public function testUpdateSuccess()
    {
        // Create test task
        $task = Task::factory()->create();


        // New task data
        $factoryTask = Task::factory()->make();

        // POST data
        $data = [
            'owner_id' => $factoryTask->owner_id,
            'reporter_id' => $factoryTask->reporter_id,
            'title' => $factoryTask->title,
            'description' => $factoryTask->description,
        ];

        // Update the task data
        $this->putJson('api/tasks/' . $task->id, $data)
            ->assertStatus(200)
            ->assertJson(['result' => true]);

        // Check database
        $this->assertDatabaseMissing(app(Task::class)->getTable(), $task->toArray());
        $this->assertDatabaseHas(app(Task::class)->getTable(), $factoryTask->toArray());
    }

    /**
     * Test API method: Update the specified resource in storage.
     * Failed action
     *
     * @dataProvider updateFailedProvider
     * @param array $data POST data.
     * @param array $errors Available validation errors.
     */
    public function testUpdateFailed(array $data, array $errors)
    {
        $task = Task::factory()->create();

        $this->putJson('api/tasks/' . $task->id, $data)
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
            'Empty POST data' => [
                [],
                ['owner_id', 'reporter_id', 'title', 'description'],
            ],

            'Incorrect all POST data' => [
                ['owner_id' => 0, 'reporter_id' => 0, 'title' => '', 'description' => ''],
                ['owner_id', 'reporter_id', 'title', 'description'],
            ],

            'Incorrect user data' => [
                ['owner_id' => 0, 'reporter_id' => 0, 'title' => 'Foo', 'description' => 'Bar'],
                ['owner_id', 'reporter_id'],
            ]
        ];
    }

    /**
     * Test API method: Remove the specified resource from storage.
     * Success action
     */
    public function testDestroySuccess()
    {
        $task = Task::factory()->create();

        // Check database
        $this->assertDatabaseHas(app(Task::class)->getTable(), ['id' => $task->id, 'deleted_at' => null]);

        // Delete the task
        $this->deleteJson('api/tasks/' . $task->id)
            ->assertStatus(200)
            ->assertJson(['result' => true]);

        // Check database
        $this->assertDatabaseMissing(app(Task::class)->getTable(), ['id' => $task->id, 'deleted_at' => null]);
    }

    /**
     * Test API method: Remove the specified resource from storage.
     * Failed action
     */
    public function testDestroyFailed()
    {
        // Task not found
        $this->deleteJson('api/tasks/0')
            ->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\Task] 0']);
    }
}
