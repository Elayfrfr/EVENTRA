<?php /* One event card. Needs: $ev (a row from get_events) */ ?>
<div class="event-card">
  <div class="event-img" style="background: <?= e($ev['color']) ?>">
    <span class="chip-white"><?= e($ev['category']) ?></span>
  </div>
  <h3><?= e($ev['title']) ?></h3>
  <p class="meta"><?= e(fmt_datetime($ev['event_date'])) ?></p>
  <p class="meta"><?= e($ev['venue']) ?></p>
  <?php if ($ev['joined']): ?>
    <a class="btn btn-outline btn-block" href="tickets.php">Registered</a>
  <?php else: ?>
    <a class="btn btn-primary btn-block" href="register_event.php?id=<?= (int)$ev['id'] ?>">Register</a>
  <?php endif; ?>
</div>
