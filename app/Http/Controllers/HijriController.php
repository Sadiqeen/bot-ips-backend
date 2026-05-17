<?php

namespace App\Http\Controllers;

use App\Models\Hijri;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function syncHijriJsonToGithub(bool $dryRun = false): array
    {
        $targets = $this->getGithubSyncTargets();
        if (count($targets) === 0) {
            return [
                'ok' => false,
                'message' => 'No sync targets configured',
                'targets' => [],
            ];
        }

        $hijri = Hijri::orderBy('id', 'DESC')->get()->toArray();
        for ($i = 0; $i < count($hijri); $i++) {
            $hijri[$i]['deletable'] = $i === 0;
        }

        $payload = [
            'status' => 'success',
            'data' => [
                'data' => $hijri,
                'probable' => $this->probableNextMonth(),
                'nextMonth' => $this->nextMonth(),
            ],
            'updated_at' => Carbon::now()->toIso8601String(),
        ];

        $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($content === false) {
            Log::error('Hijri GitHub sync failed: cannot encode payload');
            return [
                'ok' => false,
                'message' => 'Cannot encode payload',
                'targets' => [],
            ];
        }

        $client = new Client(['base_uri' => 'https://api.github.com/']);
        $results = [];

        foreach ($targets as $target) {
            $owner = $target['owner'];
            $repo = $target['repo'];
            $path = $target['path'];
            $branch = $target['branch'];
            $token = $target['token'];
            $apiPath = "repos/{$owner}/{$repo}/contents/{$path}";

            $headers = [
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . $token,
                'X-GitHub-Api-Version' => '2022-11-28',
            ];

            try {
                $sha = null;

                try {
                    $response = $client->get($apiPath, [
                        'headers' => $headers,
                        'query' => ['ref' => $branch],
                    ]);
                    $body = json_decode((string) $response->getBody(), true);
                    $sha = $body['sha'] ?? null;
                } catch (\Throwable $th) {
                    // File may not exist yet on first sync.
                }

                if (!$dryRun) {
                    $requestBody = [
                        'message' => 'chore: sync hijri date json',
                        'content' => base64_encode($content),
                        'branch' => $branch,
                    ];

                    if ($sha) {
                        $requestBody['sha'] = $sha;
                    }

                    $client->put($apiPath, [
                        'headers' => $headers,
                        'json' => $requestBody,
                    ]);
                }

                $results[] = [
                    'target' => "{$owner}/{$repo}:{$path}@{$branch}",
                    'ok' => true,
                    'mode' => $dryRun ? 'dry-run' : 'push',
                ];
            } catch (\Throwable $th) {
                Log::error('Hijri GitHub sync failed [' . $owner . '/' . $repo . ']: ' . $th->getMessage());
                $results[] = [
                    'target' => "{$owner}/{$repo}:{$path}@{$branch}",
                    'ok' => false,
                    'mode' => $dryRun ? 'dry-run' : 'push',
                    'error' => $th->getMessage(),
                ];
            }
        }

        $allOk = true;
        foreach ($results as $result) {
            if (!$result['ok']) {
                $allOk = false;
                break;
            }
        }

        return [
            'ok' => $allOk,
            'targets' => $results,
        ];
    }

    private function getGithubSyncTargets(): array
    {
        $targets = [];
        $maxTargets = (int) config('github.SYNC_MAX_TARGETS', 10);
        $defaultPath = (string) config('github.SYNC_DEFAULT_PATH', 'hijris.json');
        $defaultBranch = (string) config('github.SYNC_DEFAULT_BRANCH', 'main');

        for ($i = 1; $i <= $maxTargets; $i++) {
            $owner = env("GITHUB_SYNC_OWNER_{$i}");
            $repo = env("GITHUB_SYNC_REPO_{$i}");
            $token = env("GITHUB_SYNC_TOKEN_{$i}");
            $path = env("GITHUB_SYNC_PATH_{$i}", $defaultPath);
            $branch = env("GITHUB_SYNC_BRANCH_{$i}", $defaultBranch);

            if ($owner && $repo && $token) {
                $targets[] = compact('owner', 'repo', 'path', 'branch', 'token');
            }
        }

        return $targets;
    }
}
