<?php
/* ================================================================
   edit existing review view
   ================================================================ */

section('Edit review', 2);
$pdo = pdo_pg(); ensure_movies_table($pdo);
$genres = ['Action','Drama','Comedy','Sci-Fi','Romance','Horror','Documentary','Animation','Other'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM movie_reviews WHERE id=:id");
$stmt->execute([':id'=>$id]);
$data = $stmt->fetch();
if (!$data) {
  flash('error','Not found');
  include __DIR__.'/movie-list.php';
  return;
}
?>
<div class="panel">
  <form method="post" class="form-panel" enctype="multipart/form-data" action="?p=movie-update&id=<?= (int)$id ?>">
    <label>Movie Title*</label>
    <input type="text" name="title" required value="<?= antiXss($data['title']) ?>">

    <label>Genre*</label>
    <select name="genre" required>
      <option value="">-- select --</option>
      <?php foreach ($genres as $g): ?>
        <option value="<?= antiXss($g) ?>" <?= $data['genre']===$g?'selected':'' ?>><?= antiXss($g) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Rating (1–5)*</label>
    <select name="rating" required>
      <?php for ($i=1;$i<=5;$i++): ?>
        <option value="<?= $i ?>" <?= (int)$data['rating']===$i?'selected':'' ?>>
          <?= $i ?> <?= $i===1?'star':'stars' ?>
        </option>
      <?php endfor; ?>
    </select>

    <label>Watch Date*</label>
    <input type="date" name="watch_date" required value="<?= antiXss($data['watch_date']) ?>">

    <label>Review</label>
    <textarea name="review" rows="4"><?= antiXss($data['review'] ?? '') ?></textarea>

    <label>Poster (JPG/PNG/WebP, max 2MB)</label>
    <input type="file" name="poster" accept="image/*">
    <?php if (!empty($data['image_path'])): ?>
      <div class="muted">Current: <code><?= antiXss($data['image_path']) ?></code></div>
    <?php endif; ?>

    <div style="display:flex; gap:10px; margin-top:8px;">
      <button type="submit" class="btn">Update</button>
      <a class="btn btn-secondary" href="?p=movies">Cancel</a>
    </div>
  </form>
</div>