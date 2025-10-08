<?php
/* ================================================================
	 Process request: delete review
	 ================================================================ */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	http_response_code(405);
	exit('Method Not Allowed');
}

$pdo = pdo_pg(); ensure_movies_table($pdo);

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
	flash('error','Invalid ID');
	header('Location: ?p=movies');
	exit;
}

$stmt = $pdo->prepare("DELETE FROM movie_reviews WHERE id=:id");
$stmt->execute([':id'=>$id]);

flash('success','Review deleted ✔');
header('Location: ?p=movies');
exit;