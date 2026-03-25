<?php
// includes/header.php
session_start();

// Redirect to login if user is not authenticated
$current_script = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['user_id']) && $current_script !== 'index.php') {
    header('Location: ../index.php');
    exit();
}

// Function to handle base path depending on whether we are in root or pages/ directory
function base_url($path = '') {
    $current_dir = basename(dirname($_SERVER['PHP_SELF']));
    $base = ($current_dir === 'pages') ? '../' : './';
    return $base . $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University DBMS CRM</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
    <!-- Sidebar-->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php include 'sidebar.php'; ?>
    <?php endif; ?>
    
    <!-- Page content wrapper-->
    <div id="page-content-wrapper" class="w-100">
        <!-- Top navigation-->
        <?php if (isset($_SESSION['user_id'])): ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-primary me-3 shadow-sm" id="sidebarToggle"><i class="bi bi-list"></i> Menu</button>
                
                <form action="<?= base_url('pages/search.php') ?>" method="GET" class="d-none d-md-flex w-50 mx-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted rounded-start-pill"><i class="bi bi-search"></i></span>
                        <input class="form-control bg-light border-start-0 shadow-none rounded-end-pill" type="search" name="q" placeholder="Search Accounts, Leads, Opportunities, Cases..." required>
                    </div>
                </form>

                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-secondary">Logged in as: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> (<?= htmlspecialchars($_SESSION['user_role']) ?>)</span>
                    <a href="<?= base_url('logout.php') ?>" class="btn btn-outline-danger btn-sm">Logout <i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </nav>
        <?php endif; ?>
        
        <!-- Main Content Container setup -->
        <div class="container-fluid p-4">
