<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';

$pageTitle = 'Students';

try {
    $students = db()->query('SELECT id, first_name, last_name, email, course, enrolled_on FROM students ORDER BY created_at DESC')->fetchAll();
} catch (Throwable $exception) {
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';

if (!empty($databaseError)):
?>
    <section class="card error-page">
        <h1>Database unavailable</h1>
        <p>We can't connect to the student database right now. Please make sure MySQL is running in XAMPP, then refresh this page.</p>
        <a class="btn" href="/swd/05-student-records/index.php">Try again</a>
    </section>
<?php else: ?>
    <section class="card">
        <div class="page-heading">
            <h1>Students</h1>
            <a class="btn" href="/swd/05-student-records/add.php">Add student</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Enrolled</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$students): ?>
                    <tr><td class="empty" colspan="5">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                            $date = DateTime::createFromFormat('Y-m-d', $student['enrolled_on']) ?: new DateTime($student['enrolled_on']);
                        ?>
                        <tr>
                            <td><?= e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['course']) ?></td>
                            <td><?= e($date->format('d M Y')) ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-secondary" href="/swd/05-student-records/edit.php?id=<?= (int) $student['id'] ?>">Edit</a>
                                    <form method="post" action="/swd/05-student-records/delete.php" onsubmit="return confirm('Delete this student?');">
                                        <input type="hidden" name="id" value="<?= (int) $student['id'] ?>">
                                        <button class="btn btn-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif;

require __DIR__ . '/partials/footer.php';
