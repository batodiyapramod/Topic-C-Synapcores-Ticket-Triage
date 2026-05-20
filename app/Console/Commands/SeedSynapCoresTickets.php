<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SeedSynapCoresTickets extends Command
{
    protected $signature = 'synapcores:seed';
    protected $description = 'Seed historical tickets with real ML language signals';

    public function handle()
    {
        $this->info('Generating signal clusters...');

        $signals = [
            'P1' => [
                'subjects' => ['CRITICAL: Production Outage', 'Data Loss Incident', 'Database cluster is down'],
                'bodies' => ['We are experiencing a severe production outage.', 'All customer data dropped completely.', 'Complete system crash, urgent emergency escalation requested.'],
                'areas' => ['Database', 'Auth']
            ],
            'P2' => [
                'subjects' => ['Billing checkout failing', 'Webhook signature validation errors', 'S3 file storage timeout'],
                'bodies' => ['Customers cannot process invoices at checkout.', 'The webhooks return validation issues continuously.', 'File uploads are stalling out completely on production.'],
                'areas' => ['Billing', 'Storage']
            ],
            'P4' => [
                'subjects' => ['How do I reset tokens?', 'Documentation clarification', 'Where is the dark mode toggle?'],
                'bodies' => ['Just a general question regarding standard API keys.', 'Can you explain the layout options listed in the documentation?', 'Feature request to adjust table cell configurations.'],
                'areas' => ['UI', 'Documentation']
            ]
        ];

        for ($i = 0; $i < 4000; $i++) {
            $priority = Arr::random(['P1', 'P2', 'P4']);
            $cluster = $signals[$priority];

            Ticket::create([
                'subject' => Arr::random($cluster['subjects']) . " #" . rand(100, 999),
                'body' => Arr::random($cluster['bodies']) . " Please verify this issue.",
                'customer_tier' => Arr::random(['Enterprise', 'Premium', 'Standard']),
                'product_area' => Arr::random($cluster['areas']),
                'human_priority' => $priority,
            ]);
        }

        $this->info('Seeded 4,000 highly distinct training rows successfully.');
    }
}
