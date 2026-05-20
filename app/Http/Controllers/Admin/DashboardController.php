<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Fetch recently triaged tickets
        $recentTickets = Ticket::orderBy('id', 'desc')
            ->limit(15)
            ->get();

        // 2. Fetch raw matrix stats cross-comparing flags
        $matrixStats = Ticket::select('human_priority', 'predicted_priority', DB::raw('count(*) as count'))
            ->whereNotNull('predicted_priority')
            ->whereNotNull('human_priority')
            ->groupBy('human_priority', 'predicted_priority')
            ->get();

        // 3. Define the matrix headers we want to show on our frontend
        $priorities = ['P1', 'P2', 'P3', 'P4'];
        $matrix = [];

        foreach ($priorities as $human) {
            foreach ($priorities as $pred) {
                $matrix[$human][$pred] = 0;
            }
        }

        // 4. Translation map to harmonize SynapCores outputs with your frontend layout
        $labelMap = [
            'CRITICAL' => 'P1',
            'HIGH'     => 'P2',
            'MEDIUM'   => 'P3',
            'LOW'      => 'P4',
            'P1'       => 'P1', // Normalization fallback
            'P2'       => 'P2',
            'P3'       => 'P3',
            'P4'       => 'P4'
        ];

        // 5. Map data rows with string sanitization and translation conversion
        foreach ($matrixStats as $stat) {
            $rawHuman = strtoupper(trim($stat->human_priority));
            $rawPred  = strtoupper(trim($stat->predicted_priority));

            // Translate SynapCores strings (like "LOW" -> "P4") or default to the raw value
            $human = $labelMap[$rawHuman] ?? $rawHuman;
            $pred  = $labelMap[$rawPred]  ?? $rawPred;

            // Increment the counter if the translated keys match our matrix boundaries
            if (in_array($human, $priorities) && in_array($pred, $priorities)) {
                $matrix[$human][$pred] += $stat->count;
            }
        }

        return view('dashboard', compact('recentTickets', 'matrix', 'matrixStats'));
        // // 1. Fetch recently triaged tickets
        // $recentTickets = Ticket::orderBy('id', 'desc')
        //     ->limit(15)
        //     ->get();

        // // 2. Fetch raw matrix stats cross-comparing flags
        // $matrixStats = Ticket::select('human_priority', 'predicted_priority', DB::raw('count(*) as count'))
        //     ->whereNotNull('predicted_priority')
        //     ->whereNotNull('human_priority')
        //     ->groupBy('human_priority', 'predicted_priority')
        //     ->get();

        // // 3. Initialize a clean, empty 4x4 matrix mapping all combinations
        // $priorities = ['P1', 'P2', 'P3', 'P4'];
        // $matrix = [];

        // foreach ($priorities as $human) {
        //     foreach ($priorities as $pred) {
        //         $matrix[$human][$pred] = 0;
        //     }
        // }

        // // 4. Map the flat database rows into your nested matrix structure
        // foreach ($matrixStats as $stat) {
        //     $human = $stat->human_priority;
        //     $pred = $stat->predicted_priority;

        //     if (in_array($human, $priorities) && in_array($pred, $priorities)) {
        //         $matrix[$human][$pred] = $stat->count;
        //     }
        // }

        // // 5. Send both variables cleanly to your dashboard template
        // return view('dashboard', compact('recentTickets', 'matrix', 'matrixStats'));
    }
    public function handleWebhook(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'customer_tier' => 'required|string',
            'product_area' => 'required|string',
            'human_priority' => 'nullable|string'
        ]);

        $ticket = \App\Models\Ticket::create($validated);

        // Push calculation to your asynchronous queue
        \App\Jobs\ProcessTicketTriage::dispatch($ticket);

        return response()->json([
            'status' => 'queued',
            'ticket_id' => $ticket->id
        ], 202);
    }
}
