<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\SynapCores\SynapCoresClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTicketTriage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(protected Ticket $ticket) {}

    /**
     * Execute the queue worker task loop
     *
     * 2. FIXED: Type-hint your clean client interface wrapper here
     */
    // public function handle(SynapCoresClient $synapCores): void
    // {
    //     try {
    //         // 3. Classify ticket priority using text features
    //         $prediction = $synapCores->predict([
    //             'subject' => $this->ticket->subject,
    //             'body' => $this->ticket->body,
    //             'customer_tier' => $this->ticket->customer_tier,
    //             'product_area' => $this->ticket->product_area
    //         ]);

    //         // 4. Fetch Vector Embeddings (Stretch Goal)
    //         $vector = $synapCores->getEmbeddings($this->ticket->body);

    //         // 5. Normalization Translation Map (Ensures raw output like 'low' maps to 'P4')
    //         $labelMap = [
    //             'CRITICAL' => 'P1', 'HIGH' => 'P2', 'MEDIUM' => 'P3', 'LOW' => 'P4',
    //             'P1' => 'P1', 'P2' => 'P2', 'P3' => 'P3', 'P4' => 'P4'
    //         ];

    //         $assignedStatus = $labelMap[strtoupper(trim($prediction))] ?? 'P3';

    //         // 6. Persist classification insights back to our database
    //         $this->ticket->update([
    //             'predicted_priority' => $assignedStatus,
    //             'body_vector' => json_encode($vector)
    //         ]);

    //         Log::info("Ticket #{$this->ticket->id} auto-triaged successfully to {$assignedStatus}");

    //     } catch (\Exception $e) {
    //         Log::error("Triage Worker Error on Ticket #{$this->ticket->id}: " . $e->getMessage());
    //         throw $e; // Throwing allows Laravel to retry based on your $tries parameter above
    //     }
    // }
    public function handle(SynapCoresClient $synapCores): void
    {
        Log::info("Starting triage processing loop for Ticket #{$this->ticket->id}...");

        try {
            // 1. Classify ticket priority using flat sequential features array
            Log::info("Sending prediction payload to SynapCores interface node...");

            $prediction = $synapCores->predict([
                'customer_tier' => $this->ticket->customer_tier,
                'product_area'  => $this->ticket->product_area,
                'subject'       => $this->ticket->subject,
                'body'          => $this->ticket->body,
            ]);

            Log::info("Prediction received from engine: '{$prediction}'");

            // 2. Fetch Vector Embeddings (Stretch Goal)
            Log::info("Requesting dense token vector embeddings text array...");
            $vector = $synapCores->getEmbeddings($this->ticket->body);

            // 3. Normalization Translation Map (Ensures raw output like 'low' maps to 'P4')
            $labelMap = [
                'CRITICAL' => 'P1', 'HIGH' => 'P2', 'MEDIUM' => 'P3', 'LOW' => 'P4',
                'P1' => 'P1', 'P2' => 'P2', 'P3' => 'P3', 'P4' => 'P4'
            ];

            $assignedStatus = $labelMap[strtoupper(trim($prediction))] ?? 'P3';

            // 4. Persist classification insights back to our database
            $this->ticket->update([
                'predicted_priority' => $assignedStatus,
                'body_vector'        => json_encode($vector)
            ]);

            Log::info("Ticket #{$this->ticket->id} auto-triaged successfully to {$assignedStatus}");

        } catch (\Exception $e) {
            Log::error("Triage Worker Fatal Crash on Ticket #{$this->ticket->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
