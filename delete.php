<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/swd/05-student-records/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
try {
    if ($id > 0) {
        $stmt = db()->prepare('DELETE FROM students WHERE id = ?');
        $stmt->execute([$id]);
        flash('success', 'Student deleted successfully.');
    } else {
        flash('error', 'Student not found.');
    }
} catch (Throwable $exception) {
    flash('error', 'The student could not be deleted because the database is unavailable.');
}

redirect('/swd/05-student-records/index.php');
