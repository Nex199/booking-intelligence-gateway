<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMockBookingWebhook;
use App\Models\Deal;
use App\Models\IntegrationLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleMockBooking(Request $request){
        if(IntegrationLog::where('event_id', $request->event_id)->first()){
            return response()->json(['status' => 'duplicate', 'message' => 'Event already processed'], 200);
        }
        $log = new IntegrationLog();
        $log->event_id = $request->event_id;
        $log->source = 'mock-booking';
        $log->event_type = $request->event_type;
        $log->external_id = $request->external_id;
        $log->payload = $request->all();
        $log->status = 'received';
        $log->save();
        ProcessMockBookingWebhook::dispatch($log->id);
        return response()->json(['status' => 'received'], 200);
    }
}
