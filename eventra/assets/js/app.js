/* Eventra — small vanilla JS helpers (no libraries, no AJAX) */
(function () {
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* ---------- modals ---------- */
  function openModal(id) { var m = document.getElementById(id); if (m) m.classList.add('open'); }
  function closeModal(m) { if (m) m.classList.remove('open'); }
  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-open]');
    if (o) { e.preventDefault(); $$('.modal.open').forEach(closeModal); openModal(o.getAttribute('data-open')); return; }
    var c = e.target.closest('[data-close]');
    if (c) { e.preventDefault(); closeModal(c.closest('.modal')); return; }
    if (e.target.classList && e.target.classList.contains('modal') && !e.target.hasAttribute('data-sticky')) closeModal(e.target);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { $$('.modal.open:not([data-sticky])').forEach(closeModal); var p = $('#notifPanel'); if (p) p.classList.remove('open'); }
  });

  /* "Ok, I Agree" in the Terms modal ticks the checkbox */
  $$('[data-agree]').forEach(function (b) {
    b.addEventListener('click', function () { var t = $('#terms'); if (t) t.checked = true; closeModal(b.closest('.modal')); });
  });

  /* validate a form first, then open a confirm modal */
  $$('[data-validate-open]').forEach(function (b) {
    b.addEventListener('click', function () {
      var f = document.getElementById(b.getAttribute('data-form'));
      if (f && !f.reportValidity()) return;
      openModal(b.getAttribute('data-validate-open'));
    });
  });
  /* submit another form from inside a modal */
  $$('[data-submit-form]').forEach(function (b) {
    b.addEventListener('click', function () { var f = document.getElementById(b.getAttribute('data-submit-form')); if (f) f.submit(); });
  });

  /* ---------- notification dropdown ---------- */
  var bell = $('#bellBtn'), panel = $('#notifPanel');
  if (bell && panel) {
    bell.addEventListener('click', function (e) { e.stopPropagation(); panel.classList.toggle('open'); });
    var x = $('#bellClose'); if (x) x.addEventListener('click', function () { panel.classList.remove('open'); });
    panel.addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('click', function () { panel.classList.remove('open'); });
    $$('[data-nfilter]', panel).forEach(function (b) {
      b.addEventListener('click', function () {
        $$('[data-nfilter]', panel).forEach(function (o) { o.classList.remove('active'); });
        b.classList.add('active');
        var only = b.getAttribute('data-nfilter') === 'unread';
        $$('.np-item', panel).forEach(function (it) { it.style.display = (only && it.getAttribute('data-unread') !== '1') ? 'none' : ''; });
      });
    });
  }

  /* ---------- FAQ accordion ---------- */
  $$('.faq button').forEach(function (b) {
    b.addEventListener('click', function () {
      var item = b.parentElement, was = item.classList.contains('open');
      $$('.faq').forEach(function (f) { f.classList.remove('open'); $('span', f.querySelector('button')).textContent = '+'; });
      if (!was) { item.classList.add('open'); $('span', b).textContent = '-'; }
    });
  });

  /* ---------- login: Student / Faculty toggle changes the heading ---------- */
  $$('input[name="role"][data-heading]').forEach(function (r) {
    r.addEventListener('change', function () {
      var h = $('#welcome'); if (h) h.textContent = r.getAttribute('data-heading');
    });
  });

  /* ---------- payment: enable Pay button after choosing a method ---------- */
  var payBtn = $('#payBtn');
  if (payBtn) {
    var sync = function () { payBtn.disabled = !$('input[name="method"]:checked'); };
    $$('input[name="method"]').forEach(function (r) { r.addEventListener('change', sync); });
    sync();
  }

  /* ---------- add to calendar ---------- */
  var calBtn = $('#calBtn');
  if (calBtn) {
    var calSync = function () { calBtn.disabled = !$('input[name="cal"]:checked'); };
    $$('input[name="cal"]').forEach(function (r) { r.addEventListener('change', calSync); });
    calSync();
    calBtn.addEventListener('click', function () {
      var sel = $('input[name="cal"]:checked'); if (!sel) return;
      var link = sel.getAttribute('data-url');
      if (sel.value === 'google' || sel.value === 'outlook') window.open(link, '_blank', 'noopener');
      else window.location.href = link;      // .ics download (Apple / any calendar)
      openModal('m-added');
    });
  }

  /* ---------- directions: switch travel mode ---------- */
  $$('[data-mode]').forEach(function (b) {
    b.addEventListener('click', function () {
      $$('[data-mode]').forEach(function (o) { o.classList.remove('active'); });
      b.classList.add('active');
      $('#routeMeta').textContent = b.getAttribute('data-meta');
      $('#routeTitle').textContent = b.getAttribute('data-title');
      var nav = $('#startNav'); if (nav) nav.href = b.getAttribute('data-nav');
    });
  });

  /* ---------- download a file, then show a "downloaded" modal ---------- */
  $$('[data-download]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      var f = document.createElement('iframe'); f.style.display = 'none'; f.src = b.getAttribute('data-download');
      document.body.appendChild(f);
      $$('.modal.open').forEach(closeModal);
      openModal(b.getAttribute('data-after'));
    });
  });

  /* ---------- copy link ---------- */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var text = $(b.getAttribute('data-copy')).textContent.trim();
      var done = function () { var m = b.closest('.modal-card'); $('.copy-btn', m).style.display = 'none'; $('.copied', m).style.display = 'block'; };
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, done);
      else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); done(); }
    });
  });

  /* ---------- resend countdown (forgot password) ---------- */
  var cd = $('#countdown');
  if (cd) {
    var left = 59, box = $('#resendBox'), ready = $('#resendReady');
    var t = setInterval(function () {
      left--; cd.textContent = left;
      if (left <= 0) { clearInterval(t); box.style.display = 'none'; ready.style.display = 'block'; }
    }, 1000);
  }

  /* ---------- confirm-password check on sign up / change password ---------- */
  $$('form[data-match]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      var a = f.querySelector('[name="password"],[name="new_password"]'), b = f.querySelector('[name="confirm"],[name="confirm_password"]');
      if (a && b && a.value !== b.value) { e.preventDefault(); alert('Passwords do not match.'); }
    });
  });

  /* ---------- splash auto-forward ---------- */
  var sp = $('[data-forward]');
  if (sp) setTimeout(function () { window.location.href = sp.getAttribute('data-forward'); }, 2200);

  /* ---------- scroll a flagged element into view, e.g. after server redirect ---------- */
  var first = $('.modal.open'); if (first) first.scrollTop = 0;
})();
