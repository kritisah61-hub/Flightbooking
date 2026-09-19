<?php
require 'db.php';
require 'layout_top.php';

$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {
        try {
            $hashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO passengers (first_name, last_name, email, phone, date_of_birth, passport_number, password, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())");
            $stmt->execute([trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['email']), trim($_POST['phone']), $_POST['dob'], trim($_POST['passport']), $hashed]);
            $msg = 'Passenger added successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'update') {
        try {
            $stmt = $pdo->prepare("UPDATE passengers SET first_name=?, last_name=?, email=?, phone=?, date_of_birth=?, passport_number=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([trim($_POST['first_name']), trim($_POST['last_name']), trim($_POST['email']), trim($_POST['phone']), $_POST['dob'], trim($_POST['passport']), $_POST['id']]);
            $msg = 'Passenger updated successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM passengers WHERE id=?")->execute([$_POST['id']]);
            $msg = 'Passenger deleted.';
        } catch (Exception $e) { $err = 'Cannot delete: passenger has existing bookings.'; }
    }
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM passengers WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR passport_number LIKE ?)"; $params = array_fill(0, 4, "%$search%"); }
$sql .= " ORDER BY id";
$q = $pdo->prepare($sql); $q->execute($params);
$passengers = $q->fetchAll();
?>

<div class="topbar">
  <div class="topbar-title">Passengers</div>
  <div class="topbar-right">
    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ Add Passenger</button>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>
  <?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error">✕ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">◉ Passenger Registry</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search by name, email, passport..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-ghost btn-sm" type="submit">Search</button>
        <?php if ($search): ?><a href="passengers.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Passport</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($passengers)): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="icon">◉</div><p>No passengers found.</p></div></td></tr>
          <?php else: foreach ($passengers as $p): ?>
          <tr>
            <td class="mono"><?= $p['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
            <td style="font-size:12px"><?= htmlspecialchars($p['email']) ?></td>
            <td class="mono"><?= htmlspecialchars($p['phone']) ?></td>
            <td class="mono"><?= $p['date_of_birth'] ?></td>
            <td><span class="badge badge-muted"><?= htmlspecialchars($p['passport_number']) ?></span></td>
            <td class="mono" style="color:var(--muted);font-size:11px"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
            <td style="display:flex;gap:6px">
              <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
              <form method="POST" onsubmit="return confirm('Delete this passenger?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
      <div class="modal-title">Add Passenger</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="insert">
      <div class="form-grid">
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" required></div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" required></div>
        <div class="form-group"><label>Passport Number</label><input type="text" name="passport" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Add Passenger</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Passenger</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="e_id">
      <div class="form-grid">
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" id="e_fn" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" id="e_ln" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" id="e_email" required></div>
        <div class="form-group"><label>Phone</label><input type="text" name="phone" id="e_phone" required></div>
        <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" id="e_dob" required></div>
        <div class="form-group"><label>Passport Number</label><input type="text" name="passport" id="e_pp" required></div>
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
function openEdit(p) {
  document.getElementById('e_id').value    = p.id;
  document.getElementById('e_fn').value    = p.first_name;
  document.getElementById('e_ln').value    = p.last_name;
  document.getElementById('e_email').value = p.email;
  document.getElementById('e_phone').value = p.phone;
  document.getElementById('e_dob').value   = p.date_of_birth;
  document.getElementById('e_pp').value    = p.passport_number;
  openModal('editModal');
}
</script>

<?php require 'layout_bottom.php'; ?>
