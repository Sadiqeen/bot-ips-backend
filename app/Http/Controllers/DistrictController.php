<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Page;
use Illuminate\Http\Request;

class DistrictController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $sort = $request->get('sort_by');
        $sort = explode(".", $sort);
        $perpage = $request->get('perpage') ?? 10;
        $page_id = $request->get('page_id');

        if ($page_id) {
            $district = District::where('page_id', $page_id)->orderBy($sort[0], $sort[1])->paginate($perpage);
        } else {
            $district = District::with('page')->orderBy($sort[0], $sort[1])->paginate($perpage);
        }

        return response()->json([
            'status' => 'success',
            'data' => $district
        ]);
    }

    public function store(Request $request, $page_id)
    {
        $page = Page::find($page_id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $this->validate($request, [
            'name' => 'required|string|min:2|max:255',
            'lat' => 'required|numeric|min:0|max:9999',
            'long' => 'required|numeric|min:0|max:9999',
        ]);

        $district = new District([
            'name' => $request->name,
            'lat' => $request->lat,
            'long' => $request->long,
        ]);

        $page->district()->save($district);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function delete($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $district->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }

}
