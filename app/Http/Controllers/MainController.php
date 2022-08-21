<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\District;
use  App\Models\User;

class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $pages = Page::count();
        $districts = District::count();
        $users = User::count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'pages' => $pages,
                'districts' => $districts,
                'users' => $users,
            ]
        ]);
    }
}
