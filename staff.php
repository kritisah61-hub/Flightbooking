<?php
require 'db.php';
require 'layout_top.php';

$msg = $err = '';
$flights = $pdo->query("SELECT id, flight_number, source_airport, destination_airport FROM flights ORDER BY flight_number")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {
        try {
            $fid = !empty($_POST['flight_id']) ? $_POST['flight_id'] : null;
            $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, role, assigned_flight_id, employee_number, created_at, updated_at) VALUES (?,?,?,?,?,NOW(),NOW())");
            $stmt->execute([trim($_POST['first_name']), trim($_POST['last_name']), $_POST['role'], $fid, trim($_POST['emp_no'])]);
            $msg = 'Staff member added successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'update') {
        try {
            $fid = !empty($_POST['flight_id']) ? $_POST['flight_id'] : null;
            $stmt = $pdo->prepare("UPDATE staff SET first_name=?, last_name=?, role=?, assigned_flight_id=?, employee_number=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([trim($_POST['first_name']), trim($_POST['last_name']), $_POST['role'], $fid, trim($_POST['emp_no']), $_POST['id']]);
            $msg = 'Staff updated successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM staff WHERE id=?")->execute([$_POST['id']]);
            $msg = 'Staff member removed.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }
}

$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$sql = "SELECT s.*, f.flight_number, f.source_airport, f.destination_airport FROM staff s LEFT JOIN flights f ON f.id=s.assigned_flight_id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.employee_number LIKE ?)"; $params = array_fill(0,3,"%$search%"); }
if ($roleFilter) { $sql .= " AND s.role=?"; $params[] = $roleFilter; }
$sql .= " ORDER BY s.id";
$q = $pdo->prepare($sql); $q->execute($params);
$staff = $q->fetchAll();

$roles = ['Pilot','Co-Pilot','Flight Attendant','Maintenance','Ground Staff'];
?>

<div class="topbar">
  <div class="topbar-title">Staff</div>
  <div class="topbar-right">
    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ Add Staff</button>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>
  <?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error">✕ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">◈ Staff Directory</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search by name or emp no..." value="<?= htmlspecialchars($search) ?>">
        <select name="role">
          <option value="">All Roles</option>
          <?php foreach ($roles as $r): ?>
          <option value="<?= $r ?>" <?= $roleFilter===$r?'selected':'' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
        <?php if ($search||$roleFilter): ?><a href="staff.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Name</th><th>Employee #</th><th>Role</th><th>Assigned Flight</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($staff)): ?>
          <tr><td colspan="6"><div class="empty-state"><div class="icon">◈</div><p>No staff found.</p></div></td></tr>
          <?php else: foreach ($staff as $s): ?>
          <tr>
            <td class="mono"><?= $s['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></td>
            <td><span class="badge badge-muted"><?= htmlspecialchars($s['employee_number']) ?></span></td>
            <td>
              <?php $rc = $s['role']==='Pilot'?'amber':($s['role']==='Co-Pilot'?'blue':'muted'); ?>
              <span class="badge badge-<?= $rc ?>"><?= htmlspecialchars($s['role']) ?></span>
            </td>
            <td>
              <?php if ($s['flight_number']): ?>
                <span class="badge badge-green"><?= $s['flight_number'] ?></span>
                <span style="color:var(--muted);font-size:11px;margin-left:4px"><?= $s['source_airport'] ?> → <?= $s['destination_airport'] ?></span>
              <?php else: ?>
                <span style="color:var(--muted)">Unassigned</span>
              <?php endif; ?>
            </td>
            <td style="display:flex;gap:6px">
              <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">Edit</button>
              <form method="POST" onsubmit="return confirm('Remove this staff member?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
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
      <div class="modal-title">Add Staff</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="insert">
      <div class="form-grid">
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
        <div class="form-group"><label>Employee Number</label><input type="text" name="emp_no" required></div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <?php foreach ($roles as $r): ?><option><?= $r ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Assigned Flight (optional)</label>
          <select name="flight_id">
            <option value="">None</option>
            <?php foreach ($flights as $f): ?>
            <option value="<?= $f['id'] ?>"><?= $f['flight_number'] ?> (<?= $f['source_airport'] ?> → <?= $f['destination_airport'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Add Staff</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Staff</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="e_id">
      <div class="form-grid">
        <div class="form-group"><label>First Name</label><input type="text" name="first_name" id="e_fn" required></div>
        <div class="form-group"><label>Last Name</label><input type="text" name="last_name" id="e_ln" required></div>
        <div class="form-group"><label>Employee Number</label><input type="text" name="emp_no" id="e_emp" required></div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" id="e_role">
            <?php foreach ($roles as $r): ?><option><?= $r ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Assigned Flight</label>
          <select name="flight_id" id="e_fid">
            <option value="">None</option>
            <?php foreach ($flights as $f): ?>
            <option value="<?= $f['id'] ?>"><?= $f['flight_number'] ?> (<?= $f['source_airport'] ?> → <?= $f['destination_airport'] ?>)</option>
            <?php endforeach; ?>
          </select>
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
function openEdit(s) {
  document.getElementById('e_id').value   = s.id;
  document.getElementById('e_fn').value   = s.first_name;
  document.getElementById('e_ln').value   = s.last_name;
  document.getElementById('e_emp').value  = s.employee_number;
  document.getElementById('e_role').value = s.role;
  document.getElementById('e_fid').value  = s.assigned_flight_id || '';
  openModal('editModal');
}
</script>

<?php require 'layout_bottom.php'; ?>
