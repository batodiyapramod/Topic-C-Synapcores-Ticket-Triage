<?php

namespace App\Console\Commands;

use App\Services\SynapCores\SynapCoresService;
use Illuminate\Console\Command;

class TrainSynapCoresModel extends Command
{
    protected $signature = 'synapcores:train';
    protected $description = 'Trigger remote AIDB Model Compilation';

    public function handle(SynapCoresService $service)
    {
        $this->info('Compiling AutoML model parameters inside SynapCores...');

        try {
            $service->trainModel();
            $this->info('Model priority_triage_v1 successfully compiled and active.');
        } catch (\Exception $e) {
            $this->error('Compilation aborted: ' . $e->getMessage());
        }
    }
}
