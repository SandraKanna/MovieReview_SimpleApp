<?php
/* ================================================================
	 Process request: update existing review
	 ================================================================ */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	http_response_code(405);
	exit('Method Not Allowed');
}

$pdo = pdo_pg(); ensure_movies_table($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
	flash('error','Invalid ID');
	header('Location: ?p=movies');
	exit;
}

$stmt = $pdo->prepare("SELECT * FROM movie_reviews WHERE id=:id");
$stmt->execute([':id'=>$id]);
$curr = $stmt->fetch();
if (!$curr) {
	flash('error','Not found');
	header('Location: ?p=movies');
	exit;
}

// existing fields
$title      = str_clean($_POST['title'] ?? '');
$genre      = $_POST['genre'] ?? '';
$rating     = (int)($_POST['rating'] ?? 3);
$review     = trim($_POST['review'] ?? '');
$watch_date = $_POST['watch_date'] ?? date('Y-m-d');

// basic parsing
$genres = ['Action','Drama','Comedy','Sci-Fi','Romance','Horror','Documentary','Animation','Other'];
$errors = [];
if ($title==='') {
	$errors[]='Title is required';
}
if (!in_array($genre,$genres,true)) {
	$errors[]='Invalid genre';
}
if ($rating<1 || $rating>5) {
	$errors[]='Rating must be 1..5';
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$watch_date)) {
	$errors[]='Invalid date';
}

// Upload is optional
[$imagePath, $upErrs] = upload_image('poster');
$errors = array_merge($errors, $upErrs);
if (!$imagePath) {
	$imagePath = $curr['image_path'];
}

if ($errors) { 
	flash('error', implode(' · ', $errors)); 
	header('Location: ?p=movie-edit&id='.$id); 
	exit; 
}

$stmt = $pdo->prepare("
	UPDATE movie_reviews
		 SET title=:t, genre=:g, rating=:r, review=:rv, watch_date=:d, image_path=:p, updated_at=NOW()
	 WHERE id=:id
");
$stmt->execute([
	':t'=>$title, ':g'=>$genre, ':r'=>$rating, ':rv'=>$review,
	':d'=>$watch_date, ':p'=>$imagePath, ':id'=>$id
]);

flash('success','Review updated ✔');
header('Location: ?p=movies');
exit;