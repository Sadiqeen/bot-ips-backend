<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Hijri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TelegramHookController extends Controller
{
    protected TelegramController $telegramController;
    protected HijriController $hijriController;
    protected PostController $postController;

    public function __construct(
        TelegramController $telegramController,
        HijriController $hijriController,
        PostController $postController
    ) {
        $this->telegramController = $telegramController;
        $this->hijriController = $hijriController;
        $this->postController = $postController;
    }

    public function hook(Request $request)
    {
        $token_to_validate = config('token.TELEGRAM_HOOK_TOKEN');
        $request_token = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if ($request_token != $token_to_validate) {
            $this->telegramController->alert("Invalid token");
        } else {
            $payload = json_decode($request->getContent(), true);

            if (isset($payload['message'])) {
                $returnMessage = $this->filterMessage($payload['message']['text']);
                $this->telegramController->sendMessage($returnMessage);
            }

            if (isset($payload['callback_query'])) {
                $returnMessage = $this->filterMessage($payload['callback_query']['data']);
                $this->telegramController->sendMessage($returnMessage);
            }
        }
    }

    /**
     * Method filterMessage
     *
     * @param string $message [explicite description]
     *
     * @return array
     */
    private function filterMessage(string $message): array
    {
        $message = strtolower($message);

        // Set next month start date
        $setDate = explode(" ", $message);
        if ($setDate[0] === "ตั้งวันที่") {
            return $this->storeDate($setDate[1]);
        }

        // Filter message
        if ($message === "!" || $message === "/command" || $message === "รายการคำสั่ง") {
            return $this->command();
        }

        if ($message === "ลบผลการบันทึกล่าสุด") {
            return $this->deleteStartDate();
        }

        if ($message === "ดูผลการบันทึกย้อนหลัง") {
            return $this->showStartDateRecord();
        }

        if ($message === "บันทึกวันที่") {
            return $this->displayAvailableDate();
        }

        if ($message === "run migration") {
            return $this->runMigration();
        }

        if ($message === "run migration rollback") {
            return $this->runMigrationRollback();
        }

        return ['text' => "Hello World !\n/command"];
    }

    /**
     * Method storeDate
     *
     * @param int $date [explicite description]
     *
     * @return array
     */
    private function storeDate(string $date): array
    {
        // Date to update message
        $updateStatus = "วันที่บันทึกไม่ตรงกับการคำนวนในระบบ";
        $expectDates = $this->hijriController->probableNextMonth();

        if (in_array($date, $expectDates)) {
            $updateStatus = "อัพเดทสำเร็จ !";

            if ($expectDates[0] == $date) {
                $this->hijriController->updateMonth($date);
                $this->postController->updatePost();
            }

            if ($expectDates[1] == $date) {
                $this->postController->updatePost();
                $this->hijriController->updateMonth($date);
            }
        }

        return ['text' => $updateStatus];
    }

    /**
     * Method command
     *
     * @return array
     */
    private function command(): array
    {
        return [
            'text' => "โปรดระบุคำสั่ง",
            'reply_markup' => [
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        [
                            "text" => 'บันทึกวันที่',
                        ],
                    ],
                    [
                        [
                            "text" => 'ดูผลการบันทึกย้อนหลัง',
                        ],
                    ],
                    [
                        [
                            "text" => 'ลบผลการบันทึกล่าสุด',
                        ],
                    ],
                    [
                        [
                            "text" => 'Run migration',
                        ],
                    ],
                    [
                        [
                            "text" => 'Run migration rollback',
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * Method deleteStartDate
     *
     * @return array
     */
    private function deleteStartDate(): array
    {
        $isList = Hijri::whereDate('created_at', Carbon::today())->latest()->first();

        if ($isList) {
            $isList->delete();
            return ['text' => "ลบสำเร็จ !\n/command"];
        }

        return ['text' => "คำสั่งถูกปฏิเสธ\n/command"];
    }

    /**
     * Method showStartDateRecord
     *
     * @return array
     */
    private function showStartDateRecord(): array
    {
        $isList = Hijri::orderBy('id', "DESC")->limit(5)->get();
        $listMessage = "ผลการบันทึกย้อนหลัง\n";
        $listMessage .= "--------------------\n";

        foreach ($isList->toArray() as $date) {
            $monthNum = str_pad($date['month_num'], 2, '0', STR_PAD_LEFT);
            $listMessage .= "({$monthNum})   01 {$date['month_th']} {$date['year']} \n";

            $dateFromDB = Carbon::createFromFormat('Y-m-d', $date['international']);
            $listMessage .= "{$dateFromDB->locale('th')->translatedFormat("(m)   d F Y")} \n\n";
        }

        return ['text' => $listMessage];
    }

    /**
     * Method displayAvailableDate
     *
     * @return array
     */
    private function displayAvailableDate(): array
    {
        $isDate = $this->hijriController->today();
        $date = explode(" ", $isDate);
        $saved_today = Hijri::whereDate('created_at', Carbon::today())->first();
        $saved_yesterday = Hijri::whereDate('created_at', Carbon::yesterday())->first();

        if ($date[0] < 29) {
            return ['text' => "ไม่สามารถบันทึกก่อนเวลาได้ !\n/command"];
        }

        if ($saved_today || $saved_yesterday) {
            return ['text' => "วันเริ่มต้นเดือนได้ถูกบันทึกแล้ว !\n/command"];
        }

        $expectDate = $this->hijriController->probableNextMonth();
        return [
            'text' => "โปรดเลือกวัน",
            'reply_markup' => [
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        [
                            "text" => 'ตั้งวันที่ ' . $expectDate[0],
                        ],
                        [
                            "text" => 'ตั้งวันที่ ' . $expectDate[1],
                        ],
                    ],
                ]
            ]
        ];
    }

    public function runMigration(): array
    {
        try {
            Artisan::call("migrate", ['--force' => true]);
            return ['text' => "Migration Success !"];
        } catch (\Throwable $e) {
            return ['text' => $e->getMessage()];
        }
    }

    public function runMigrationRollback(): array
    {
        try {
            Artisan::call("migrate:rollback", ['--force' => true]);
            return ['text' => "Migration Rollback Success !"];
        } catch (\Throwable $e) {
            return ['text' => $e->getMessage()];
        }
    }
}
