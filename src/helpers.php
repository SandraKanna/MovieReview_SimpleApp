<?php
/* ================================================================
   helper functions
   ================================================================ */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

function section(string $title, int $level = 2) { // switch for h1/h2/h3
	switch ($level) {
		case 1: echo "<h1>$title</h1>"; break;
		case 3: echo "<h3>$title</h3>"; break;
		default: echo "<h2>$title</h2>";
	}
}

function antiXss($s) {
	// htmlspecialchars takes special chars as "plain"/safe text
	return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


/** creates the connection to the DB (Postgres) and returns a PHP Data Object (PDO)*/
function pdo_pg(): PDO {
	$host = getenv('DB_HOST') ?: 'db';
	$port = (int)(getenv('DB_PORT') ?: 5432);
	$db   = getenv('DB_NAME') ?: 'appdb';
	$user = getenv('DB_USER') ?: 'app';
	$pass = getenv('DB_PASS') ?: 'secret';
	$dsn  = "pgsql:host=$host;port=$port;dbname=$db";
	return new PDO($dsn, $user, $pass, [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
}

/** Create table in DB if doesnt exist yet. It needs the PDO*/
function ensure_movies_table(PDO $pdo): void {
	$pdo->exec("
	CREATE TABLE IF NOT EXISTS movie_reviews (
		id           	SERIAL PRIMARY KEY,
		title        	TEXT NOT NULL,
		genre        	TEXT NOT NULL,
		rating       	INTEGER NOT NULL CHECK (rating BETWEEN 1 AND 5),
		review       	TEXT,
		watch_date   	DATE NOT NULL,
		image_path 	TEXT,
		created_at   	TIMESTAMPTZ NOT NULL DEFAULT NOW(),
		updated_at   	TIMESTAMPTZ NOT NULL DEFAULT NOW()
	)
	");
}

/** store a temp message (success or error) in the current session */
function flash(string $type, string $msg): void {
	$_SESSION['flashes'][] = ['type'=>$type,'msg'=>$msg];
}

/** get the message and deletes it */
function get_flashes(): array {
	$f = $_SESSION['flashes'] ?? [];
	unset($_SESSION['flashes']);
	return $f;
}

/** Utilities */
function str_clean(string $s): string {
	return trim($s);
}

function stars(int $n): string {
	$n = max(1, min(5, $n));
	return str_repeat('★', $n) . str_repeat('☆', 5-$n);
}


/** Optional img upload */
function upload_image(string $field = 'poster'): array {
	$errs = [];
	if (empty($_FILES[$field]['name'])) {
		return [null, $errs];
	}

	$f = $_FILES[$field];
	if ($f['error'] !== UPLOAD_ERR_OK) {
		if ($f['error'] !== UPLOAD_ERR_NO_FILE) {
			$errs[] = 'Upload error';
		}
		return [null, $errs];
	}
	if ($f['size'] > 2*1024*1024) {
		$errs[] = 'Image too large (max 2MB)';
		return [null, $errs];
	}

	$fi = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($fi, $f['tmp_name']); finfo_close($fi);
	$map = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
	if (!isset($map[$mime])) {
		$errs[] = 'Only JPG/PNG/WebP allowed';
		return [null, $errs];
	}

	$uploadDir  = __DIR__ . '/uploads';
	$publicBase = '/uploads';
	if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0775, true)) {
		$errs[]='Upload dir cannot be created';
		return [null,$errs];
	}
	if (!is_writable($uploadDir)) {
		$errs[]='Upload dir not writable';
		return [null,$errs];
	}

	$name = 'img_' . bin2hex(random_bytes(6)) . '.' . $map[$mime];
	$dest = $uploadDir . '/' . $name;
	if (!@move_uploaded_file($f['tmp_name'], $dest)) {
		$errs[]='Cannot move uploaded file';
		return [null,$errs];
	}

 	return [$publicBase.'/'.$name, $errs];
}