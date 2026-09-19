<?php
require 'db.php';
require 'layout_top.php';

$msg = $err = '';
$passengers = $pdo->query("SELECT id, first_name, last_name FROM passengers ORDER BY first_name")->fetchAll();
$flights    = $pdo->query("SELECT id, flight_number, source_airport, destination_airport, price FROM flights ORDER BY flight_number")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'book') {
        try {
            // Check seat availability
            $chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE flight_id=? AND seat_no=? AND status COLLATE utf8mb4_unicode_ci != 'Cancelled'");
            $chk->execute([$_POST['flight_id'], trim($_POST['seat_no'])]);
            if ($chk->fetchColumn() > 0) throw new Exception('Seat ' . $_POST['seat_no'] . ' is already taken on this flight.');

            $priceQ = $pdo->prepare("SELECT price FROM flights WHERE id=?");
            $priceQ->execute([$_POST['flight_id']]);
            $price  = $priceQ->fetchColumn();
            $amount = $price * $_POST['num_tickets'];

            $stmt = $pdo->prepare("INSERT INTO bookings (passenger_id, flight_id, booking_date, status, seat_no, num_tickets, amount, created_at, updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())");
            $stmt->execute([$_POST['passenger_id'], $_POST['flight_id'], $_POST['booking_date'], 'Pending', strtoupper(trim($_POST['seat_no'])), $_POST['num_tickets'], $amount]);
            $msg = "Booking created successfully. Amount: ₹" . number_format($amount);
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'pay') {
        try {
            $bQ = $pdo->prepare("SELECT * FROM bookings WHERE id=?");
            $bQ->execute([$_POST['booking_id']]);
            $bk = $bQ->fetch();
            if (!$bk) throw new Exception('Booking not found.');
            if ($bk['status'] === 'paid') throw new Exception('Booking is already paid.');
            if ($bk['status'] === 'Cancelled') throw new Exception('Cannot pay for a cancelled booking.');

            $ref = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 12));
            $stmt = $pdo->prepare("INSERT INTO payments (booking_id, amount, payment_date, payment_method, status, payment_reference, created_at, updated_at) VALUES (?,?,NOW(),?,?,?,NOW(),NOW())");
            $stmt->execute([$bk['id'], $bk['amount'], $_POST['method'], 'Completed', $ref]);
            $pdo->prepare("UPDATE bookings SET status='paid', updated_at=NOW() WHERE id=?")->execute([$bk['id']]);
            $msg = "Payment of ₹" . number_format($bk['amount']) . " processed. Ref: $ref";
        } catch (Exception $e) { $err = $e->getMessage(); }
    }

    if ($action === 'cancel') {
        try {
            $bQ = $pdo->prepare("SELECT status FROM bookings WHERE id=?");
            $bQ->execute([$_POST['booking_id']]);
            $st = $bQ->fetchColumn();
            if ($st === 'paid') throw new Exception('Cannot cancel a paid booking.');
            $pdo->prepare("UPDATE bookings SET status='Cancelled', updated_at=NOW() WHERE id=?")->execute([$_POST['booking_id']]);
            $msg = 'Booking cancelled.';
        } catch (Exception $e) { $err = $e->getMessage(); }
    }
}

$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$sql = "SELECT b.*, p.first_name, p.last_name, f.flight_number, f.source_airport, f.destination_airport
        FROM bookings b
        JOIN passengers p ON p.id=b.passenger_id
        JOIN flights f ON f.id=b.flight_id
        WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (p.first_name COLLATE utf8mb4_unicode_ci LIKE ? 
                   OR p.last_name COLLATE utf8mb4_unicode_ci LIKE ? 
                   OR f.flight_number COLLATE utf8mb4_unicode_ci LIKE ?)"; $params = array_fill(0,3,"%$search%"); }
if ($statusFilter) { 
    $sql .= " AND b.status COLLATE utf8mb4_unicode_ci=?"; 
}
$sql .= " ORDER BY b.id DESC";
$q = $pdo->prepare($sql); $q->execute($params);
$bookings = $q->fetchAll();

