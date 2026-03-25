<?php
// pages/reports.php
require '../config/db.php';
include '../includes/header.php';

try {
    if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
        echo "<div class='container mt-5'><div class='alert alert-danger shadow-sm'><h4><i class='bi bi-shield-lock'></i> Access Denied</h4><p>You do not have permission to view system reports. This area is restricted to Administrators.</p></div></div>";
        include '../includes/footer.php';
        exit;
    }
    
    // 1. Leads by status
    $leads_by_status = $pdo->query("SELECT status, COUNT(*) as count FROM Leads GROUP BY status")->fetchAll();
    
    // 2. Sales Report
    $sales = $pdo->query("SELECT stage, SUM(value) as total_value, COUNT(*) as deal_count FROM Deals GROUP BY stage")->fetchAll();

    // 3. Tickets by Status
    $tickets_by_status = $pdo->query("SELECT status, COUNT(*) as count FROM SupportTickets GROUP BY status")->fetchAll();
    
    // 4. Leads assigned to users
    $leads_by_user = $pdo->query("SELECT u.name, COUNT(l.id) as lead_count FROM Users u LEFT JOIN Leads l ON u.id = l.user_id GROUP BY u.id")->fetchAll();
    
    // 5. General Stats
    $total_customers = $pdo->query("SELECT COUNT(*) FROM Customers")->fetchColumn();
    $total_sales_won = $pdo->query("SELECT SUM(value) FROM Deals WHERE stage = 'Closed Won'")->fetchColumn();
    $open_tickets = $pdo->query("SELECT COUNT(*) FROM SupportTickets WHERE status = 'Open'")->fetchColumn();
    
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error generating reports: " . $e->getMessage() . "</div>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>System Reports</h2>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print / Export</button>
</div>

<!-- Key Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white text-center p-3 shadow-sm h-100">
            <h5>Total Customers</h5>
            <h2 class="fw-bold"><?= $total_customers ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white text-center p-3 shadow-sm h-100">
            <h5>Total Sales (Closed Won)</h5>
            <h2 class="fw-bold">$<?= number_format($total_sales_won ?? 0, 2) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white text-center p-3 shadow-sm h-100">
            <h5>Open Tickets</h5>
            <h2 class="fw-bold"><?= $open_tickets ?></h2>
        </div>
    </div>
</div>

<div class="row">
    <!-- Leads by Status -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-pie-chart me-2"></i> Leads by Status</div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($leads_by_status as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['status']) ?></td>
                            <td><?= $l['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sales by Stage -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-bar-chart me-2"></i> Sales Pipeline</div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Stage</th>
                            <th>Total Deals</th>
                            <th>Total Value ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sales as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['stage']) ?></td>
                            <td><?= $s['deal_count'] ?></td>
                            <td class="text-end">$<?= number_format($s['total_value'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tickets by Status -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-headset me-2"></i> Support Tickets Overview</div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Ticket Status</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets_by_status as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['status']) ?></td>
                            <td><?= $t['count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Employee Performance (Leads Assigned) -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold"><i class="bi bi-person-badge me-2"></i> Employee Lead Assignments</div>
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead class="bg-light">
                        <tr>
                            <th>Employee Name</th>
                            <th>Assigned Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($leads_by_user as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['name']) ?></td>
                            <td><?= $u['lead_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
