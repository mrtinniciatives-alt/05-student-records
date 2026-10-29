<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$id = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Student details';

try {
    if ($id <= 0) {
        http_response_code(404);
        $notFound = true;
    } else {
        $stmt = db()->prepare('SELECT * FROM students WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $student = $stmt->fetch();

        if (!$student) {
            http_response_code(404);
            $notFound = true;
        }
    }
} catch (Throwable $exception) {
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';

if (!empty($databaseError)): ?>
    <section class="card error-page"><h1>Database unavailable</h1><p>Please make sure MySQL is running in XAMPP, then try again.</p></section>
<?php elseif (!empty($notFound)): ?>
    <section class="card error-page"><h1>404 — Student not found</h1><p>The requested student does not exist.</p><a class="btn" href="/swd/05-student-records/index.php">Back to students</a></section>
<?php else:
    $enrolled = DateTime::createFromFormat('!Y-m-d', $student['enrolled_on']) ?: new DateTime($student['enrolled_on']);
?>
<section class="card">
    <div class="page-heading"><h1><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1></div>
    <dl class="student-details">
        <div><dt>ID</dt><dd><?= (int) $student['id'] ?></dd></div>
        <div><dt>First name</dt><dd><?= e($student['first_name']) ?></dd></div>
        <div><dt>Last name</dt><dd><?= e($student['last_name']) ?></dd></div>
        <div><dt>Email</dt><dd><?= e($student['email']) ?></dd></div>
        <div><dt>Phone</dt><dd><?= e($student['phone'] !== '' ? $student['phone'] : 'Not provided') ?></dd></div>
        <div><dt>Course</dt><dd><?= e($student['course']) ?></dd></div>
        <div><dt>Enrolled on</dt><dd><?= e($enrolled->format('d M Y')) ?></dd></div>
        <div><dt>Created at</dt><dd><?= e($student['created_at']) ?></dd></div>
        <div><dt>Updated at</dt><dd><?= e($student['updated_at']) ?></dd></div>
    </dl>
    <div class="form-actions">
        <a class="btn btn-secondary" href="/swd/05-student-records/edit.php?id=<?= (int) $student['id'] ?>">Edit</a>
        <a class="btn btn-secondary" href="/swd/05-student-records/index.php">Back to students</a>
    </div>
</section>
<?php endif;
require __DIR__ . '/partials/footer.php';
