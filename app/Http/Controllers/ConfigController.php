<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {

        $configs = Config::orderBy('id', "DESC")->get();

        return response()->json([
            'status' => 'success',
            'data' => $configs
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|min:5|max:255',
            'key' => 'required|min:5|max:255|unique:configs,key',
            'value' => 'required|string|min:1|max:255',
        ]);

        $config = Config::create([
            'name' => $request->name,
            'key' => $request->key,
            'value' => $request->value,
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function delete($id)
    {
        $config = Config::find($id);

        if (!$config) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $config->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }
}
