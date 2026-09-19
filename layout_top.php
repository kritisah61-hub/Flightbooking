<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$nav = [
    'index'      => ['icon' => '◈', 'label' => 'Dashboard'],
    'airlines'   => ['icon' => '✈', 'label' => 'Airlines'],
    'flights'    => ['icon' => '⊹', 'label' => 'Flights'],
    'passengers' => ['icon' => '◉', 'label' => 'Passengers'],
    'bookings'   => ['icon' => '▣', 'label' => 'Bookings & Payments'],
    'staff'      => ['icon' => '◈', 'label' => 'Staff'],
    'logs'       => ['icon' => '≡', 'label' => 'Logs'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SkyBase — <?= ucfirst($current) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">

<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">SkyBase</div>
    <div class="sub">Flight Operations DB</div>
  </div>
  <nav class="nav">
    <div class="nav-section">Main</div>
    <?php foreach ($nav as $page => $info): ?>
    <a href="<?= $page === 'index' ? 'index.php' : $page . '.php' ?>"
       class="<?= $current === $page ? 'active' : '' ?>">
      <span class="icon"><?= $info['icon'] ?></span>
      <?= $info['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    SkyBase v1.0 &nbsp;·&nbsp; MySQL
  </div>
</aside>

<div class="main">
