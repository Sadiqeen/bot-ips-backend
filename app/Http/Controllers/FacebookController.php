<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class FacebookController extends Controller
{

    protected $client;
    protected $page_id;
    protected $page_token;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://graph.facebook.com']);
    }

    public function set_location(object $location) : void
    {
        $this->page_id = $location->page_id;
        $this->page_token = $location->token;
    }

    public function post(string $message) : array
    {
        $post_id = '';
        $error = '';

        try {
            $response = $this->client->post("/{$this->page_id}/feed" , [
                "query" => [
                    'access_token' => $this->page_token
                ],
                "form_params" => [
                    "message" => $message,
                ]
            ]);

            $post_id = json_decode($response->getBody())->id;
        } catch (\Throwable $err) {
            $error = Str::limit($err, $limit = 150, $end = '...');
        }

        return [
            "data" => $post_id,
            "err" => $error
        ];
    }

    public function comment(string $post_id, string $message) : array
    {
        $comment_id = '';
        $error = '';

        try {

            $response = $this->client->post("/{$post_id}/comments" , [
                "query" => [
                    'access_token' => $this->page_token
                ],
                "form_params" => [
                    "message" => $message,
                ]
            ]);

            $comment_id = json_decode($response->getBody())->id;
        } catch (\Throwable $err) {
            $error = Str::limit($err, $limit = 150, $end = '...');
        }

        return [
            "data" => $comment_id,
            "err" => $error
        ];
    }
}
