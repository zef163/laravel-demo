<?php

namespace App\Articles;

use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


/**
 * Class Elasticsearch
 * @package App\Repositories
 */
class Elasticsearch
{
    /**
     * @var Client Elasticsearch client
     */
    protected Client $client;

    /**
     * @var array Searchable fields
     */
    protected array $fields = [
        'users' => [
            'name^5',
            'email',
        ],
        'tasks' => [
            'title^5',
            'description^3',
        ],
    ];

    /**
     * Elasticsearch constructor.
     */
    public function __construct()
    {
        // Create client builder
        $clientBuilder = ClientBuilder::create();
        $clientBuilder->setHosts(['elasticsearch-demo:9200']);

        // Elasticsearch client
        $this->client = $clientBuilder->build();
    }

    /**
     * Get Elasticsearch client
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Create indices in Elasticsearch
     *
     * @param string $mainIndex Elastic index.
     */
    public function createIndices(string $mainIndex)
    {
        $index = ['index' => $mainIndex];

        // Delete indices
        if ($this->client->indices()->exists(compact('index'))) {
            $this->client->indices()->delete(compact('index'));
        }

        // Create index for search params
        $this->client->indices()->create([
            'index' => $mainIndex,
            'body' => [
                'settings' => [
                    'index' => [
                        'max_result_window' => 1000000000,
                        'analysis' => [
                            'analyzer' => [
                                'Demo_Analyzer' => [
                                    'tokenizer' => 'icu_tokenizer',
                                    'char_filter' => [
                                        'icu_normalizer',
                                        'demo_filter',
                                    ],
                                    'filter' => [
                                        'english_possessive_stemmer',
                                        'lowercase',
                                        'english_stemmer',
                                    ],
                                ],
                            ],
                            'char_filter' => [
                                'demo_filter' => [
                                    'type' => 'icu_normalizer',
                                    'name' => 'nfc',
                                    'mode' => 'decompose',
                                ]
                            ],
                            'filter' => [
                                'english_stemmer' => [
                                    'type' => 'stemmer',
                                    'language' => 'english',
                                ],
                                'english_possessive_stemmer' => [
                                    'type' => 'stemmer',
                                    'language' => 'possessive_english',
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Index a single item
     *
     * @param string $index Elastic index.
     * @param int $id Model identification.
     * @param array $body Data for indexing.
     * @return array
     */
    public function index(string $index, int $id, array $body): array
    {
        return $this->client->index(compact('index', 'id', 'body'));
    }

    /**
     * Update a single item
     *
     * @param string $index Elastic index.
     * @param int $id Model identification.
     * @param array $body Data for indexing.
     * @return array
     */
    public function update(string $index, int $id, array $body): array
    {
        return $this->client->bulk([
            'body' => [
                ['update' => ['_index' => $index, '_id' => $id]],
                ['doc' => $body],
            ]
        ]);
    }

    /**
     * Delete a single item
     *
     * @param string $index Elastic index.
     * @param int $id Model identification.
     * @return array|callable
     */
    public function delete(string $index, int $id)
    {
        return $this->client->bulk([
            'body' => [[
                'delete' => [
                    '_index' => $index,
                    '_id' => $id,
                ],
            ]],
        ]);
    }

    /**
     * Search by query
     *
     * @param string $index Elastic index.
     * @param string $query Query string.
     * @param array $pagination Pagination info.
     * @return array
     */
    public function search(string $index, string $query = '', array $pagination = []): array
    {
        $page = Arr::get($pagination, 'page', 1);
        $limit = Arr::get($pagination, 'limit', 15);

        $body = [
            'sort' => ['_score', ['id' => 'desc']],
            'track_total_hits' => true,
            'from' => ($page - 1) * $limit,
            'size' => $limit,
        ];

        // Query string
        if (!empty($query)) {
            Arr::set($body, 'query.bool.must.multi_match', [
                'fields' => $this->fields[$index],
                'query' => sprintf('%s, %s', preg_replace('/(\w{3,})/', '$1~', $query), $query),
                'type' => 'bool_prefix',
                'fuzziness' => 'auto',
                'analyzer' => 'Demo_Analyzer'
            ]);
        }

        try {
            // Search items
            $data = $this->client->search(compact('index', 'body'));
        } catch (\Exception $error) {
            // Empty result
            $data = [
                'took' => 0,
                'timed_out' => false,
                '_shards' => [
                    'total' => 0,
                    'successful' => 0,
                    'skipped' => 0,
                    'failed' => 1,
                ],
                'hits' => [
                    'total' => [
                        'value' => 0,
                        'relation' => 'eq',
                    ],
                    'max_score' => null,
                    'hits' => [],
                ]
            ];
        }

        return $data;
    }
}
