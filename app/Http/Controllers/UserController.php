<?php

namespace App\Http\Controllers;

use App\Articles\Elasticsearch;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request HTTP request.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(app(UserRepository::class)->search($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request HTTP request.
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validate data
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique(User::class, 'email')],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Encrypt the password
        $data['password'] = bcrypt($data['password']);

        // Create the user
        $user = User::create($data);

        return response()->json($user->only(['id']));
    }

    /**
     * Display the specified resource.
     *
     * @param User $user User data.
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user->toArray());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request HTTP request.
     * @param User $user User data.
     * @return JsonResponse
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Validate data
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique(User::class, 'email')->ignore($user->id)],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Encrypt the password
        $data['password'] = bcrypt($data['password']);

        return response()->json([
            'result' => $user->fill($data)->save(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user User data.
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse
    {
        return response()->json([
            'result' => $user->delete(),
        ]);
    }
}
