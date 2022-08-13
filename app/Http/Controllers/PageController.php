<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
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

        $page = Page::orderBy($sort[0], $sort[1])->paginate($perpage);

        return response()->json([
            'status' => 'success',
            'data' => $page
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|min:5|max:255',
            'url' => 'required|url|min:5|max:255',
            'page_id' => 'required|string|unique:pages,page_id',
            'token' => 'required|min:10|max:500',
        ]);

        $page = Page::create([
            'name' => $request->name,
            'url' => $request->url,
            'page_id' => $request->page_id,
            'token' => $request->token,
        ]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function edit($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $page
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|min:5|max:255',
            'url' => 'required|url|min:5|max:255',
            'page_id' => 'required|string|unique:pages,page_id,' . $id,
            'token' => 'required|min:10|max:500',
        ]);

        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $page->name = $request->name;
        $page->url = $request->url;
        $page->page_id = $request->page_id;
        $page->token = $request->token;
        $page->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function delete($id)
    {
        $page = Page::find($id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $page->delete();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function show($id)
    {
        $page = Page::with('district')->find($id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $page
        ]);
    }
}
