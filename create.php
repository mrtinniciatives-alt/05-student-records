<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$pageTitle = 'Add student';
$errors = [];
$student = [];

try {
    $pdo = db();
    $courses = enumCourses($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $errors['_form'] = 'Your session expired. Please refresh the page and try again.';
            [$student] = validateStudent($_POST, $courses, $pdo);
        } else {
            [$student, $errors] = validateStudent($_POST, $courses, $pdo);

            if (!$errors) {
                $stmt = $pdo->prepare(
                    'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                     VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
                );
                $stmt->execute([
                    ':first_name' => $student['first_name'],
                    ':last_name' => $student['last_name'],
                    ':email' => $student['email'],
                    ':phone' => $student['phone'],
                    ':course' => $student['course'],
                    ':enrolled_on' => $student['enrolled_on'],
                ]);

                flash('success', 'Student added');
                redirect('/swd/05-student-records/index.php');
            }
        }
    } else {
        $student = [
            'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
            'course' => '', 'enrolled_on' => date('Y-m-d'),
        ];
    }
} catch (Throwable $exception) {
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';
if (!empty($databaseError)): ?>
    <section class="card error-page">
        <h1>Database unavailable</h1>
        <p>Please make sure MySQL is running in XAMPP, then try again.</p>
        <a class="btn" href="/swd/05-student-records/create.php">Try again</a>
    </section>
<?php else: ?>
<section class="card">
    <div class="page-heading"><h1>Add student</h1></div>
    <?php if (isset($errors['_form'])): ?><div class="flash flash-error"><?= e($errors['_form']) ?></div><?php endif; ?>
    <?php $action = '/swd/05-student-records/create.php'; $submitLabel = 'Add student'; require __DIR__ . '/partials/student-form.php'; ?>
</section>
<?php endif; require __DIR__ . '/partials/footer.php';
