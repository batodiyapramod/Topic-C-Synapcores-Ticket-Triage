<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Jobs\ProcessTicketTriage;
use Illuminate\Http\Request;

class TicketWebhookController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'customer_tier' => 'required|string|in:Enterprise,Premium,Standard',
            'product_area' => 'required|string',
            'human_priority' => 'required|string|in:P1,P2,P3,P4'
        ]);

        $ticket = Ticket::create($validated);

        // Instantly push parsing over to background queue worker
        ProcessTicketTriage::dispatch($ticket);

        return response()->json(['status' => 'queued', 'ticket_id' => $ticket->id], 202);
    }
}
