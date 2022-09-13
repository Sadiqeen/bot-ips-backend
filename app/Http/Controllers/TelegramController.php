<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use App\Models\Config;

class TelegramController extends Controller
{
    protected $url = "https://api.telegram.org/bot";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function alert($message)
    {
        $this->sendMessage(['text' => $message]);
    }

    public function sendMessage(array $message): void
    {
        $chat_id = env("TELEGRAM_CHAT_ID");
        $token = env("TELEGRAM_TOKEN");
        $endpoint =  $this->url . $token->value . '/';

        $client = new Client();

        // Set chat id
        $standard = [
            'chat_id' => $chat_id->value,
            'reply_markup' => [
                'one_time_keyboard' => true,
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        [
                            "text" => 'รายการคำสั่ง',
                        ],
                    ],
                ]
            ]
        ];

        // Loop to set message
        foreach ($message as $key => $value) {
            $standard[$key] = $value;
        }

        $response = $client->post($endpoint . 'sendMessage', [
            RequestOptions::JSON => $standard
        ]);
    }
}
