<?php
/**
 * Taska Admin – Manage Taskas (Childcare Centres)
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name    = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        if (!$name) {
            $message = 'Taska name is required.';
            $msgType = 'danger';
        } else {
            db_insert('taskas.txt', [
                'name'    => $name,
                'address' => $address,
                'phone'   => $phone,
            ]);
            $message = 'Taska "' . htmlspecialchars($name) . '" created successfully.';
        }
    } elseif ($action === 'edit') {
        $id      = $_POST['id'] ?? '';
        $name    = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        if ($id && $name) {
            db_update('taskas.txt', $id, ['name' => $name, 'address' => $address, 'phone' => $phone]);
            $message = 'Taska updated.';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            db_delete('taskas.txt', $id);
            $message = 'Taska deleted.';
        }
    }
}

$taskas  = db_read('taskas.txt');
$assignments = db_read('assignments.txt');

$pageTitle = 'Manage Taskas';
$activeNav = 'taskas';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="page-header-row d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0"><i class="fa-solid fa-house-chimney me-2"></i>Manage Taskas</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskaModal">
            <i class="fa-solid fa-plus me-2"></i>Add Taska
        </button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($taskas as $taska):
            // Count assigned teachers
            $assignedTeachers = array_filter($assignments, fn($a) => $a['taska_id'] === $taska['id']);
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card post-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($taska['name']) ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item" onclick="editTaska(<?= htmlspecialchars(json_encode($taska)) ?>)">
                                        <i class="fa-solid fa-pen me-2"></i>Edit
                                    </button>
                                </li>
                                <li>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($taska['id']) ?>">
                                        <button class="dropdown-item text-danger btn-confirm-delete" type="submit">
                                            <i class="fa-solid fa-trash me-2"></i>Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php if ($taska['address']): ?>
                    <p class="card-text text-muted small mb-1">
                        <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($taska['address']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($taska['phone']): ?>
                    <p class="card-text text-muted small mb-2">
                        <i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($taska['phone']) ?>
                    </p>
                    <?php endif; ?>
                    <div class="mt-3 d-flex gap-2">
                        <span class="badge bg-success">
                            <i class="fa-solid fa-chalkboard-teacher me-1"></i><?= count($assignedTeachers) ?> Teacher(s)
                        </span>
                        <span class="text-muted small ms-auto">
                            <i class="fa-regular fa-calendar me-1"></i><?= date('d M Y', strtotime($taska['created_at'] ?? 'now')) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$taskas): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fa-solid fa-info-circle me-2"></i>No taskas yet. Click "Add Taska" to create one.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Taska Modal -->
<div class="modal fade" id="addTaskaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add New Taska</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Taska Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Taska Nur Hidayah" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full address"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+60 12-345 6789">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Taska Modal -->
<div class="modal fade" id="editTaskaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Taska</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Taska Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTaska(taska) {
    document.getElementById('editId').value      = taska.id;
    document.getElementById('editName').value    = taska.name;
    document.getElementById('editAddress').value = taska.address || '';
    document.getElementById('editPhone').value   = taska.phone || '';
    new bootstrap.Modal(document.getElementById('editTaskaModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
