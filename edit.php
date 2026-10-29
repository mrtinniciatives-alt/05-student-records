<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$id = (int) ($_GET['id'] ?? 0);
try {
    if ($id <= 0) {
        flash('error', 'Student not found.');
        redirect('/swd/05-student-records/index.php');
    }

    $column = db()->query("SHOW COLUMNS FROM students LIKE 'course'")->fetch();
    preg_match_all("/'([^']*)'/", $column['Type'] ?? '', $matches);
    $courses = $matches[1] ?? [];
    $stmt = db()->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        flash('error', 'Student not found.');
        redirect('/swd/05-student-records/index.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $enrolledOn = trim($_POST['enrolled_on'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '' || $course === '' || $enrolledOn === '') {
            flash('error', 'Please complete all required fields.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
        } else {
            $update = db()->prepare('UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ?, course = ?, enrolled_on = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $update->execute([$firstName, $lastName, $email, $phone, $course, $enrolledOn, $id]);
            flash('success', 'Student updated successfully.');
            redirect('/swd/05-student-records/index.php');
        }
    }
} catch (Throwable $exception) {
    $databaseError = true;
}

$pageTitle = 'Edit student';
require __DIR__ . '/partials/header.php';
if (!empty($databaseError)): ?>
    <section class="card error-page"><h1>Database unavailable</h1><p>Please make sure MySQL is running in XAMPP, then try again.</p></section>
<?php elseif (!empty($student)): ?>
<section class="card"><div class="page-heading"><h1>Edit student</h1></div>
<form method="post">
<div class="form-grid">
<div class="field"><label for="first_name">First name *</label><input id="first_name" name="first_name" required value="<?= e($_POST['first_name'] ?? $student['first_name']) ?>"></div>
<div class="field"><label for="last_name">Last name *</label><input id="last_name" name="last_name" required value="<?= e($_POST['last_name'] ?? $student['last_name']) ?>"></div>
<div class="field"><label for="email">Email *</label><input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? $student['email']) ?>"></div>
<div class="field"><label for="phone">Phone</label><input id="phone" name="phone" value="<?= e($_POST['phone'] ?? $student['phone']) ?>"></div>
<div class="field"><label for="course">Course *</label><select id="course" name="course" required><option value="">Select a course</option><?php foreach ($courses as $course): $selected = $_POST['course'] ?? $student['course']; ?><option value="<?= e($course) ?>" <?= ($selected === $course) ? 'selected' : '' ?>><?= e($course) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="enrolled_on">Enrolled date *</label><input id="enrolled_on" name="enrolled_on" type="date" required value="<?= e($_POST['enrolled_on'] ?? $student['enrolled_on']) ?>"></div>
</div><div class="form-actions"><button class="btn" type="submit">Update student</button><a class="btn btn-secondary" href="/swd/05-student-records/index.php">Cancel</a></div>
</form></section>
<?php endif; require __DIR__ . '/partials/footer.php';
