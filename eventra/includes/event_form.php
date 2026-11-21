<?php
/**
 * Shared bits for event-add.php and event-edit.php
 */
function event_form_values(array $src): array
{
    return [
        'title'       => trim($src['title'] ?? ''),
        'category'    => $src['category'] ?? 'Academic',
        'event_date'  => trim($src['event_date'] ?? ''),
        'event_time'  => trim($src['event_time'] ?? ''),
        'capacity'    => trim((string)($src['capacity'] ?? '')),
        'venue'       => trim($src['venue'] ?? ''),
        'price'       => trim((string)($src['price'] ?? '0')),
        'description' => trim($src['description'] ?? ''),
    ];
}

/** Returns an error message or null. */
function event_form_error(array $f, int $minCapacity = 1): ?string
{
    if ($f['title'] === '' || $f['venue'] === '' || $f['event_date'] === '' || $f['event_time'] === '') return 'Please fill in the title, date, time and venue.';
    if (!in_array($f['category'], CATEGORIES, true))                      return 'Please choose a category.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['event_date']) || !strtotime($f['event_date'])) return 'Please enter a valid date.';
    if (!preg_match('/^\d{2}:\d{2}$/', $f['event_time']))                 return 'Please enter a valid time.';
    if (!ctype_digit($f['capacity']) || (int)$f['capacity'] < max(1, $minCapacity)) return 'Capacity must be a number' . ($minCapacity > 1 ? " of at least $minCapacity (people already registered)." : '.');
    if (!is_numeric($f['price']) || (float)$f['price'] < 0)               return 'Price must be 0 (free) or more.';
    return null;
}

function event_form_fields(array $f, bool $isEdit = false, string $status = 'published'): void
{ ?>
  <label class="lbl" for="title">Event title</label>
  <input class="input" id="title" name="title" placeholder="e.g. Intramurals Opening Ceremony" value="<?= e($f['title']) ?>" required>
  <div class="two">
    <div><label class="lbl" for="category">Category</label>
      <select class="select" id="category" name="category"><?php foreach (CATEGORIES as $c): ?><option <?= $f['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div><label class="lbl" for="event_date">Date</label><input class="input" type="date" id="event_date" name="event_date" value="<?= e($f['event_date']) ?>" required></div>
  </div>
  <div class="two">
    <div><label class="lbl" for="event_time">Time</label><input class="input" type="time" id="event_time" name="event_time" value="<?= e($f['event_time']) ?>" required></div>
    <div><label class="lbl" for="capacity">Capacity</label><input class="input" type="number" min="1" id="capacity" name="capacity" placeholder="e.g. 200" value="<?= e($f['capacity']) ?>" required></div>
  </div>
  <label class="lbl" for="venue">Venue / Room</label>
  <input class="input" id="venue" name="venue" placeholder="e.g. Room 301" value="<?= e($f['venue']) ?>" required>
  <div class="two">
    <div><label class="lbl" for="price">Price (₱, 0 = Free)</label><input class="input" type="number" min="0" step="0.01" id="price" name="price" placeholder="0" value="<?= e($f['price']) ?>"></div>
    <?php if ($isEdit): ?>
      <div><label class="lbl" for="status">Status</label>
        <select class="select" id="status" name="status"><option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option><option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft (hidden)</option></select></div>
    <?php endif; ?>
  </div>
  <label class="lbl" for="description">Description</label>
  <textarea class="textarea" id="description" name="description" placeholder="Tell students what this event is about..."><?= e($f['description']) ?></textarea>
<?php }
