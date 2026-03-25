<?php
// dashboard.php
require 'config/db.php';
include 'includes/header.php';

// Fetch summary statistics
$stats = [
    'customers' => 0,
    'leads' => 0,
    'active_leads' => 0,
    'deals' => 0,
    'tickets' => 0
];

try {
    $stats['customers'] = $pdo->query("SELECT COUNT(*) FROM Customers")->fetchColumn();
    $stats['leads'] = $pdo->query("SELECT COUNT(*) FROM Leads")->fetchColumn();
    $stats['active_leads'] = $pdo->query("SELECT COUNT(*) FROM active_leads_view")->fetchColumn(); // Using VIEW
    $stats['deals'] = $pdo->query("SELECT COUNT(*) FROM Deals")->fetchColumn();
    $stats['tickets'] = $pdo->query("SELECT COUNT(*) FROM SupportTickets WHERE status = 'Open'")->fetchColumn();
    
    // Recent follow-ups
    $recent_followups = $pdo->query("SELECT f.*, l.title as lead_title FROM FollowUps f JOIN Leads l ON f.lead_id = l.id ORDER BY f.follow_up_date ASC LIMIT 5")->fetchAll();

    // Chart Data: Leads by Status
    $lead_status_counts = [];
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM Leads GROUP BY status");
    while($row = $stmt->fetch()) { $lead_status_counts[$row['status']] = $row['count']; }

    // Overdue alerts
    $overdue_followups = $pdo->query("SELECT COUNT(*) FROM FollowUps WHERE follow_up_date < CURDATE() AND status = 'Pending'")->fetchColumn();

} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error fetching stats: " . $e->getMessage() . "</div>";
}
?>

<h2 class="mb-4">Dashboard Overview</h2>

<?php if(!empty($overdue_followups) && $overdue_followups > 0): ?>
<div class="alert alert-danger shadow-sm border-start border-danger border-4 d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i> 
    <div>
        <strong>Action Required:</strong> You have <b><?= $overdue_followups ?></b> overdue follow-up task(s) that require immediate attention. 
        <a href="pages/followups.php" class="alert-link text-decoration-underline">View tasks now</a>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <!-- Customers -->
    <div class="col-md-4 col-xl-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Total Customers</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['customers'] ?></h2>
                </div>
                <i class="bi bi-people card-icon"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between border-0">
                <a class="text-white stretched-link text-decoration-none" href="pages/customers.php">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <!-- Active Leads -->
    <div class="col-md-4 col-xl-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Active Leads</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['active_leads'] ?> <span class="fs-6 fw-normal text-white-50">/ <?= $stats['leads'] ?> Total</span></h2>
                </div>
                <i class="bi bi-person-lines-fill card-icon"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between border-0">
                <a class="text-white stretched-link text-decoration-none" href="pages/leads.php">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Deals -->
    <div class="col-md-4 col-xl-3">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Total Deals</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['deals'] ?></h2>
                </div>
                <i class="bi bi-currency-dollar card-icon text-dark opacity-50"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between border-0">
                <a class="text-dark stretched-link text-decoration-none" href="pages/deals.php">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Open Tickets -->
    <div class="col-md-4 col-xl-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title mb-1">Open Tickets</h6>
                    <h2 class="mb-0 fw-bold"><?= $stats['tickets'] ?></h2>
                </div>
                <i class="bi bi-headset card-icon"></i>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between border-0">
                <a class="text-white stretched-link text-decoration-none" href="pages/tickets.php">View Details <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Column -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100 border-top border-primary border-3">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-pie-chart-fill me-1 text-primary"></i> Lead Pipeline Distribution
            </div>
            <div class="card-body d-flex justify-content-center align-items-center">
                <canvas id="leadsChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Follow-Ups Column -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <i class="bi bi-calendar-event me-1"></i> Upcoming Follow-ups
            </div>
            <div class="card-body p-0">
                <?php if ($recent_followups): ?>
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Lead</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_followups as $f): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($f['follow_up_date'])) ?></td>
                            <td><?= htmlspecialchars($f['lead_title']) ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($f['status'] == 'Pending') $badge = 'warning text-dark';
                                if($f['status'] == 'Completed') $badge = 'success';
                                if($f['status'] == 'Cancelled') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= $f['status'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-muted p-3 mb-0">No upcoming follow-ups.</p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-end">
                <a href="pages/followups.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('leadsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($lead_status_counts)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($lead_status_counts)) ?>,
                    backgroundColor: ['#0ea5e9', '#64748b', '#f59e0b', '#10b981', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 10 } }
                }
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
