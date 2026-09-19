<?php
require 'db.php';
require 'layout_top.php';

$msg = $err = '';
$airlines = $pdo->query("SELECT id, name, iata_code FROM airlines ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'insert') {
        try {
            $stmt = $pdo->prepare("INSERT INTO flights (flight_number, airline_id, source_airport, destination_airport, price, scheduled_departure, scheduled_arrival, status, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())");
            $stmt->execute([$_POST['flight_number'], $_POST['airline_id'], strtoupper(trim($_POST['source'])), strtoupper(trim($_POST['dest'])), $_POST['price'], $_POST['dep'], $_POST['arr'], $_POST['status']]);
            $msg = 'Flight added successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'update') {
        try {
            $stmt = $pdo->prepare("UPDATE flights SET flight_number=?, airline_id=?, source_airport=?, destination_airport=?, price=?, scheduled_departure=?, scheduled_arrival=?, status=?, updated_at=NOW() WHERE id=?");
            $stmt->execute([$_POST['flight_number'], $_POST['airline_id'], strtoupper(trim($_POST['source'])), strtoupper(trim($_POST['dest'])), $_POST['price'], $_POST['dep'], $_POST['arr'], $_POST['status'], $_POST['id']]);
            $msg = 'Flight updated successfully.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'delete') {
        try {
            $pdo->prepare("DELETE FROM flights WHERE id=?")->execute([$_POST['id']]);
            $msg = 'Flight deleted.';
        } catch (Exception $e) { $err = 'Cannot delete: flight has existing bookings or staff assigned.'; }
    }
}

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT f.*, a.name as airline_name, a.iata_code FROM flights f JOIN airlines a ON a.id=f.airline_id WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (f.flight_number LIKE ? OR f.source_airport LIKE ? OR f.destination_airport LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($statusFilter) { $sql .= " AND f.status=?"; $params[] = $statusFilter; }
$sql .= " ORDER BY f.id";
$q = $pdo->prepare($sql); $q->execute($params);
$flights = $q->fetchAll();
?>

<div class="topbar">
  <div class="topbar-title">Flights</div>
  <div class="topbar-right">
    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ Add Flight</button>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>
  <?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error">✕ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">⊹ Flights Schedule</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search flights..." value="<?= htmlspecialchars($search) ?>">
        <select name="status">
          <option value="">All Statuses</option>
          <?php foreach (['Scheduled','On Time','Delayed','Cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
        <?php if ($search || $statusFilter): ?><a href="flights.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Flight #</th><th>Airline</th><th>Route</th><th>Departure</th><th>Arrival</th><th>Price</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($flights)): ?>
          <tr><td colspan="9"><div class="empty-state"><div class="icon">⊹</div><p>No flights found.</p></div></td></tr>
          <?php else: foreach ($flights as $f): ?>
          <tr>
            <td class="mono"><?= $f['id'] ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($f['flight_number']) ?></span></td>
            <td><?= htmlspecialchars($f['airline_name']) ?> <span style="color:var(--muted);font-size:11px">(<?= $f['iata_code'] ?>)</span></td>
            <td style="font-weight:500"><?= $f['source_airport'] ?> <span style="color:var(--amber)">→</span> <?= $f['destination_airport'] ?></td>
            <td class="mono" style="font-size:11px"><?= $f['scheduled_departure'] ?></td>
            <td class="mono" style="font-size:11px"><?= $f['scheduled_arrival'] ?></td>
            <td class="mono">₹<?= number_format($f['price']) ?></td>
            <td>
              <?php $sc = $f['status']==='On Time'?'green':($f['status']==='Delayed'?'amber':($f['status']==='Cancelled'?'red':'muted')); ?>
              <span class="badge badge-<?= $sc ?>"><?= $f['status'] ?></span>
            </td>
            <td style="display:flex;gap:6px">
              <button class="btn btn-ghost btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($f)) ?>)">Edit</button>
              <form method="POST" onsubmit="return confirm('Delete this flight?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $f['id'] ?>">
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
      <div class="modal-title">Add Flight</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="insert">
      <div class="form-grid">
        <div class="form-group">
          <label>Flight Number</label>
          <input type="text" name="flight_number" required>
        </div>
        <div class="form-group">
          <label>Airline</label>
          <select name="airline_id" required>
            <option value="">Select Airline</option>
            <?php foreach ($airlines as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['name'] ?> (<?= $a['iata_code'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Source Airport</label>
          <input type="text" name="source" required>
        </div>
        <div class="form-group">
          <label>Destination Airport</label>
          <input type="text" name="dest" required>
        </div>
        <div class="form-group">
          <label>Price (₹)</label>
          <input type="number" name="price" step="0.01" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status">
            <?php foreach (['Scheduled','On Time','Delayed','Cancelled'] as $s): ?>
            <option><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Scheduled Departure</label>
          <input type="datetime-local" name="dep" required>
        </div>
        <div class="form-group">
          <label>Scheduled Arrival</label>
          <input type="datetime-local" name="arr" required>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Add Flight</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Edit Flight</div>
      <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="e_id">
      <div class="form-grid">
        <div class="form-group">
          <label>Flight Number</label>
          <input type="text" name="flight_number" id="e_fn" required>
        </div>
        <div class="form-group">
          <label>Airline</label>
          <select name="airline_id" id="e_aid" required>
            <?php foreach ($airlines as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['name'] ?> (<?= $a['iata_code'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Source Airport</label>
          <input type="text" name="source" id="e_src" required>
        </div>
        <div class="form-group">
          <label>Destination Airport</label>
          <input type="text" name="dest" id="e_dst" required>
        </div>
        <div class="form-group">
          <label>Price (₹)</label>
          <input type="number" name="price" id="e_price" step="0.01" required>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select name="status" id="e_status">
            <?php foreach (['Scheduled','On Time','Delayed','Cancelled'] as $s): ?>
            <option><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Scheduled Departure</label>
          <input type="datetime-local" name="dep" id="e_dep" required>
        </div>
        <div class="form-group">
          <label>Scheduled Arrival</label>
          <input type="datetime-local" name="arr" id="e_arr" required>
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
function openEdit(f) {
  document.getElementById('e_id').value = f.id;
  document.getElementById('e_fn').value = f.flight_number;
  document.getElementById('e_aid').value = f.airline_id;
  document.getElementById('e_src').value = f.source_airport;
  document.getElementById('e_dst').value = f.destination_airport;
  document.getElementById('e_price').value = f.price;
  document.getElementById('e_status').value = f.status;
  document.getElementById('e_dep').value = f.scheduled_departure.replace(' ','T');
  document.getElementById('e_arr').value = f.scheduled_arrival.replace(' ','T');
  openModal('editModal');
}
</script>

<?php require 'layout_bottom.php'; ?>
