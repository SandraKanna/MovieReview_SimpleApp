<?php
/* ================================================================
	 Process request: Create new review
	 ================================================================ */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
		http_response_code(405);
		exit('Method Not Allowed');
}

$pdo = pdo_pg(); ensure_movies_table($pdo);

// fields
$title      = str_clean($_POST['title'] ?? '');
$genre      = $_POST['genre'] ?? '';
$rating     = (int)($_POST['rating'] ?? 3);
$review     = trim($_POST['review'] ?? '');
$watch_date = $_POST['watch_date'] ?? date('Y-m-d');

// basic Parsing
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

if ($errors) { 
	flash('error', implode(' · ', $errors)); 
	header('Location: ?p=movie-new'); 
	exit; 
}

$stmt = $pdo->prepare("
	INSERT INTO movie_reviews (title, genre, rating, review, watch_date, image_path)
	VALUES (:t, :g, :r, :rv, :d, :p)
");
$stmt->execute([
	':t'=>$title, ':g'=>$genre, ':r'=>$rating, ':rv'=>$review,
	':d'=>$watch_date, ':p'=>$imagePath
]);

flash('success','Review created ✔');
header('Location: ?p=movies');
exit;