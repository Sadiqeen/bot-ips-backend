<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FacebookLog;
use App\Http\Controllers\FacebookController;
use Carbon\Carbon;

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

        if ($post['data']) {
            $log = new FacebookLog;
            $log->post_id = $post['data'];

            if ($comment['data']) {
                $log->comment_id = $post['data'];
            }

            $log->page_id = $page->id;
            $log->save();
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

    public function updatePost()
    {
        $telegam = new TelegramController;
        $facebook = new FacebookController;

        $pages = Page::with(["facebookLog" => function ($q) {
            $q->where('is_edited', 0)->whereDate('created_at', Carbon::today());
        }])->get();


        foreach ($pages as $page) {
                // Build prayer time message
                $message = (new MessageController)->buildMessage($page, true);

                $facebook->set_location($page);
                $is_success = $facebook->updatePost($page->facebookLog[0]->post_id, $message);

                if ($is_success) {
                    $log = FacebookLog::where('post_id', $page->facebookLog[0]->post_id)
                        ->where("is_edited", 0)
                        ->first();
                    $log->is_edited = 1;
                    $log->save();

                    $telegam->alert("{$page->name} : อัพเดทโพสสำเร็จ");
                } else {
                    $telegam->alert("{$page->name} : การอัพเดทโพสมีข้อผิดพลาด");
                }
        }
    }
}
