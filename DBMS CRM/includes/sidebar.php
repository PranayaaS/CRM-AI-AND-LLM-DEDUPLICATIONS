<?php
// includes/sidebar.php
?>
<div class="border-end bg-dark text-white" id="sidebar-wrapper">
    <div class="sidebar-heading border-bottom bg-primary text-white text-center py-3 fs-5 fw-bold">
        <i class="bi bi-database"></i> CRM System
    </div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('dashboard.php') ?>">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/calendar.php') ?>">
            <i class="bi bi-calendar-month me-2 text-info"></i> Task Calendar
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/customers.php') ?>">
            <i class="bi bi-people me-2"></i> Customers
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/leads.php') ?>">
            <i class="bi bi-person-lines-fill me-2"></i> Leads
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/followups.php') ?>">
            <i class="bi bi-calendar-check me-2"></i> Follow-Ups
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/deals.php') ?>">
            <i class="bi bi-currency-dollar me-2"></i> Opportunities
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/tickets.php') ?>">
            <i class="bi bi-case me-2"></i> Cases
        </a>
        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3" href="<?= base_url('pages/reports.php') ?>">
            <i class="bi bi-bar-chart-line me-2"></i> Reports
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3 text-primary fw-bold" href="<?= base_url('pages/ai_deduplication.php') ?>">
            <i class="bi bi-cpu me-2"></i> AI Engine
        </a>
        <a class="list-group-item list-group-item-action list-group-item-dark p-3 text-danger fw-bold" href="<?= base_url('pages/audit_log.php') ?>">
            <i class="bi bi-shield-lock me-2"></i> Audit Log
        </a>
        <?php endif; ?>
    </div>
</div>
