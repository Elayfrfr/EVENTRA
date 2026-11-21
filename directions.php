<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_role('student');
$id  = (int)($_GET['id'] ?? 0);
$reg = q_one("SELECT r.id, e.title, e.venue, e.address FROM registrations r JOIN events e ON e.id = r.event_id
              WHERE r.id = ? AND r.user_id = ? AND r.status = 'registered'", [$id, $user['id']]);
if (!$reg) { flash('error', 'Ticket not found.'); redirect('tickets.php'); }

$dest  = trim($reg['venue'] . ' ' . ($reg['address'] ?? ''));
$gmaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($dest);
$navUrl = fn($mode) => 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($dest) . '&travelmode=' . $mode;
/* Estimated figures (demo). Replace with a real routing API later if you want live distances. */
$modes = [
    'walk'  => ['Walk',  '12 min', '12 min · 850 m', 'Walking route',  'walking'],
    'ride'  => ['Ride',  '6 min',  '6 min · 1.9 km', 'Ride route',     'transit'],
    'drive' => ['Drive', '5 min',  '5 min · 2.3 km', 'Driving route',  'driving'],
];

$page_title = 'Directions'; $active = 'tickets';
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow" style="max-width:600px;margin-top:34px">
  <h2 style="font-size:22px">Directions</h2>
  <div class="mode-pills">
    <?php $first = true; foreach ($modes as $key => [$label, $mins, $meta, $title, $gm]): ?>
      <button type="button" class="pill <?= $first ? 'active' : '' ?>" data-mode="<?= $key ?>" data-meta="<?= e($meta) ?>" data-title="<?= e($title) ?>" data-nav="<?= e($navUrl($gm)) ?>"><?= e($label . ' · ' . $mins) ?></button>
    <?php $first = false; endforeach; ?>
  </div>

  <div class="map-mock">
    <svg viewBox="0 0 544 190" width="100%" height="100%" preserveAspectRatio="none" aria-hidden="true">
      <polyline points="48,132 148,80 228,110 348,42 486,82" fill="none" stroke="#9B2FB8" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"/>
      <circle cx="486" cy="82" r="9" fill="#E5484D"/>
    </svg>
  </div>

  <small style="color:var(--muted)">Destination</small>
  <h3 style="font-size:18px;margin:2px 0"><?= e($reg['venue']) ?></h3>
  <p class="sub" style="font-size:12.5px"><?= e($reg['address'] ?: 'Cebu City, Philippines') ?></p>
  <p class="route-meta" id="routeMeta"><?= e($modes['walk'][2]) ?></p>
  <p style="font-weight:700;font-size:12.5px;color:var(--muted);margin-bottom:6px" id="routeTitle"><?= e($modes['walk'][3]) ?></p>

  <div class="step"><i class="d"></i><span>Head toward the main entrance</span><span class="dist">100 m</span></div>
  <div class="step"><i class="d"></i><span>Turn right at the campus gate</span><span class="dist">450 m</span></div>
  <div class="step"><i class="d end"></i><span>Arrive at <?= e($reg['venue']) ?></span></div>

  <div class="two" style="margin-top:24px">
    <a class="btn btn-primary btn-lg" id="startNav" href="<?= e($navUrl('walking')) ?>" target="_blank" rel="noopener">Start Navigation</a>
    <a class="btn btn-outline btn-lg" href="<?= e($gmaps) ?>" target="_blank" rel="noopener">Open in Maps</a>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
