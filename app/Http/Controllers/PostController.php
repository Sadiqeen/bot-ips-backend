<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FacebookLog;
use App\Http\Controllers\FacebookController;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected TelegramController $telegramController;
    protected MessageController $messageController;
    protected FacebookController $facebookController;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        TelegramController $telegramController,
        MessageController $messageController,
        FacebookController $facebookController
    ) {
        $this->telegramController = $telegramController;
        $this->messageController = $messageController;
        $this->facebookController = $facebookController;
    }

    public function to(Request $request, $id)
    {
        if (!$this->verifyToken($request->input('token'))) {
            return response()->json([
                'status' => '401',
                'message' => 'Unauthorized.',
            ], 401);
        }

        $message = "";

        $page = Page::where('page_id', $id)->first();

        // Build prayer time message
        $message .= $this->messageController->buildMessage($page);

        $this->facebookController->set_location($page);
        $post = $this->facebookController->post($message);

        $comment = [
            "data" => "",
            "err" => "",
        ];

        // Comment to post
        if ($post["data"]) {
            // $comment_message = MessageController::otherPageMessage();
            // $comment = $this->facebookController->comment($post["data"], $comment_message);
        }

        // If post or comment was catch
        if ($post["err"] || $comment["err"]) {
            $this->telegramController->alert("{$page->name} : " . $post["err"]);
            $this->telegramController->alert("{$page->name} : " . $comment["err"]);

            return response()->json([
                'status' => '500',
                'message' => 'Can not post to page ' . $post["err"],
            ]);
        } else {
            // $this->telegramController->alert("{$page->name} : โพสสำเร็จ");
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
        $pages = Page::with(["facebookLog" => function ($q) {
            $q->where('is_edited', 0)->whereDate('created_at', Carbon::today());
        }])->get();


        foreach ($pages as $page) {
                // Build prayer time message
                $message = $this->messageController->buildMessage($page, true);

                $this->facebookController->set_location($page);
                $is_success = $this->facebookController->updatePost($page->facebookLog[0]->post_id, $message);

                if ($is_success) {
                    $log = FacebookLog::where('post_id', $page->facebookLog[0]->post_id)
                        ->where("is_edited", 0)
                        ->first();
                    $log->is_edited = 1;
                    $log->save();

                    $this->telegramController->alert("{$page->name} : อัพเดทโพสสำเร็จ");
                } else {
                    $this->telegramController->alert("{$page->name} : การอัพเดทโพสมีข้อผิดพลาด");
                }
        }
    }

    public function checkStartDate(Request $request)
    {
        if (!$this->verifyToken($request->input('token'))) {
            return response()->json([
                'status' => '401',
                'message' => 'Unauthorized.',
            ], 401);
        }

        $today = (new HijriController)->today();
        $date = explode(" ", $today);

        if ($date[0] == 29) {
            // Send update notification
            $text = "โปรดอัพเดทวันสำหรับเดือนถัดไป\n";
            $text .= "------------------------\n";
            $text .= "สำนักจุฬาราชมนตรี\n";
            $text .= "https://www.facebook.com/samnakjula";

            $this->telegramController->sendMessage([
                'text' => $text,
                'disable_web_page_preview' => false,
            ]);
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    private function verifyToken($token)
    {
        $token_to_validate = config('token.POST_TOKEN');

        if ($token !== $token_to_validate) {
            return false;
        }

        return true;
    }
}
