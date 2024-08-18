<?php

namespace App\Http\Controllers;

use App\Models\Adjust;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdjustController extends Controller
{
    private $adjustId = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getAdjustment() : Array
    {
        return Adjust::find($this->adjustId)->toArray();
    }

    public function updateAdjustment(Request $request)
    {
        $this->validateAdjustment($request);

        $adjustment = Adjust::find($this->adjustId);

        if (!$adjustment) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        $adjustment->update($request->all());
        $adjustment->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function adjustPrayerTime(Request $request)
    {
        $this->validateAdjustment($request);

        $this->validate($request, [
            'lat' => 'required|numeric|min:0|max:9999',
            'long' => 'required|numeric|min:0|max:9999',
            'date' => 'required',
            'method' => 'required',
        ]);

        $times = PrayerController::calPrayerTime(
            $request->all(),
            Carbon::parse($request->date)->timezone('Asia/Bangkok')->toDateTime(),
            $request->lat,
            $request->long,
            $request->method
        );

        return response()->json([
            'status' => 'success',
            'data' => $times
        ]);
    }

    private function validateAdjustment($request)
    {
        $rules = 'required|numeric|min:-10|max:10';

        $this->validate($request, [
            'imsak' => $rules,
            'fajr' => $rules,
            'sunrise' => $rules,
            'dhuhr' => $rules,
            'asr' => $rules,
            'maghrib' => $rules,
            'sunset' => $rules,
            'isha' => $rules,
            'midnight' => $rules,
        ]);
    }

}
