<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Http\Controllers\FacebookController;

class PostController extends Controller
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

    public function to($id)
    {
        $message = "";

        $page = Page::where('page_id', $id)->first();

        // Build prayer time message
        $message .= (new MessageController)->buildMessage($page);

        $facebook = new FacebookController;
        $facebook->set_location($page);
        $post = $facebook->post($message);

        $comment = [
            "data" => "",
            "err" => "",
        ];

        // Comment to post
        if ($post["data"]) {
            $comment_message = MessageController::otherPageMessage();
            $comment = $facebook->comment($post["data"], $comment_message);
        }

        // If post or comment was catch
        if ($post["err"] || $comment["err"]) {
            (new TelegramController)->alert("{$page->name} : " . $post["err"]);
            (new TelegramController)->alert("{$page->name} : " . $comment["err"]);

            return response()->json([
                'status' => '500',
                'message' => 'Can not post to page ' . $post["err"],
            ]);
        } else {
            (new TelegramController)->alert("{$page->name} : โพสสำเร็จ");
        }

        return response()->json([
            'status' => '200',
            'message' => 'The operation was completed successfully',
            'data' => [
                'post_id' => $post["data"],
                'comment_id' => $comment["data"],
            ]
        ]);
    }
}
