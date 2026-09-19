<?php
require 'db.php';
require 'layout_top.php';

$msg = $err = '';

// INSERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'insert') {
    try {
        $stmt = $pdo->prepare("INSERT INTO airlines (name, iata_code, country, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([strtoupper(trim($_POST['name'])), strtoupper(trim($_POST['iata_code'])), strtoupper(trim($_POST['country']))]);
        $msg = 'Airline added successfully.';
    } catch (Exception $e) { $err = $e->getMessage(); }
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    try {
        $stmt = $pdo->prepare("UPDATE airlines SET name=?, iata_code=?, country=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([strtoupper(trim($_POST['name'])), strtoupper(trim($_POST['iata_code'])), strtoupper(trim($_POST['country'])), $_POST['id']]);
        $msg = 'Airline updated successfully.';
    } catch (Exception $e) { $err = $e->getMessage(); }
}

// DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    try {
        $pdo->prepare("DELETE FROM airlines WHERE id=?")->execute([$_POST['id']]);
        $msg = 'Airline deleted.';
    } catch (Exception $e) { $err = 'Cannot delete: airline is linked to existing flights.'; }
}

$search = trim($_GET['q'] ?? '');
$query = $search
    ? $pdo->prepare("SELECT * FROM airlines WHERE name LIKE ? OR iata_code LIKE ? OR country LIKE ? ORDER BY id")
    : $pdo->prepare("SELECT * FROM airlines ORDER BY id");
if ($search) $query->execute(["%$search%", "%$search%", "%$search%"]);
else $query->execute();
$airlines = $query->fetchAll();
?>

<div class="topbar">
  <div class="topbar-title">Airlines</div>
  <div class="topbar-right">
    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ Add Airline</button>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>

  <?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error">✕ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">✈ Airlines Registry</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search airlines..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-ghost btn-sm" type="submit">Search</button>
        <?php if ($search): ?><a href="airlines.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>IATA Code</th><th>Country</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($airlines)): ?>
          <tr><td colspan="6"><div class="empty-state"><div class="icon">✈</div><p>No airlines found.</p></div></td></tr>
          <?php else: foreach ($airlines as $a): ?>
          <tr>
            <td class="mono"><?= $a['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($a['name']) ?></td>
            <td><span class="badge badge-amber"><?= htmlspecialchars($a['iata_code']) ?></span></td>
            <td><?= htmlspecialchars($a['country']) ?></td>
            <td class="mono" style="color:var(--muted);font-size:11px"><?= $a['created_at'] ?></td>
            <td style="display:flex;gap:6px">
              <button class="btn btn-ghost btn-sm"
                onclick="openEdit(<?= $a['id'] ?>, '<?= addslashes($a['name']) ?>', '<?= addslashes($a['iata_code']) ?>', '<?= addslashes($a['country']) ?>')">
                Edit
              </button>
              <form method="POST" onsubmit="return confirm('Delete this airline?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Add Airline</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="insert">
      <div class="form-grid">
        <div class="form-group">
          <label>Airline Name</label>
          <input type="text" name="name" placeholder="e.g. INDIGO" required>
        </div>
        <div class="form-group">
          <label>IATA Code</label>
          <input type="text" name="iata_code" placeholder="e.g. 6E" maxlength="4" required>
        </div>
        <div class="form-group">
          <label>Country</label>
          <input type="text" name="country" placeholder="e.g. INDIA" required>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Add Airline</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Airline</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-grid">
        <div class="form-group">
          <label>Airline Name</label>
          <input type="text" name="name" id="edit_name" required>
        </div>
        <div class="form-group">
          <label>IATA Code</label>
          <input type="text" name="iata_code" id="edit_iata" maxlength="4" required>
        </div>
        <div class="form-group">
          <label>Country</label>
          <input type="text" name="country" id="edit_country" required>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('editModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openEdit(id, name, iata, country) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_iata').value = iata;
  document.getElementById('edit_country').value = country;
  openModal('editModal');
}
</script>

<?php require 'layout_bottom.php'; ?>
