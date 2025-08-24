<?php

namespace App\Http\Controllers;

use App\Events\CreateMetaWebhook;
use Illuminate\Http\Request;

class MetaController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Webhook verification
        if ($request->query('hub_mode') === 'subscribe' && $request->query('hub_verify_token') == 12345678) {
            return response($request->query('hub_challenge'), 200);
        }
        $payload = $request->all();
        if (empty($payload) || !is_array($payload)) {
            return response()->json(['status' => 0, 'message' => 'Invalid payload'], 400);
        }       
        event(new CreateMetaWebhook($payload));
       
    }
}