// Payments
$payments = $pdo->query("SELECT py.*, b.seat_no, p.first_name, p.last_name, f.flight_number
    FROM payments py
    JOIN bookings b ON b.id=py.booking_id
    JOIN passengers p ON p.id=b.passenger_id
    JOIN flights f ON f.id=b.flight_id
    ORDER BY py.id DESC LIMIT 20")->fetchAll();
?>

<div class="topbar">
  <div class="topbar-title">Bookings & Payments</div>
  <div class="topbar-right">
    <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">+ New Booking</button>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>
  <?php if ($msg): ?><div class="alert alert-success">✓ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-error">✕ <?= htmlspecialchars($err) ?></div><?php endif; ?>

  <!-- Bookings Table -->
  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">▣ Bookings</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search passenger, flight..." value="<?= htmlspecialchars($search) ?>">
        <select name="status">
          <option value="">All Statuses</option>
          <?php foreach (['Pending','paid','Cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
        <?php if ($search||$statusFilter): ?><a href="bookings.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Passenger</th><th>Flight</th><th>Route</th><th>Date</th><th>Seat</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
          <tr><td colspan="9"><div class="empty-state"><div class="icon">▣</div><p>No bookings found.</p></div></td></tr>
          <?php else: foreach ($bookings as $b): ?>
          <tr>
            <td class="mono"><?= $b['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></td>
            <td><span class="badge badge-blue"><?= htmlspecialchars($b['flight_number']) ?></span></td>
            <td style="font-size:12px;color:var(--muted)"><?= $b['source_airport'] ?> → <?= $b['destination_airport'] ?></td>
            <td class="mono"><?= $b['booking_date'] ?></td>
            <td class="mono"><?= htmlspecialchars($b['seat_no'] ?? '—') ?></td>
            <td class="mono">₹<?= number_format($b['amount']) ?></td>
            <td>
              <?php $sc = $b['status']==='paid'?'green':($b['status']==='Cancelled'?'red':'amber'); ?>
              <span class="badge badge-<?= $sc ?>"><?= $b['status'] ?></span>
            </td>
            <td style="display:flex;gap:5px;flex-wrap:wrap">
              <?php if ($b['status'] === 'Pending'): ?>
              <button class="btn btn-ghost btn-sm" onclick="openPay(<?= $b['id'] ?>)">Pay</button>
              <form method="POST" onsubmit="return confirm('Cancel this booking?')">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Cancel</button>
              </form>
              <?php else: ?>
              <span style="color:var(--muted);font-size:11px">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Payments Table -->
  <div class="card">
    <div class="card-title">₹ Recent Payments</div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Pay ID</th><th>Booking</th><th>Passenger</th><th>Flight</th><th>Amount</th><th>Method</th><th>Reference</th><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $py): ?>
          <tr>
            <td class="mono"><?= $py['id'] ?></td>
            <td class="mono">#<?= $py['booking_id'] ?></td>
            <td><?= htmlspecialchars($py['first_name'].' '.$py['last_name']) ?></td>
            <td><span class="badge badge-blue"><?= $py['flight_number'] ?></span></td>
            <td class="mono">₹<?= number_format($py['amount']) ?></td>
            <td><?= htmlspecialchars($py['payment_method']) ?></td>
            <td class="mono" style="font-size:11px;color:var(--amber)"><?= $py['payment_reference'] ?></td>
            <td class="mono" style="font-size:11px"><?= $py['payment_date'] ?></td>
            <td><span class="badge badge-green"><?= $py['status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- New Booking Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">New Booking</div>
      <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="book">
      <div class="form-grid">
        <div class="form-group">
          <label>Passenger</label>
          <select name="passenger_id" required>
            <option value="">Select Passenger</option>
            <?php foreach ($passengers as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Flight</label>
          <select name="flight_id" required>
            <option value="">Select Flight</option>
            <?php foreach ($flights as $f): ?>
            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['flight_number']) ?> — <?= $f['source_airport'] ?> → <?= $f['destination_airport'] ?> (₹<?= number_format($f['price']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Seat Number</label>
          <select name="seat_no" id="seatDropdown" required>
  <option value="">Select Seat</option>
</select>
        </div>
        <div class="form-group">
          <label>No. of Tickets</label>
          <input type="number" name="num_tickets" value="1" min="1" required>
        </div>
        <div class="form-group">
          <label>Booking Date</label>
          <input type="date" name="booking_date" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Create Booking</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Process Payment</div>
      <button class="modal-close" onclick="closeModal('payModal')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="pay">
      <input type="hidden" name="booking_id" id="pay_bid">
      <div class="form-grid">
        <div class="form-group">
          <label>Payment Method</label>
          <select name="method" required>
            <option>Online</option>
            <option>Cash</option>
            <option>Card</option>
            <option>UPI</option>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit">Confirm Payment</button>
        <button class="btn btn-ghost" type="button" onclick="closeModal('payModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function openPay(bid) {
  document.getElementById('pay_bid').value = bid;
  openModal('payModal');
}
</script>

<script>
const allSeats = [
  "1A","1B","1C","1D",
  "2A","2B","2C","2D",
  "3A","3B","3C","3D",
  "4A","4B","4C","4D",
  "5A","5B","5C","5D"
];

document.querySelector("select[name='flight_id']").addEventListener("change", function () {
    let flightId = this.value;
    let dropdown = document.getElementById("seatDropdown");

    dropdown.innerHTML = '<option value="">Loading...</option>';

    if (!flightId) return;

    fetch("get_seats.php?flight_id=" + flightId)
    .then(res => res.json())
    .then(bookedSeats => {
        dropdown.innerHTML = '<option value="">Select Seat</option>';

        let availableSeats = allSeats.filter(seat => !bookedSeats.includes(seat));

        if (availableSeats.length === 0) {
            dropdown.innerHTML = '<option value="">No seats available</option>';
            return;
        }

        availableSeats.forEach(seat => {
            let option = document.createElement("option");
            option.value = seat;
            option.textContent = seat;
            dropdown.appendChild(option);
        });
    })
    .catch(() => {
        dropdown.innerHTML = '<option value="">Error loading seats</option>';
    });
});
</script>

<?php require 'layout_bottom.php'; ?>
