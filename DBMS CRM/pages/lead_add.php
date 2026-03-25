<?php
// pages/lead_add.php
require '../config/db.php';
include '../includes/header.php';

$error = '';
// Fetch users for assignment
$users = $pdo->query("SELECT id, name FROM Users ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_POST['user_id'] ? $_POST['user_id'] : null;
    $status = $_POST['status'] ?? 'New';

    if ($title) {
        try {
            $insert = $pdo->prepare("INSERT INTO Leads (title, description, user_id, status) VALUES (?, ?, ?, ?)");
            $insert->execute([$title, $description, $user_id, $status]);
            header("Location: leads.php?msg=Lead added successfully");
            exit();
        } catch(PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Title is required!';
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Lead</h2>
    <a href="leads.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<div class="card shadow-sm max-w-lg">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Lead Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Assign To</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Unassigned --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="New" <?= (isset($_POST['status']) && $_POST['status'] == 'New') ? 'selected' : '' ?>>New</option>
                        <option value="Contacted" <?= (isset($_POST['status']) && $_POST['status'] == 'Contacted') ? 'selected' : '' ?>>Contacted</option>
                        <option value="Interested" <?= (isset($_POST['status']) && $_POST['status'] == 'Interested') ? 'selected' : '' ?>>Interested</option>
                        <option value="Converted" <?= (isset($_POST['status']) && $_POST['status'] == 'Converted') ? 'selected' : '' ?>>Converted</option>
                        <option value="Closed" <?= (isset($_POST['status']) && $_POST['status'] == 'Closed') ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Lead</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
