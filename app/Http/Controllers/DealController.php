<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deal;

class DealController extends Controller
{
    public function index()
    {
        $deals = Deal::all();

        return response()->json($deals);
    }
}
