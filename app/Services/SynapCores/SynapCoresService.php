<?php

namespace App\Services\SynapCores;

class SynapCoresService
{
    public function __construct(protected SynapCoresClient $client) {}

    public function trainModel(): void
    {
        $this->client->executeSql(
            "CREATE EXPERIMENT priority_triage_v1 WITH (target='human_priority', model_type='classification')"
        );
        $this->client->executeSql("TRAIN priority_triage_v1");
    }

    public function predict(array $features): string
    {
        $payload = json_encode($features);
        $result = $this->client->executeSql(
            "SELECT AUTOML.PREDICT('priority_triage_v1', ?) AS prediction",
            [$payload]
        );

        return $result[0]['prediction'] ?? 'P3';
    }

    public function getEmbeddings(string $text): array
    {
        $result = $this->client->executeSql("SELECT EMBED(?) AS vector", [$text]);
        return $result[0]['vector'] ?? [];
    }
}
