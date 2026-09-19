<?php
require 'db.php';
require 'layout_top.php';

$stats = [
    'airlines'   => $pdo->query("SELECT COUNT(*) FROM airlines")->fetchColumn(),
    'flights'    => $pdo->query("SELECT COUNT(*) FROM flights")->fetchColumn(),
    'passengers' => $pdo->query("SELECT COUNT(*) FROM passengers")->fetchColumn(),
    'bookings'   => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
    'paid'       => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='paid'")->fetchColumn(),
    'pending'    => $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='Pending'")->fetchColumn(),
    'revenue'    => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='Completed'")->fetchColumn(),
    'staff'      => $pdo->query("SELECT COUNT(*) FROM staff")->fetchColumn(),
];

$recent_bookings = $pdo->query("
    SELECT b.id, p.first_name, p.last_name, f.flight_number,
           f.source_airport, f.destination_airport,
           b.booking_date, b.status, b.amount
    FROM bookings b
    JOIN passengers p ON p.id = b.passenger_id
    JOIN flights f ON f.id = b.flight_id
    ORDER BY b.created_at DESC LIMIT 8
")->fetchAll();

$recent_logs = $pdo->query("
    SELECT * FROM logs ORDER BY created_at DESC LIMIT 6
")->fetchAll();
?>

<div class="topbar">
  <div class="topbar-title">Dashboard</div>
  <div class="topbar-right">
    <span class="badge badge-amber">Admin</span>
    <span class="mono" style="color:var(--muted);font-size:11px"><?= date('D, d M Y') ?></span>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">✈</div>
      <div class="stat-val"><?= $stats['airlines'] ?></div>
      <div class="stat-label">Airlines</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⊹</div>
      <div class="stat-val"><?= $stats['flights'] ?></div>
      <div class="stat-label">Flights</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">◉</div>
      <div class="stat-val"><?= $stats['passengers'] ?></div>
      <div class="stat-label">Passengers</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">▣</div>
      <div class="stat-val"><?= $stats['bookings'] ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✓</div>
      <div class="stat-val"><?= $stats['paid'] ?></div>
      <div class="stat-label">Paid Bookings</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">◈</div>
      <div class="stat-val"><?= $stats['staff'] ?></div>
      <div class="stat-label">Staff Members</div>
    </div>
    <div class="stat-card" style="border-color:rgba(245,166,35,0.4)">
      <div class="stat-icon">₹</div>
      <div class="stat-val" style="font-size:26px">₹<?= number_format($stats['revenue']) ?></div>
      <div class="stat-label">Total Revenue</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;flex-wrap:wrap">

    <div class="card">
      <div class="card-title">◈ Recent Bookings</div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th><th>Passenger</th><th>Flight</th><th>Route</th><th>Date</th><th>Amount</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recent_bookings as $b): ?>
            <tr>
              <td class="mono"><?= $b['id'] ?></td>
              <td><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
              <td class="mono"><?= htmlspecialchars($b['flight_number']) ?></td>
              <td style="font-size:12px;color:var(--muted)"><?= $b['source_airport'] ?> → <?= $b['destination_airport'] ?></td>
              <td class="mono"><?= $b['booking_date'] ?></td>
              <td class="mono">₹<?= number_format($b['amount']) ?></td>
              <td>
                <?php
                  $sc = $b['status'] === 'paid' ? 'green' : ($b['status'] === 'Cancelled' ? 'red' : 'amber');
                ?>
                <span class="badge badge-<?= $sc ?>"><?= $b['status'] ?></span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-title">≡ Recent Activity</div>
      <?php foreach ($recent_logs as $log): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
          <span style="font-size:12px;color:var(--text)"><?= htmlspecialchars($log['description']) ?></span>
          <span class="badge badge-muted" style="font-size:9px"><?= $log['model'] ?></span>
        </div>
        <div class="mono" style="font-size:10px;color:var(--muted)"><?= $log['document_type'] ?> #<?= $log['document_id'] ?> &nbsp;·&nbsp; <?= date('d M, H:i', strtotime($log['created_at'])) ?></div>
      </div>
      <?php endforeach; ?>
      <div class="mt-4">
        <a href="logs.php" class="btn btn-ghost btn-sm">View All Logs →</a>
      </div>
    </div>

  </div>
</div>

<?php require 'layout_bottom.php'; ?>
