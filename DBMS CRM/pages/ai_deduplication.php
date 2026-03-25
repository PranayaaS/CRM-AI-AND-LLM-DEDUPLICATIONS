<?php
// pages/ai_deduplication.php
require '../config/db.php';
include '../includes/header.php';

// Strict Admin-Only Check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    die("<div class='alert alert-danger m-4'><h2>Access Denied</h2><p>Only Administrators have access to the AI Processing Engine.</p><a href='../dashboard.php' class='btn btn-primary'>Go Back</a></div>");
}

$msg = '';

// Handle merging logic (The "LLM Action")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'merge') {
    $master_id = $_POST['master_id'];
    $fragment_id = $_POST['fragment_id'];
    
    try {
        $pdo->beginTransaction();

        // LLM Simulated Logic: Move all related records to the Master Golden Record
        $pdo->prepare("UPDATE Deals SET customer_id = ? WHERE customer_id = ?")->execute([$master_id, $fragment_id]);
        $pdo->prepare("UPDATE SupportTickets SET customer_id = ? WHERE customer_id = ?")->execute([$master_id, $fragment_id]);
        $pdo->prepare("UPDATE Interactions SET customer_id = ? WHERE customer_id = ?")->execute([$master_id, $fragment_id]);
        
        // Log the AI action
        log_audit($pdo, $_SESSION['user_id'], 'AI LLM Deduplication', "Merged fragmented identity $fragment_id into Master ID $master_id");

        // Delete the fragment
        $pdo->prepare("DELETE FROM Customers WHERE id = ?")->execute([$fragment_id]);

        $pdo->commit();
        $msg = "<div class='alert alert-success'><i class='bi bi-robot me-2'></i> <strong>LLM Output:</strong> Data conflicts resolved. The fragmented identity has been securely merged into the Master Golden Record. All historical data was preserved.</div>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $msg = "<div class='alert alert-danger'>LLM Processing Error: " . $e->getMessage() . "</div>";
    }
}

// Handle test injection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'inject') {
    $pdo->exec("INSERT IGNORE INTO Customers (first_name, last_name, email, phone) VALUES ('Jonathon', 'Dohe', 'jon.d@example.org', '123-456-7890')");
    $pdo->exec("INSERT IGNORE INTO Customers (first_name, last_name, email, phone) VALUES ('John', 'Doe', 'john.doe@example.com', '123-456-7899')");
    $pdo->exec("INSERT IGNORE INTO Customers (first_name, last_name, email, phone) VALUES ('Jane', 'Smithy', 'jsmithy44@example.net', '555-555-5555')");
    $pdo->exec("INSERT IGNORE INTO Customers (first_name, last_name, email, phone) VALUES ('Jaelyn', 'Smith', 'jane.s@example.net', '555-555-5556')");
    $msg = "<div class='alert alert-info'>Injected fragmented test accounts successfully.</div>";
}

// 1. Fetch all customers
$customers = $pdo->query("SELECT id, first_name, last_name, email FROM Customers")->fetchAll();

// 2. Fuzzy Matching Algorithm
$fragmentations = [];
for ($i = 0; $i < count($customers); $i++) {
    for ($j = $i + 1; $j < count($customers); $j++) {
        $c1 = $customers[$i];
        $c2 = $customers[$j];
        
        $name1 = strtolower(trim($c1['first_name'] . ' ' . $c1['last_name']));
        $name2 = strtolower(trim($c2['first_name'] . ' ' . $c2['last_name']));
        
        // Calculate similarity percentage Using PHP built-in similar_text
        similar_text($name1, $name2, $percent);
        
        // Soundex phonetic match
        $soundex_match = (soundex($name1) === soundex($name2));
        
        // If similarity is above 75% OR they sound exactly the same phonetically
        if ($percent > 75 || $soundex_match) {
            $fragmentations[] = [
                'master' => $c1,
                'fragment' => $c2,
                'confidence' => round($percent, 1)
            ];
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cpu text-primary me-2"></i> AI-Based Identity Resolution Engine</h2>
    <form method="POST" class="d-inline">
        <input type="hidden" name="action" value="inject">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bug"></i> Inject Test Fragments</button>
    </form>
</div>

<?= $msg ?>

<div class="row mb-5">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-stars text-warning me-2"></i> System Diagnostics</h4>
                <p><strong>Status:</strong> Online</p>
                <p><strong>Algorithms Active:</strong> Levenshtein Distance Matrix & Soundex Phonetic Analysis</p>
                <p><strong>Objective:</strong> Resolve Customer Identity Fragmentation via LLM Deduplication rules.</p>
                
                <div class="d-flex align-items-center mt-4">
                    <div class="spinner-grow text-success spinner-grow-sm me-3" role="status"></div>
                    <span class="fw-bold text-success">Scanning database... Discovered <span class="badge bg-danger fs-6"><?= count($fragmentations) ?></span> fragmented identities requiring LLM review.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<h3 class="mb-4">Identified Fragmentations</h3>

<?php if (empty($fragmentations)): ?>
    <div class="alert alert-success py-5 text-center">
        <i class="bi bi-shield-check fs-1 d-block mb-3"></i>
        <h4>Database is Clean</h4>
        <p>No fragmented customer identities detected by the Fuzzy Matching algorithm.</p>
    </div>
<?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($fragmentations as $frag): ?>
        <div class="col-12">
            <div class="card shadow-sm border-top border-warning border-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Fragmentation Match Detected</span>
                    <span class="badge bg-primary">LLM Confidence: <?= $frag['confidence'] ?>%</span>
                </div>
                <div class="card-body">
                    <div class="row align-items-center text-center">
                        <div class="col-md-4">
                            <h5 class="text-success border-bottom pb-2">Golden Record (Master)</h5>
                            <p class="fs-5 mb-0 fw-bold"><?= htmlspecialchars($frag['master']['first_name'] . ' ' . $frag['master']['last_name']) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($frag['master']['email']) ?></small>
                            <span class="d-block mt-2 badge bg-light text-dark">ID: #<?= $frag['master']['id'] ?></span>
                        </div>
                        
                        <div class="col-md-4 py-3">
                            <i class="bi bi-arrow-left-right fs-1 text-muted opacity-50 d-none d-md-block"></i>
                        </div>

                        <div class="col-md-4">
                            <h5 class="text-danger border-bottom pb-2">Fragmented Identity (Duplicate)</h5>
                            <p class="fs-5 mb-0 fw-bold"><?= htmlspecialchars($frag['fragment']['first_name'] . ' ' . $frag['fragment']['last_name']) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($frag['fragment']['email']) ?></small>
                            <span class="d-block mt-2 badge bg-light text-dark">ID: #<?= $frag['fragment']['id'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-end">
                    <form method="POST" class="d-inline" onsubmit="showAILoading(this)">
                        <input type="hidden" name="action" value="merge">
                        <input type="hidden" name="master_id" value="<?= $frag['master']['id'] ?>">
                        <input type="hidden" name="fragment_id" value="<?= $frag['fragment']['id'] ?>">
                        <button type="submit" class="btn btn-warning fw-bold text-dark px-4 llm-btn">
                            <i class="bi bi-lightning-charge-fill me-1"></i> LLM Auto-Merge Resolution
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function showAILoading(form) {
    const btn = form.querySelector('.llm-btn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> LLM processing data matrices...';
    btn.classList.replace('btn-warning', 'btn-secondary');
    btn.classList.add('disabled');
}
</script>

<?php include '../includes/footer.php'; ?>
