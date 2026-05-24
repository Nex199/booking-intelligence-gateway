<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(){
        $deals = Deal::all();
        return response()->json($deals);
    }
}
