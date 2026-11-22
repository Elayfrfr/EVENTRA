</main>
<?php
$ok  = flash('success');
$err = flash('error');
if ($ok || $err): ?>
  <div class="toast <?= $err ? 'toast-err' : '' ?>" id="toast"><?= e($err ?: $ok) ?></div>
<?php endif; ?>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
