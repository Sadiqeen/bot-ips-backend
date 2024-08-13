<?php

namespace App\Http\Controllers;

use App\Models\Hijri;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HijriController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index']]);
    }

    public function index()
    {
        $hijri = Hijri::orderBy('id', 'DESC')->get();
        $hijri = $hijri->toArray();

        for ($i=0; $i < count($hijri); $i++) {
            $hijri[$i]['deletable'] = $i === 0 ? true : false;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'data' => $hijri,
                'probable' => $this->probableNextMonth(),
                'nextMonth' => $this->nextMonth(),
            ]
        ]);
    }

    public function updateMonth($date)
    {
        $nextMonth = $this->nextMonth();

        $hijri = new Hijri();
        $hijri->month_num = $nextMonth[1];
        $hijri->month_th = $nextMonth[0];
        $hijri->year = $nextMonth[2];
        $hijri->international = $date;
        $hijri->save();
    }

    public function extendDate($qt = 0)
    {
        return $this->dateCal($qt);
    }

    public function today()
    {
        return $this->dateCal();
    }

    public function dateCal($dateExtend = 0)
    {
        $date = Carbon::now()->addDay($dateExtend);
        $commonDate = Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'));
        $skip = 0;

        do {
            $currentIsDate = Hijri::skip($skip)->latest()->first();
            $islamicStartDate = Carbon::createFromFormat('Y-m-d', $currentIsDate->international);
            $skip++;
        } while ($commonDate->lessThan($islamicStartDate));

        $length = $islamicStartDate->diffInDays($commonDate);

        return ($length + 1)  . ' ' . $this->getMonth()[$currentIsDate->month_num] . ' ' . $currentIsDate->year;
    }

    public function probableNextMonth()
    {
        $currentIsDate = Hijri::orderBy('international', "DESC")->first();
        $islamicStartDate = Carbon::createFromFormat('Y-m-d', $currentIsDate->international);
        return [
            $islamicStartDate->addDays(29)->format('Y-m-d'),
            $islamicStartDate->addDays(1)->format('Y-m-d')
        ];
    }

    public function nextMonth()
    {
        $currentIsDate = Hijri::orderBy('international', "DESC")->first();

        if ($currentIsDate->month_num == 12) {
            $month = 1;
            $year = $currentIsDate->year + 1;
        } else {
            $month = $currentIsDate->month_num + 1;
            $year = $currentIsDate->year;
        }

        return [$this->getMonth()[$month], $month, $year];
    }

    public function saveStartMonthDate(Request $request)
    {
        $possibleDate = $this->probableNextMonth();

        if ($request->date == $possibleDate[0] || $request->date == $possibleDate[1]) {
            $this->updateMonth($request->date);
            $dateToFormat = Carbon::createFromFormat('Y-m-d', $request->date);
            (new TelegramController)->alert("ผลการดูดวงจันทร์ : " . $dateToFormat->locale('th')->translatedFormat("d F Y"));


            return response()->json([
                'status' => 'success',
                'data' => $request->date
            ]);
        }

        return response()->json([
            'status' => 'fail',
        ]);
    }

    public function delStartMonthDate(Request $request)
    {
        $hijri = Hijri::orderBy('id', 'DESC')->first();

        if ($hijri->id === $request->id) {
            $hijri->delete();

            return response()->json([
                'status' => 'success',
            ]);
        }

        return response()->json([
            'status' => 'fail',
        ]);
    }

    public function getMonth()
    {
        return [
            '1' => 'มูฮัรรอม',
            '2' => 'ซอฟัร',
            '3' => 'รอบีอุ้ลเอาวาล',
            '4' => 'รอบีอุ้ลอาเคร',
            '5' => 'ยะมาดิ้ลเอาวาล',
            '6' => 'ยะมาดิ้ลอาเคร',
            '7' => 'รอยับ',
            '8' => 'ชะบาน',
            '9' => 'รอมฎอน',
            '10' => 'เชาวาล',
            '11' => 'ซุ้ลเกาะดะห์',
            '12' => 'ซุ้ลฮิจยะห์',
        ];
    }
}
