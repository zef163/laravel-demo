<?php

namespace App\Observers;

use App\Articles\Elasticsearch;
use App\Models\Task;
use App\Models\User;

class ElasticsearchObserver
{
    /**
     * @var Elasticsearch
     */
    protected Elasticsearch $elastic;

    /**
     * ElasticsearchObserver constructor.
     */
    public function __construct()
    {
        $this->elastic = new Elasticsearch();
    }

    /**
     * Handle the User "created" event.
     *
     * @param User|Task $item Item data.
     * @return void
     */
    public function created($item)
    {
        $this->elastic->index($item->getTable(), $item->id, $item->toArray());
    }

    /**
     * Handle the User "updated" event.
     *
     * @param User|Task $item Item data.
     * @return void
     */
    public function updated($item)
    {
        $this->elastic->update($item->getTable(), $item->id, $item->toArray());
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param User|Task $item Item data.
     * @return void
     */
    public function deleted($item)
    {
        $this->elastic->delete($item->getTable(), $item->id);
    }

    /**
     * Handle the User "restored" event.
     *
     * @param User|Task $item Item data.
     * @return void
     */
    public function restored($item)
    {
        $this->elastic->index($item->getTable(), $item->id, $item->toArray());
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User|Task $item Item data.
     * @return void
     */
    public function forceDeleted($item)
    {
        $this->elastic->delete($item->getTable(), $item->id);
    }
}
