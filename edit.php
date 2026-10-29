<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$id = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Edit student';
$errors = [];

try {
    if ($id <= 0) {
        flash('error', 'Student not found.');
        redirect('/swd/05-student-records/index.php');
    }

    $pdo = db();
    $courses = enumCourses($pdo);
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $student = $stmt->fetch();

    if (!$student) {
        flash('error', 'Student not found.');
        redirect('/swd/05-student-records/index.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors['_form'] = 'Your session expired. Please refresh the page and try again.';
            [$student] = validateStudent($_POST, $courses, $pdo, $id);
        } else {
            [$student, $errors] = validateStudent($_POST, $courses, $pdo, $id);
            if (!$errors) {
                $update = $pdo->prepare('UPDATE students SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, course = :course, enrolled_on = :enrolled_on, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
                $update->execute([
                    ':first_name' => $student['first_name'], ':last_name' => $student['last_name'],
                    ':email' => $student['email'], ':phone' => $student['phone'], ':course' => $student['course'],
                    ':enrolled_on' => $student['enrolled_on'], ':id' => $id,
                ]);
                flash('success', 'Student updated successfully.');
                redirect('/swd/05-student-records/index.php');
            }
        }
    }
} catch (Throwable $exception) {
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';
if (!empty($databaseError)): ?>
    <section class="card error-page"><h1>Database unavailable</h1><p>Please make sure MySQL is running in XAMPP, then try again.</p></section>
<?php elseif (!empty($student)): ?>
<section class="card"><div class="page-heading"><h1>Edit student</h1></div>
<?php if (isset($errors['_form'])): ?><div class="flash flash-error"><?= e($errors['_form']) ?></div><?php endif; ?>
<?php $action = '/swd/05-student-records/edit.php?id=' . $id; $submitLabel = 'Update student'; require __DIR__ . '/partials/student-form.php'; ?>
</section>
<?php endif; require __DIR__ . '/partials/footer.php';
