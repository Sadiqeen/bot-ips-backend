<?php

namespace App\Http\Controllers;

use App\Models\FacebookLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FacebookLogController extends Controller
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
        $browser_total_raw = DB::raw('DATE(`created_at`) as date');
        $logs = FacebookLog::select(
            "created_at", $browser_total_raw
        )->groupBy('date')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }

    public function getLogOndate(Request $request)
    {
        $log = FacebookLog::with('page')->whereDate('created_at', $request->date)->get();

        return response()->json([
            'status' => 'success',
            'data' => $log
        ]);
    }
}
