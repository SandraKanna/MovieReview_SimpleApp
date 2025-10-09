<?php
/*  ================================================================
    Movies: list, search, sort
    ================================================================ */

section('Movies', 2);

$pdo = pdo_pg();
ensure_movies_table($pdo);

// first read and normalize parameters of the list to be showed
$search      = trim($_GET['q'] ?? '');
$genreF = trim($_GET['genre'] ?? '');
$sort   = $_GET['sort'] ?? 'date_desc';

switch ($sort) {
  case 'date_asc':
    $orderSql='watch_date ASC, id ASC';
    break;
  case 'rating_desc':
    $orderSql='rating DESC, id DESC';
    break;
  case 'rating_asc':
    $orderSql='rating ASC, id ASC';
    break;
  default:
    $orderSql='watch_date DESC, id DESC';
}

$params=[]; // stores the real values that will replace the placeholders :q and :g
$where=[]; // stores SQL conditions (the filters selected by the user)

if ($search !==''){ // if the user submited a search query
  $where[]='title ILIKE :q'; // ILIKE operator used with wildcard % in a WHERE clause for case-insensitive search
  $params[':q']='%'.$search.'%'; //store the real value of the search query, use % to search for strings that contains the query
}
if ($genreF!==''){ // if a genre was selected in the search
  $where[]='genre = :g'; // prepare another condition
  $params[':g']=$genreF; // store the real value of the selected genre
}

$sql = "SELECT * FROM movie_reviews"; // build the Structure Query Language
if ($where){ //if there were any filters, apply them
  $sql .= " WHERE ".implode(' AND ',$where);
}
$sql .= " ORDER BY $orderSql";

$stmt=$pdo->prepare($sql); // access the DB
$stmt->execute($params); // execute the query
$rows=$stmt->fetchAll();
$genres = get_genres();

?>
<!-- HTML: show the table in the browser -->
<div class="panel">
  <form method="get" class="toolbar">
    <input type="hidden" name="p" value="movies">
    <div>
      <label>Search</label>
      <input type="text" name="q" value="<?= antiXss($search) ?>" placeholder="Title...">
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
  <table class="movies-table">
    <thead>
      <tr>
        <th>Poster</th>
        <th>Title</th>
        <th>Genre</th>
        <th>Rating</th>
        <th>Watch date</th>
        <th>Review</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td class="col-poster">
          <?php if ($r['image_path']): ?>
            <img class="poster-thumb" src="<?= antiXss($r['image_path']) ?>" alt="">
          <?php endif; ?>
        </td>
        <td><?= antiXss($r['title']) ?></td>
        <td><?= antiXss($r['genre']) ?></td>
        <td><?= stars((int)$r['rating']) ?></td>
        <td><?= antiXss($r['watch_date']) ?></td>
        <td class="review"><?= nl2br(antiXss($r['review'] ?? '')) ?></td>
        <td class="col-actions">
          <a class="btn btn-small" href="?p=movie-edit&id=<?= (int)$r['id'] ?>">Edit</a>
          <form method="post" action="?p=movie-delete" class="delete-form" data-confirm="Delete this review?">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit" class="btn btn-small">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>