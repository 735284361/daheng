<?php

namespace App\Services;

use App\Models\Feedback;
use Illuminate\Http\Request;

class UserService
{

    public function feedback(Request $request)
    {
        $feedback = new Feedback();
        $feedback->user_id = auth('api')->id();
        $feedback->type = $request->type;
        $feedback->content = $request->input('content');
        $feedback->phone = $request->phone;
        return $feedback->save();
    }

    public function bindPhone($phone)
    {

    }

}
