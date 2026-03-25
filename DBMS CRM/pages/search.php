<?php
// pages/search.php
require '../config/db.php';
include '../includes/header.php';

$q = $_GET['q'] ?? '';
$q_wildcard = "%" . $q . "%";

$customers = [];
$leads = [];
$deals = [];
$tickets = [];

if (strlen($q) >= 2) {
    try {
        // Customer Search
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, email FROM Customers WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? LIMIT 10");
        $stmt->execute([$q_wildcard, $q_wildcard, $q_wildcard]);
        $customers = $stmt->fetchAll();

        // Leads Search
        $stmt = $pdo->prepare("SELECT id, title, status FROM Leads WHERE title LIKE ? OR description LIKE ? LIMIT 10");
        $stmt->execute([$q_wildcard, $q_wildcard]);
        $leads = $stmt->fetchAll();

        // Deals Search
        $stmt = $pdo->prepare("SELECT id, title, stage, value FROM Deals WHERE title LIKE ? LIMIT 10");
        $stmt->execute([$q_wildcard]);
        $deals = $stmt->fetchAll();

        // Tickets Search
        $stmt = $pdo->prepare("SELECT id, subject, status FROM SupportTickets WHERE subject LIKE ? OR description LIKE ? LIMIT 10");
        $stmt->execute([$q_wildcard, $q_wildcard]);
        $tickets = $stmt->fetchAll();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Search Error: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Global Search Results for: "<span class="text-primary"><?= htmlspecialchars($q) ?></span>"</h2>
    <a href="javascript:history.back()" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Go Back</a>
</div>

<?php if(strlen($q) < 2): ?>
    <div class="alert alert-warning">Please enter at least 2 characters to search.</div>
<?php elseif(empty($customers) && empty($leads) && empty($deals) && empty($tickets)): ?>
    <div class="alert alert-info py-5 text-center">
        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
        <h4>No results found for "<?= htmlspecialchars($q) ?>"</h4>
        <p class="text-muted">Try checking for spelling errors or using more general keywords.</p>
    </div>
<?php else: ?>
    
    <div class="row g-4">
        <!-- Customers Results -->
        <?php if(!empty($customers)): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-top border-primary border-3 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-people-fill text-primary"></i> Accounts (<?= count($customers) ?>)</div>
                <div class="list-group list-group-flush">
                    <?php foreach($customers as $c): ?>
                    <a href="customer_view.php?id=<?= $c['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($c['company'] ?? $c['email']) ?></small>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Deals Results -->
        <?php if(!empty($deals)): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-top border-warning border-3 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-currency-dollar text-warning"></i> Opportunities (<?= count($deals) ?>)</div>
                <div class="list-group list-group-flush">
                    <?php foreach($deals as $d): ?>
                    <a href="deal_edit.php?id=<?= $d['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($d['title']) ?></div>
                            <small class="text-success fw-bold">$<?= number_format($d['value'], 2) ?></small>
                        </div>
                        <span class="badge bg-secondary"><?= htmlspecialchars($d['stage']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Leads Results -->
        <?php if(!empty($leads)): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-top border-success border-3 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-person-lines-fill text-success"></i> Leads (<?= count($leads) ?>)</div>
                <div class="list-group list-group-flush">
                    <?php foreach($leads as $l): ?>
                    <a href="lead_edit.php?id=<?= $l['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div class="fw-bold"><?= htmlspecialchars($l['title']) ?></div>
                        <span class="badge bg-secondary"><?= htmlspecialchars($l['status']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tickets Results -->
        <?php if(!empty($tickets)): ?>
        <div class="col-md-6">
            <div class="card shadow-sm border-top border-danger border-3 h-100">
                <div class="card-header bg-white fw-bold"><i class="bi bi-case-fill text-danger"></i> Cases (<?= count($tickets) ?>)</div>
                <div class="list-group list-group-flush">
                    <?php foreach($tickets as $t): ?>
                    <a href="ticket_edit.php?id=<?= $t['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div class="fw-bold"><?= htmlspecialchars($t['subject']) ?></div>
                        <span class="badge bg-secondary"><?= htmlspecialchars($t['status']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
