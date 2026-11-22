<?php require_once __DIR__ . '/terms_content.php'; ?>
<div class="modal" id="m-terms">
  <div class="modal-card wide">
    <button type="button" class="modal-x" data-close aria-label="Close">x</button>
    <h3 style="font-size:24px;margin-bottom:6px">Terms and Conditions</h3>
    <div style="max-height:58vh;overflow:auto;padding-right:6px"><?php render_terms_columns($TERMS); ?></div>
    <button type="button" class="btn btn-primary btn-lg" data-agree>Ok, I Agree</button>
  </div>
</div>
