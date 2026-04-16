<?php
/**
 * Taska Teacher – Create Post
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('teacher');

$message = '';
$msgType = 'success';

// Get teacher's assigned taskas
$assignments = db_find_all('assignments.txt', 'teacher_id', $user['id']);
$taskaIds    = array_column($assignments, 'taska_id');
$myTaskas    = array_filter(db_read('taskas.txt'), fn($t) => in_array($t['id'], $taskaIds, true));

// All children in my taskas
$allChildren = db_read('children.txt');
$myChildren  = array_filter($allChildren, fn($c) => in_array($c['taska_id'] ?? '', $taskaIds, true));

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? 'other';
    $content  = trim($_POST['content'] ?? '');
    $taska_id = trim($_POST['taska_id'] ?? '');
    $child_id = trim($_POST['child_id'] ?? '');

    $validCategories = ['feed', 'rest', 'nappy', 'hygiene', 'health', 'other'];
    if (!in_array($category, $validCategories, true)) {
        $category = 'other';
    }

    if (!$content) {
        $message = 'Post content cannot be empty.';
        $msgType = 'danger';
    } elseif ($taska_id && !in_array($taska_id, $taskaIds, true)) {
        $message = 'Invalid taska selected.';
        $msgType = 'danger';
    } else {
        // Handle media uploads
        $mediaFiles = [];
        if (!empty($_FILES['media']['name'][0])) {
            $uploadDir = __DIR__ . '/../uploads/';
            $allowed   = ['jpg','jpeg','png','gif','mp4','mov','webm','avi'];
            foreach ($_FILES['media']['tmp_name'] as $i => $tmpName) {
                if (!is_uploaded_file($tmpName)) continue;
                $origName = $_FILES['media']['name'][$i];
                $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed, true)) {
                    $message = 'File type not allowed: ' . htmlspecialchars($ext);
                    $msgType = 'warning';
                    continue;
                }
                $size = $_FILES['media']['size'][$i];
                if ($size > 50 * 1024 * 1024) { // 50 MB limit
                    $message = 'File too large (max 50 MB): ' . htmlspecialchars($origName);
                    $msgType = 'warning';
                    continue;
                }
                $newName = uniqid('media_', true) . '.' . $ext;
                if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                    $mediaFiles[] = 'uploads/' . $newName;
                }
            }
        }

        $post = db_insert('posts.txt', [
            'teacher_id' => $user['id'],
            'taska_id'   => $taska_id,
            'child_id'   => $child_id,
            'category'   => $category,
            'content'    => $content,
            'media'      => $mediaFiles,
        ]);

        // Create notifications for parents of children in this taska
        $notifyChildren = $child_id
            ? [db_find('children.txt', 'id', $child_id)]
            : array_values(array_filter($allChildren, fn($c) =>
                ($taska_id ? $c['taska_id'] === $taska_id : in_array($c['taska_id'] ?? '', $taskaIds, true))
            ));

        $notifiedParents = [];
        foreach ($notifyChildren as $child) {
            if (empty($child['parent_id'])) continue;
            if (in_array($child['parent_id'], $notifiedParents, true)) continue;
            $notifiedParents[] = $child['parent_id'];
            db_insert('notifications.txt', [
                'user_id' => $child['parent_id'],
                'post_id' => $post['id'],
                'message' => 'New update from ' . $user['name'] . ' in ' .
                             (db_find('taskas.txt', 'id', $taska_id)['name'] ?? 'Taska') .
                             ' – ' . ucfirst($category),
                'is_read' => false,
            ]);
        }

        if (!$message) {
            $message = 'Post published successfully!';
        }

        // Redirect to avoid double-submit
        header('Location: ' . base_url('teacher/posts.php') . '?posted=1');
        exit;
    }
}

$pageTitle = 'New Post';
$activeNav = 'post';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="d-flex align-items-center gap-3 mb-3">
        <a href="<?= base_url('teacher/posts.php') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h4 class="section-heading mb-0"><i class="fa-solid fa-plus-circle me-2"></i>New Post</h4>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (!$myTaskas): ?>
    <div class="alert alert-warning">
        <i class="fa-solid fa-exclamation-triangle me-2"></i>
        You have not been assigned to any taska yet. Please contact the admin.
    </div>
    <?php else: ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" novalidate>

                        <!-- Category -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Category <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php
                                $categories = [
                                    'feed'    => ['fa-utensils',       'Feeding',  'cat-feed'],
                                    'rest'    => ['fa-moon',           'Rest/Sleep','cat-rest'],
                                    'nappy'   => ['fa-baby',           'Nappy',    'cat-nappy'],
                                    'hygiene' => ['fa-soap',           'Hygiene',  'cat-hygiene'],
                                    'health'  => ['fa-heart-pulse',    'Health',   'cat-health'],
                                    'other'   => ['fa-ellipsis',       'Other',    'cat-other'],
                                ];
                                $selCat = $_POST['category'] ?? 'other';
                                ?>
                                <?php foreach ($categories as $val => [$icon, $label, $cls]): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="category"
                                           id="cat_<?= $val ?>" value="<?= $val ?>"
                                           <?= $selCat === $val ? 'checked' : '' ?>
                                           onchange="updateCatBadge('<?= $val ?>', '<?= $cls ?>', '<?= $label ?>')">
                                    <label class="form-check-label post-category-badge <?= $cls ?>" for="cat_<?= $val ?>">
                                        <i class="fa-solid <?= $icon ?> me-1"></i><?= $label ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Taska -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Taska</label>
                            <select name="taska_id" id="taskaSelect" class="form-select" onchange="filterChildren(this.value)">
                                <option value="">— All my taskas —</option>
                                <?php foreach ($myTaskas as $t): ?>
                                <option value="<?= htmlspecialchars($t['id']) ?>"
                                    <?= (($_POST['taska_id'] ?? '') === $t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Child (optional) -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Child <span class="text-muted small fw-normal">(optional – leave blank for all children)</span>
                            </label>
                            <select name="child_id" id="childSelect" class="form-select">
                                <option value="">— All children in taska —</option>
                                <?php foreach ($myChildren as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"
                                    data-taska="<?= htmlspecialchars($c['taska_id'] ?? '') ?>"
                                    <?= (($_POST['child_id'] ?? '') === $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Update <span class="text-danger">*</span>
                            </label>
                            <textarea name="content" class="form-control" rows="4"
                                      placeholder="Describe the update…" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        </div>

                        <!-- Media upload -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Photos / Videos <span class="text-muted small fw-normal">(optional, max 50 MB each)</span>
                            </label>
                            <input type="file" name="media[]" id="mediaFiles" class="form-control"
                                   accept="image/*,video/*" multiple>
                            <div id="mediaPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa-solid fa-paper-plane me-2"></i>Publish Post
                            </button>
                            <a href="<?= base_url('teacher/posts.php') ?>" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Pass children data to JS for filtering
var childrenData = <?= json_encode(array_values($myChildren)) ?>;

function filterChildren(taskaId) {
    var $sel = document.getElementById('childSelect');
    for (var i = 1; i < $sel.options.length; i++) {
        var opt = $sel.options[i];
        var show = !taskaId || opt.dataset.taska === taskaId;
        opt.style.display = show ? '' : 'none';
        if (!show && opt.selected) {
            $sel.selectedIndex = 0;
        }
    }
}

function updateCatBadge(val, cls, label) {
    // just visual feedback handled by CSS
}

// Initial filter
document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('taskaSelect');
    if (sel) filterChildren(sel.value);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
