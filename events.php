<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_student($pdo);

$category = $_GET['category'] ?? '';
if (!in_array($category, CATEGORIES, true)) {
    $category = '';
}
$q = trim($_GET['q'] ?? '');

$events = get_events($pdo, $user['id'], $category, $q);

$pageTitle = 'Events';
$active    = 'events';
include __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Hello, <?= e($user['first_name']) ?></h1>
<p class="page-sub">Find your next campus event</p>

<div class="chips">
  <?php foreach (array_merge([''], CATEGORIES) as $c):
      $params = [];
      if ($c !== '') { $params['category'] = $c; }
      if ($q !== '') { $params['q'] = $q; }
      $href = 'events.php' . ($params ? '?' . http_build_query($params) : '');
  ?>
    <a class="pill <?= $c === $category ? 'active' : '' ?>" href="<?= e($href) ?>"><?= $c === '' ? 'All' : e($c) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($events): ?>
  <div class="grid grid-3">
    <?php foreach ($events as $ev) { include __DIR__ . '/includes/event_card.php'; } ?>
  </div>
<?php else: ?>
  <p class="empty">No upcoming events found.</p>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
