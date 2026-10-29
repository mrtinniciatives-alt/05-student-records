<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo '405 Method Not Allowed';
    exit;
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    flash('error', 'Invalid or expired security token.');
    redirect('/swd/05-student-records/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
try {
    if ($id <= 0) {
        flash('error', 'Student not found.');
    } else {
        $stmt = db()->prepare('DELETE FROM students WHERE id = :id');
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            flash('success', 'Student deleted successfully.');
        } else {
            flash('error', 'Student not found.');
        }
    }
} catch (Throwable $exception) {
    flash('error', 'The student could not be deleted because the database is unavailable.');
}

redirect('/swd/05-student-records/index.php');
