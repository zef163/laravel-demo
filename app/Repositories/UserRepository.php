<?php

namespace App\Repositories;

use App\Articles\Elasticsearch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    /**
     * Search users
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function search(Request $request): LengthAwarePaginator
    {
        $elastic = new Elasticsearch();
        $data = $elastic->search('users', $request->input('query', ''));

        $ids = Arr::pluck($data['hits']['hits'], '_id');
        if (empty($ids)) {
            $ids = [0];
        }

        // Pagination
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);

        $items = User::whereIn('id', $ids)
            ->orderBy(DB::raw('FIELD(`id`, ' . join(',', $ids) . ')'))
            ->get();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            Arr::get($data, 'hits.total.value'),
            $limit,
            $page,
        );
    }
}
