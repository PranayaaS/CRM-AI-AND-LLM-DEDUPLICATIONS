<?php
// pages/customer_view.php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;

try {
    // 1. Get Customer Details
    $stmt = $pdo->prepare("SELECT * FROM Customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch();
    if (!$customer) die("<div class='container mt-5'><div class='alert alert-danger'>Customer not found.</div></div>");

    // 2. Get Opportunities (Deals)
    $stmt = $pdo->prepare("SELECT * FROM Deals WHERE customer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$id]);
    $deals = $stmt->fetchAll();

    // 3. Get Cases (Tickets)
    $stmt = $pdo->prepare("CALL get_customer_tickets(?)");
    $stmt->execute([$id]);
    $tickets = $stmt->fetchAll();
    $stmt->closeCursor(); // Must close cursor after stored proc

    // 4. Get Activity Log (Interactions)
    $stmt = $pdo->prepare("SELECT * FROM Interactions WHERE customer_id = ? ORDER BY interaction_date DESC");
    $stmt->execute([$id]);
    $interactions = $stmt->fetchAll();

} catch(PDOException $e) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Error: " . $e->getMessage() . "</div></div>");
}
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2>Account Profile: <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></h2>
        <div>
            <a href="customers.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Accounts</a>
            <a href="customer_edit.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i> Edit Details</a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Details (35%) -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100 border-top border-primary border-4">
            <div class="card-header bg-white fw-bold fs-5 py-3"><i class="bi bi-person-badge text-primary me-2"></i> Account Details</div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.75rem;">Email</small>
                    <a href="mailto:<?= htmlspecialchars($customer['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($customer['email']) ?></a>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.75rem;">Phone</small>
                    <span><?= htmlspecialchars($customer['phone']) ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.75rem;">Company</small>
                    <span><?= htmlspecialchars($customer['company'] ?? 'N/A') ?></span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.75rem;">Address</small>
                    <span><?= nl2br(htmlspecialchars($customer['address'])) ?></span>
                </div>
            </div>
            <div class="card-footer bg-light text-muted small">
                Added on <?= date('M d, Y', strtotime($customer['created_at'])) ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Related Lists (65%) -->
    <div class="col-lg-8">
        <!-- Tabs Nav -->
        <ul class="nav nav-tabs border-bottom-0" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold text-dark border-top-0 border-start-0 border-end-0 border-primary border-3" id="deals-tab" data-bs-toggle="tab" data-bs-target="#deals" type="button" role="tab">Opportunities (<?= count($deals) ?>)</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted border-0" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab" onclick="this.classList.remove('text-muted'); this.classList.add('text-dark', 'border-bottom', 'border-primary', 'border-3'); document.getElementById('deals-tab').classList.remove('border-bottom', 'border-primary', 'text-dark'); document.getElementById('interaction-tab').classList.remove('border-bottom', 'border-primary', 'text-dark');">Cases (<?= count($tickets) ?>)</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold text-muted border-0" id="interaction-tab" data-bs-toggle="tab" data-bs-target="#interactions" type="button" role="tab" onclick="this.classList.remove('text-muted'); this.classList.add('text-dark', 'border-bottom', 'border-primary', 'border-3'); document.getElementById('deals-tab').classList.remove('border-bottom', 'border-primary', 'text-dark'); document.getElementById('tickets-tab').classList.remove('border-bottom', 'border-primary', 'text-dark');">Activity Log (<?= count($interactions) ?>)</button>
          </li>
        </ul>
        
        <!-- Tabs Content -->
        <div class="tab-content border p-0 bg-white shadow-sm mb-4 rounded-bottom" id="myTabContent">
          
          <!-- OPPORTUNITIES -->
          <div class="tab-pane fade show active" id="deals" role="tabpanel">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Opportunities</h6>
                <a href="deal_add.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">New</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size:0.8rem;"><tr><th>Title</th><th>Value</th><th>Stage</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($deals as $d): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($d['title']) ?></td>
                            <td>$<?= number_format($d['value'], 2) ?></td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if($d['stage'] == 'Prospect') $badge = 'info text-dark';
                                if($d['stage'] == 'Negotiation') $badge = 'warning text-dark';
                                if($d['stage'] == 'Closed Won') $badge = 'success';
                                if($d['stage'] == 'Closed Lost') $badge = 'danger';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= htmlspecialchars($d['stage']) ?></span>
                            </td>
                            <td><a href="deal_edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-light border text-primary"><i class="bi bi-pencil"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(!$deals) echo "<tr><td colspan='4' class='text-muted text-center py-4'>No opportunities found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
          </div>

          <!-- CASES -->
          <div class="tab-pane fade" id="tickets" role="tabpanel">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Cases</h6>
                <a href="ticket_add.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">New</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size:0.8rem;"><tr><th>Subject</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($t['subject']) ?></td>
                            <td><span class="badge bg-<?= $t['status'] == 'Open' ? 'danger' : ($t['status'] == 'In Progress' ? 'warning text-dark' : 'success') ?>"><?= htmlspecialchars($t['status']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                            <td><a href="ticket_edit.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-light border text-primary"><i class="bi bi-pencil"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(!$tickets) echo "<tr><td colspan='4' class='text-muted text-center py-4'>No cases found.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
          </div>

          <!-- ACTIVITIES -->
          <div class="tab-pane fade" id="interactions" role="tabpanel">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Activity Log</h6>
                <a href="interaction_add.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">Log Activity</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size:0.8rem;"><tr><th>Type</th><th>Notes</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php foreach($interactions as $i): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($i['interaction_type']) ?></span></td>
                            <td class="text-wrap" style="max-width: 250px;"><?= nl2br(htmlspecialchars($i['notes'])) ?></td>
                            <td class="text-nowrap"><?= date('M d, Y', strtotime($i['interaction_date'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(!$interactions) echo "<tr><td colspan='3' class='text-muted text-center py-4'>No activity logged.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
          </div>
          
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
