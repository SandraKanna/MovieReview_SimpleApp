<?php

/* ================================================================
	 Process request: update existing review
	 ================================================================ */

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	http_response_code(405); exit('Method Not Allowed');
}

$pdo = pdo_pg(); ensure_movies_table($pdo);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$existing = null;

if ($id > 0) {
	$stmt = $pdo->prepare("SELECT * FROM movie_reviews WHERE id=:id");
	$stmt->execute([':id'=>$id]);
	$existing = $stmt->fetch();
	if (!$existing) {
		flash('error','Not found'); header('Location: ?p=movies');
		exit;
	}
}

/* basic validation of the form's fields */
list($clean, $valErrs) = validate_review($_POST);
$errors = $valErrs;

/* optional Upload */
list($imagePath, $upErrs) = upload_image('poster');
$errors = array_merge($errors, $upErrs);

if (!$imagePath && $existing) {
	$imagePath = $existing['image_path']; // conservar
}
$clean['image_path'] = $imagePath;

/* Errors display using current session (flash)*/
if ($errors) {
	flash('error', implode(' · ', $errors));
	header('Location: '.($id>0 ? '?p=movie-edit&id='.$id : '?p=movie-new'));
	exit;
}

/* Save */
$newId = save_review($pdo, $clean, $id>0 ? $id : null);

flash('success', $id>0 ? 'Review updated ✔' : 'Review created ✔');
header('Location: ?p=movies');
exit;