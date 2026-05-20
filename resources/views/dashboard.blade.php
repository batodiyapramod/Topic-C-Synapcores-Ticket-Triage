<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SynapCores Support Auto-Triage Matrix Engine</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f4f6f9; color: #333; padding: 40px; }
        .container { max-width: 1100px; margin: 0 auto; }
        h1, h2 { color: #1e293b; margin-top: 0; }
        h2 { font-size: 1.5rem; margin-top: 30px; margin-bottom: 15px; }

        /* Layout Buttons */
        .btn { display: inline-block; padding: 10px 20px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; cursor: pointer; border-radius: 6px; border: none; transition: background 0.2s ease; }
        .btn-dark { background: #0f172a; color: #fff; }
        .btn-dark:hover { background: #1e293b; }

        /* Tables layout design overrides */
        table { width: 100%; border-collapse: collapse; background: #fff; margin-bottom: 40px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        th, td { padding: 14px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #0f172a; color: #fff; font-weight: 600; font-size: 14px; }
        td { font-size: 14px; color: #334155; }

        /* Badge Mapping Modules */
        .badge { padding: 6px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; text-transform: uppercase; }
        .badge-p1 { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
        .badge-p2 { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .badge-p3 { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; } /* Added missing P3 styles */
        .badge-p4 { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-pending { background: #f1f5f9; color: #64748b; font-style: italic; }
    </style>
</head>
<body>
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="margin: 0;">Support Ticket Auto-Triage Monitor Dashboard</h1>
            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Asynchronous machine learning pipeline status analytics engine</p>
        </div>

        <!-- Webhook Trigger Simulator Button -->
        <button type="button" onclick="triggerMockWebhook()" class="btn btn-dark">
            Simulate Inbound Webhook
        </button>
    </div>

    <hr style="border: 0; height: 1px; background: #cbd5e1; margin-bottom: 30px;">

    <h2>Confusion Matrix Metric Evaluation Breakdown</h2>
    <p style="color: #64748b; font-size: 14px; margin-top: -10px; margin-bottom: 20px;">
        Rows represent <strong>Actual Labels (Human)</strong>. Columns represent <strong>Predicted Labels (SynapCores AI)</strong>.
    </p>

    <table style="table-layout: fixed; text-align: center;">
        <thead>
            <tr>
                <th style="background: #1e293b; width: 20%;">Actual \ Predicted</th>
                <th style="text-align: center; width: 20%;">P1</th>
                <th style="text-align: center; width: 20%;">P2</th>
                <th style="text-align: center; width: 20%;">P3</th>
                <th style="text-align: center; width: 20%;">P4</th>
            </tr>
        </thead>
        <tbody>
            @php $priorities = ['P1', 'P2', 'P3', 'P4']; @endphp

            @foreach($priorities as $human)
                <tr>
                    <!-- Left-most Row Header (Actual Human Label) -->
                    <td style="background: #f8fafc; font-weight: bold; text-align: left; border-right: 2px solid #cbd5e1;">
                        <span class="badge badge-{{ strtolower($human) }}">{{ $human }}</span>
                    </td>

                    <!-- Dynamic X-Axis Intersection Columns (Predicted AI Label) -->
                    @foreach($priorities as $predicted)
                        @php
                            $count = $matrix[$human][$predicted] ?? 0;
                            $isDiagonal = ($human === $predicted);
                        @endphp
                        <td style="
                            font-size: 15px;
                            {{ $isDiagonal && $count > 0 ? 'background-color: #f0fdf4; color: #16a34a; font-weight: bold;' : '' }}
                            {{ !$isDiagonal && $count > 0 ? 'background-color: #fff1f2; color: #e11d48;' : '' }}
                        ">
                            {{ $count }} <span style="font-size: 11px; font-weight: normal; display: block; color: #94a3b8;">tickets</span>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Recently Triaged Webhook Inbound Flows</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Subject Context Summary</th>
                <th>Product Core</th>
                <th>Tier</th>
                <th>Human Assigned</th>
                <th>AI Triage Assignment</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentTickets as $ticket)
                <tr>
                    <td><strong>#{{ $ticket->id }}</strong></td>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ $ticket->product_area }}</td>
                    <td>{{ $ticket->customer_tier }}</td>
                    <td>
                        @if($ticket->human_priority)
                            <span class="badge badge-{{ strtolower($ticket->human_priority) }}">{{ $ticket->human_priority }}</span>
                        @else
                            <span class="text-muted small">None</span>
                        @endif
                    </td>
                    <td>
                        @if($ticket->predicted_priority)
                            <span class="badge badge-{{ strtolower($ticket->predicted_priority) }}">{{ $ticket->predicted_priority }}</span>
                        @else
                            <span class="badge badge-pending">Processing in Queue...</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">No inbound webhook ticket transactions recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
</body>

<script>
function triggerMockWebhook() {
    // Randomized sample priorities to generate mixed confusion matrix distributions
    const sampleLabels = ['P1', 'P2', 'P3', 'P4'];
    const randomHumanLabel = sampleLabels[Math.floor(Math.random() * sampleLabels.length)];

    const payload = {
        subject: "System Outage Alert - Connection Loop Exception",
        body: "API Gateway returns a consecutive 502 Bad Gateway timeout loop breakdown.",
        customer_tier: "Platinum",
        product_area: "Database Layer Optimization",
        human_priority: randomHumanLabel // Provided to evaluate accuracy variables
    };

    fetch("/api/tickets", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP network response returned an unexpected status code.');
        }
        return response.json();
    })
    .then(data => {
        alert("Webhook Status: " + data.status + "\nTicket Row ID: #" + data.ticket_id + "\n\nDispatched onto backend queue successfully!");
        window.location.reload();
    })
    .catch(error => {
        console.error("Error submitting webhook payload:", error);
        alert("Webhook dispatch failed. Make sure api routes are active and queues are listening.");
    });
}
</script>
</html>
