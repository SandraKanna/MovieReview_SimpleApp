<?php
/*  ================================================================
	1) Movies
	----------------------------------------------------------------
	- list
	- search
	- organize
	================================================================ */

section('Movies', 2);

$pdo = pdo_pg();
ensure_movies_table($pdo);

$q      = trim($_GET['q'] ?? '');
$genreF = trim($_GET['genre'] ?? '');
$sort   = $_GET['sort'] ?? 'date_desc';

switch ($sort) {
  case 'date_asc':    $orderSql='watch_date ASC, id ASC'; break;
  case 'rating_desc': $orderSql='rating DESC, id DESC';   break;
  case 'rating_asc':  $orderSql='rating ASC, id ASC';     break;
  default:            $orderSql='watch_date DESC, id DESC';
}

$params=[]; $where=[];
if ($q!==''){ $where[]='LOWER(title) LIKE :q'; $params[':q']='%'.mb_strtolower($q,'UTF-8').'%'; }
if ($genreF!==''){ $where[]='genre = :g'; $params[':g']=$genreF; }

$sql = "SELECT * FROM movie_reviews";
if ($where) $sql .= " WHERE ".implode(' AND ',$where);
$sql .= " ORDER BY $orderSql";

$stmt=$pdo->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();

$genres=['Action','Drama','Comedy','Sci-Fi','Romance','Horror','Documentary','Animation','Other'];
?>

<div class="panel">
  <form method="get" class="toolbar">
    <input type="hidden" name="p" value="movies">
    <div>
      <label>Search</label>
      <input type="text" name="q" value="<?= antiXss($q) ?>" placeholder="Title...">
    </div>
    <div>
      <label>Genre</label>
      <select name="genre">
        <option value="">All</option>
        <?php foreach ($genres as $g): ?>
          <option value="<?= $g ?>" <?= $genreF===$g?'selected':'' ?>><?= $g ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Sort</label>
      <select name="sort">
        <option value="date_desc"   <?= $sort==='date_desc'?'selected':'' ?>>Newest</option>
        <option value="date_asc"    <?= $sort==='date_asc'?'selected':'' ?>>Oldest</option>
        <option value="rating_desc" <?= $sort==='rating_desc'?'selected':'' ?>>Rating ↑</option>
        <option value="rating_asc"  <?= $sort==='rating_asc'?'selected':'' ?>>Rating ↓</option>
      </select>
    </div>
    <button type="submit">Apply</button>
  </form>
</div>

<?php if (!$rows): ?>
  <p class="muted">No reviews yet.</p>
<?php else: ?>
  <table class="table-fixed">
    <colgroup>
      <col class="col-poster"><col class="col-title"><col class="col-genre">
      <col class="col-rating"><col class="col-date"><col><!-- review -->
      <col class="col-actions">
    </colgroup>
    <thead>
      <tr>
        <th>Poster</th><th>Title</th><th>Genre</th><th>Rating</th>
        <th>Watch date</th><th>Review</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <?php if ($r['image_path']): ?>
            <img class="poster-thumb" src="<?= antiXss($r['image_path']) ?>" alt="">
          <?php endif; ?>
        </td>
        <td><?= antiXss($r['title']) ?></td>
        <td><?= antiXss($r['genre']) ?></td>
        <td><?= stars((int)$r['rating']) ?></td>
        <td><?= antiXss($r['watch_date']) ?></td>
        <td class="review"><?= nl2br(antiXss($r['review'] ?? '')) ?></td>
        <td class="actions">
          <a class="btn" href="?p=movie-edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=movie-delete" class="inline-form" data-confirm="Delete this review?">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>