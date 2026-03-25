<?php
// pages/customer_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';

    if ($first_name && $last_name && $email && $phone) {
        // Check for duplicates
        $stmt = $pdo->prepare("SELECT id FROM Customers WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->fetch()) {
            $error = 'A customer with this email or phone already exists!';
        } else {
            try {
                $insert = $pdo->prepare("INSERT INTO Customers (first_name, last_name, email, phone, address) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$first_name, $last_name, $email, $phone, $address]);
                header("Location: customers.php?msg=Customer added successfully");
                exit();
            } catch(PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill all required fields!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Customer</h2>
    <a href="customers.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="card shadow-sm max-w-lg">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger bg-danger text-white border-0 py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Physical Address</label>
                <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Customer</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
