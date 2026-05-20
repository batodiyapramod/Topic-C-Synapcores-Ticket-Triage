

```markdown
# SynapCores Support Ticket Auto-Triage Monitor (`allcalls-triage-synapcores`)

An intelligent full-stack triage system built with Laravel that handles inbound support tickets via automated webhooks, offloads heavy text analysis to asynchronous queue pipelines, and uses the SynapCores machine learning engine to instantly classify ticket priorities (`P1` to `P4`). The platform includes a clean administrative monitor dashboard featuring a live multi-dimensional Confusion Matrix to evaluate model accuracy drift.

---

## Core Application Workflow Architecture

1. **Inbound Webhook Interface:** External systems dispatch a JSON payload to `POST /api/tickets`.
2. **Asynchronous Job Dispatch:** The application validates the payload, records a pending ticket row to MariaDB, and pushes a `ProcessTicketTriage` job wrapper onto the Laravel Queue.
3. **SynapCores API Evaluation Wrapper:** A long-lived background queue worker invokes the unified SynapCores AutoML layer using specialized `AUTOML.PREDICT` blocks over an authenticated HTTP connection pool.
4. **Data Normalization & Logging:** The worker normalizes variant model outputs (e.g., mapping prediction strings like `low` directly to operational targets like `P4`), saves the value, and writes a structural record into the application logging layer.
5. **Analytical Visual Layer:** The `/dashboard` panel pulls raw cross-tabulated intersections from database rows, building a 4x4 matrix view tracking human targets against machine inferences.

---

## Installation & Local System Deployment

Follow these structured steps to boot the complete environment end-to-end:

### 1. Project Initialization & Dependencies
Clone your public repository locally, move into the root folder, and initialize your PHP dependencies:
```bash
git clone <your-public-github-repo-url>
cd allcalls-triage-synapcores
composer install

```

### 2. Configure Environment Properties

Duplicate the repository baseline template and configure your persistent MariaDB and SynapCores connection parameters:

```bash
cp .env.example .env
php artisan key:generate

```

Open your `.env` file and make sure the following properties are accurately populated:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Asynchronous Background Processing Configuration
QUEUE_CONNECTION=database

# SynapCores API Authentication Gateway Context
SYNAPCORES_BASE_URL=[http://127.0.0.1:8080](http://127.0.0.1:8080)
SYNAPCORES_USERNAME=your_synapcores_admin_username
SYNAPCORES_API_KEY=your_settings_panel_generated_api_key

```

### 3. Database Migration & Historical Data Seeding

Expose the optional API routing system, apply system migrations, and execute the structural database matrix seeder to pre-populate historical classification data:

```bash
php artisan install:api
php artisan migrate
php artisan db:seed --class=TicketMatrixSeeder

```

### 4. Initialize and Train the Machine Learning Model

Ensure your local SynapCores daemon process is actively listening on its configuration port (`synapcores --port 8080`). Then run the dedicated Artisan training command to instantiate and build your classification experiment:

```bash
php artisan synapcores:train

```

### 5. Boot Up Application Run Channels

To run the platform end-to-end, execute these separate long-running background tracking processes in individual terminal tabs:

* **Terminal Tab 1 (Web Application Server Routing):**
```bash
php artisan serve


```



```
* **Terminal Tab 2 (Asynchronous Queue Job Processor Listener):**
  ```bash
  php artisan queue:work
  

```

Navigate to `http://127.0.0.1:8000/dashboard` in your browser to view the administrative monitor dashboard.


---

## License

Distributed under the official MIT License. See `LICENSE` inside the repository root for more information.

---

### Problem in code :


```
1. Community Edition "API endpoint not found: /v1/api-keys/93782282-175f-40f1-a11a4f96dc0e490c/stats. The feature may be unavailable in this edition (Community Edition
does not include all enterprise routes). See /v1/license for the list of features available on
this binary.)" so we use direct connection
2. php artisan synapcores:train
    After Run we get: Initiating model training on SynapCores engine...
3. Right now Simulate Inbound Webhook work for only first row 
4. Rigth now not working properly "Confusion Matrix Metric Evaluation Breakdown" Tabel 
 
```
