<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class StartController extends Controller
{
    function __construct()
    {
        $rk = new \RdKafka\Producer();
        if (empty($rk))
            throw new Exception("Producer error");

        echo "Started";
    }
}
