<?php
// pages/lead_edit.php
require '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$error = '';

if (!$id) {
    header("Location: leads.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM Leads WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();

if (!$lead) {
    echo "<div class='alert alert-danger'>Lead not found!</div>";
    include '../includes/footer.php';
    exit();
}

$users = $pdo->query("SELECT id, name FROM Users ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $user_id = $_POST['user_id'] ? $_POST['user_id'] : null;
    $status = $_POST['status'] ?? 'New';

    if ($title) {
        try {
            $update = $pdo->prepare("UPDATE Leads SET title=?, description=?, user_id=?, status=? WHERE id=?");
            $update->execute([$title, $description, $user_id, $status, $id]);
            
            // Note: If status changes to 'Converted', the database TRIGGER `after_lead_update` will fire.
            
            header("Location: leads.php?msg=Lead updated successfully");
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
    <h2>Edit Lead</h2>
    <a href="leads.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<?php
$stages = ['New', 'Contacted', 'Interested', 'Converted', 'Closed'];
$current_status_index = array_search($lead['status'] ?? 'New', $stages);
?>
<div class="sales-path mb-4 shadow-sm">
    <?php foreach($stages as $index => $stage): 
        $class = '';
        if ($lead['status'] == 'Closed' && $stage == 'Closed') $class = 'lost';
        elseif ($lead['status'] == 'Converted' && $index <= array_search('Converted', $stages)) $class = 'completed';
        elseif ($index < $current_status_index && $lead['status'] != 'Closed') $class = 'completed';
        elseif ($index == $current_status_index) $class = 'active';
    ?>
    <div class="sales-path-step <?= $class ?>"><?= $stage ?></div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm max-w-lg">
    <div class="card-body">
        <?php if($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Lead Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($_POST['title'] ?? $lead['title']) ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($_POST['description'] ?? $lead['description']) ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Assign To</label>
                    <select name="user_id" class="form-select">
                        <option value="">-- Unassigned --</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($lead['user_id'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <?php $l_status = $_POST['status'] ?? $lead['status']; ?>
                    <select name="status" class="form-select">
                        <option value="New" <?= ($l_status == 'New') ? 'selected' : '' ?>>New</option>
                        <option value="Contacted" <?= ($l_status == 'Contacted') ? 'selected' : '' ?>>Contacted</option>
                        <option value="Interested" <?= ($l_status == 'Interested') ? 'selected' : '' ?>>Interested</option>
                        <option value="Converted" <?= ($l_status == 'Converted') ? 'selected' : '' ?>>Converted</option>
                        <option value="Closed" <?= ($l_status == 'Closed') ? 'selected' : '' ?>>Closed</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Update Lead</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
