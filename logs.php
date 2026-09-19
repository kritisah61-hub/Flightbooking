<?php
require 'db.php';
require 'layout_top.php';

$search      = trim($_GET['q'] ?? '');
$typeFilter  = $_GET['type'] ?? '';
$modelFilter = $_GET['model'] ?? '';

$sql = "SELECT * FROM logs WHERE 1=1";
$params = [];
if ($search)      { $sql .= " AND (description LIKE ? OR link LIKE ?)"; $params = array_fill(0,2,"%$search%"); }
if ($typeFilter)  { $sql .= " AND document_type=?"; $params[] = $typeFilter; }
if ($modelFilter) { $sql .= " AND model=?"; $params[] = $modelFilter; }
$sql .= " ORDER BY id DESC";

$q = $pdo->prepare($sql); $q->execute($params);
$logs = $q->fetchAll();

$types  = $pdo->query("SELECT DISTINCT document_type FROM logs ORDER BY document_type")->fetchAll(PDO::FETCH_COLUMN);
$models = $pdo->query("SELECT DISTINCT model FROM logs ORDER BY model")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="topbar">
  <div class="topbar-title">Activity Logs</div>
  <div class="topbar-right">
    <span class="badge badge-muted"><?= count($logs) ?> entries</span>
  </div>
</div>

<div class="content">
  <div class="runway-line"></div>

  <div class="card">
    <div class="flex-between" style="margin-bottom:16px">
      <div class="card-title" style="margin-bottom:0">≡ Audit Trail</div>
      <form method="GET" class="search-bar" style="margin:0">
        <input type="text" name="q" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
        <select name="type">
          <option value="">All Types</option>
          <?php foreach ($types as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= $typeFilter===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="model">
          <option value="">All Methods</option>
          <?php foreach ($models as $m): ?>
          <option value="<?= htmlspecialchars($m) ?>" <?= $modelFilter===$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
        <?php if ($search||$typeFilter||$modelFilter): ?><a href="logs.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </form>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Description</th><th>Method</th><th>Model/Type</th><th>Doc ID</th><th>Time</th><th>URL</th></tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="icon">≡</div><p>No logs found.</p></div></td></tr>
          <?php else: foreach ($logs as $l): ?>
          <tr>
            <td class="mono"><?= $l['id'] ?></td>
            <td style="font-weight:500"><?= htmlspecialchars($l['description'] ?? '—') ?></td>
            <td>
              <?php
                $mc = $l['model']==='DELETE'?'red':($l['model']==='POST'||$l['model']==='INSERT'?'green':($l['model']==='UPDATE'?'amber':'muted'));
              ?>
              <span class="badge badge-<?= $mc ?>"><?= htmlspecialchars($l['model'] ?? '—') ?></span>
            </td>
            <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($l['document_type'] ?? '—') ?></td>
            <td class="mono"><?= $l['document_id'] ?? '—' ?></td>
            <td class="mono" style="font-size:11px;color:var(--muted);white-space:nowrap"><?= date('d M Y, H:i', strtotime($l['created_at'])) ?></td>
            <td style="font-size:11px;color:var(--muted);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <span title="<?= htmlspecialchars($l['link']) ?>"><?= htmlspecialchars($l['link']) ?></span>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require 'layout_bottom.php'; ?>
