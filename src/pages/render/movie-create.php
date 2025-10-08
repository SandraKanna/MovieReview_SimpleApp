<?php
/* ================================================================
   create new review view
   ================================================================ */

section('New review', 2);
$pdo = pdo_pg(); ensure_movies_table($pdo);
$genres = ['Action','Drama','Comedy','Sci-Fi','Romance','Horror','Documentary','Animation','Other'];
$data = ['title'=>'','genre'=>'','rating'=>3,'review'=>'','watch_date'=>date('Y-m-d'),'image_path'=>null];
$errors = get_flashes();
?>
<div class="panel">
  <form method="post" class="form-panel" enctype="multipart/form-data" action="?p=movie-create">
    <label>Movie Title*</label>
    <input type="text" name="title" required value="<?= antiXss($data['title']) ?>">

    <label>Genre*</label>
    <select name="genre" required>
      <option value="">-- select --</option>
      <?php foreach ($genres as $g): ?>
        <option value="<?= antiXss($g) ?>"><?= antiXss($g) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Rating (1–5)*</label>
    <select name="rating" required>
      <?php for ($i=1;$i<=5;$i++): ?>
        <option value="<?= $i ?>" <?= $i===3?'selected':'' ?>><?= $i ?> <?= $i===1?'star':'stars' ?></option>
      <?php endfor; ?>
    </select>

    <label>Watch Date*</label>
    <input type="date" name="watch_date" required value="<?= antiXss($data['watch_date']) ?>">

    <label>Review</label>
    <textarea name="review" rows="4"></textarea>

    <label>Poster (JPG/PNG/WebP, max 2MB)</label>
    <input type="file" name="poster" accept="image/*">

    <div style="display:flex; gap:10px; margin-top:8px;">
      <button class="btn" type="submit">Create</button>
      <a class="btn btn-secondary" href="?p=movies">Cancel</a>
    </div>
  </form>
</div>