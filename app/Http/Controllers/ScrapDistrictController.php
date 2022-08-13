<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Page;
use Illuminate\Http\Request;
use Symfony\Component\HttpClient\HttpClient;
use Goutte\Client;

class ScrapDistrictController extends Controller
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

    public function run(Request $request)
    {
        $this->validate($request, [
            'page_id' => 'required',
            'province_id' => 'required',
            'first_district_id' => 'required',
            'last_district_id' => 'required',
        ]);

        $provinceId = $request->province_id;
        $firstDistrictId = $request->first_district_id;
        $lastDistrictId = $request->last_district_id;

        $page = Page::find($request->page_id);

        if (!$page) {
            return response()->json([
                'status' => 'fail',
            ]);
        }

        // Scraping data
        for ($districtId = $firstDistrictId; $districtId <= $lastDistrictId; $districtId++) {
            $district = $this->scrapData($provinceId, $districtId);

            $model = new District([
                'name' => $district['name'],
                'lat' => $district['lat'],
                'long' => $district['long'],
            ]);

            $page->district()->save($model);
        }

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function scrapData($province, $district): array
    {
        $client = new Client(HttpClient::create(['verify_peer' => false]));

        $content = "province_no=$province&amphor_no=$district&date_no=All&dfMonth=2&dyear=2565&today=9&jState=none&GP=1&latitude_form=&longitude_form=&sun=1&method=PS&aitisha=18&aitsubh=19&midsea=0&ct=2&mp=0&ju=1&dtime=%E0%B9%81%E0%B8%AA%E0%B8%94%E0%B8%87%E0%B9%80%E0%B8%A7%E0%B8%A5%E0%B8%B2%E0%B8%A5%E0%B8%B0%E0%B8%AB%E0%B8%A1%E0%B8%B2%E0%B8%94";
        $crawler = $client->request('POST', 'https://prayertime.muslimthaipost.com/index.php', [], [], ['HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded'], $content);

        $data = $crawler->filter("body")->each(function ($node) {
            $data = [
                "district" => $node->filter("div.sdmenu > div > table  > tbody > tr:nth-child(2) > td:nth-child(2) > font")->html(),
                "lat" => $node->filter("div.sdmenu > div > table  > tbody > tr:nth-child(3) > td:nth-child(2) > font")->html(),
                "long" => $node->filter("div.sdmenu > div > table  > tbody > tr:nth-child(4) > td:nth-child(2) > font")->html(),
            ];

            return $data;
        });

        $data = $data[0];

        $lat = $this->getFloat($data["lat"]);
        $long = $this->getFloat($data["long"]);

        return [
            "name" => $data["district"],
            "lat" => $lat,
            "long" => $long,
        ];
    }

    public function getFloat(string $string): float
    {
        $string = explode("อ", $string);
        return (float) $string[0];
    }
}
