<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Helpers\ResponseApiHelper;
use App\Repository\UserRepository;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
       $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->get([
           'with' => ['role'],
           'search' => [
               'name' => request()->name,
           ],
           'page' => 5,
        ]);

        return ResponseApiHelper::success('Users retrieved successfully.', new UserCollection($users));
    }

    public function show(string $id)
    {
        //
    }
}
