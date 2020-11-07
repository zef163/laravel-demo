<?php

namespace App\Console\Commands;

use App\Articles\Elasticsearch;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;

class Search extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create indices in Elasticsearch';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Elastic
        $elastic = new Elasticsearch();

        $indices = [
            ['index' => app(User::class)->getTable(), 'items' => User::all()],
            ['index' => app(Task::class)->getTable(), 'items' => Task::all()],
        ];

        foreach ($indices as $arr) {
            // Create index
            $elastic->createIndices($arr['index']);

            // Index items
            if ($arr['items']->isNotEmpty()) {
                $params = ['body' => []];
                foreach ($arr['items'] as $item) {
                    $params['body'][] = [
                        'index' => [
                            '_index' => $arr['index'],
                            '_id' => $item->id,
                        ],
                    ];

                    $params['body'][] = $item->toArray();
                }
                $elastic->getClient()->bulk($params);
            }
        }

        return 0;
    }
}
