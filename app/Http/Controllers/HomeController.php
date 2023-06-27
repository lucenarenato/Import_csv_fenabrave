<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\APIController;

class HomeController extends APIController
{
    public function home()
    {
        $data = [
            'page' => "This is the home page"
        ];

        $message = "You found me!";

        return $this->sendResponse($data, $message);
    }
}
