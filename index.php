<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$pageTitle = 'Students';
$q = trim((string) ($_GET['q'] ?? ''));
$courseFilter = trim((string) ($_GET['course'] ?? ''));
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

try {
    $pdo = db();
    $courses = enumCourses($pdo);
    if ($courseFilter !== '' && !in_array($courseFilter, $courses, true)) {
        $courseFilter = '';
    }

    $where = [];
    $params = [];
    if ($q !== '') {
        $where[] = '(first_name LIKE :q_first OR last_name LIKE :q_last OR email LIKE :q_email)';
        $searchValue = '%' . $q . '%';
        $params[':q_first'] = $searchValue;
        $params[':q_last'] = $searchValue;
        $params[':q_email'] = $searchValue;
    }
    if ($courseFilter !== '') {
        $where[] = 'course = :course';
        $params[':course'] = $courseFilter;
    }
    $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM students' . $whereSql);
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $listSql = 'SELECT id, first_name, last_name, email, course, enrolled_on
                FROM students' . $whereSql . '
                ORDER BY created_at DESC, id DESC
                LIMIT :limit OFFSET :offset';
    $listStmt = $pdo->prepare($listSql);
    foreach ($params as $name => $value) {
        $listStmt->bindValue($name, $value, PDO::PARAM_STR);
    }
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $students = $listStmt->fetchAll();
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
<?php else:
    $preservedParams = [];
    if ($q !== '') { $preservedParams['q'] = $q; }
    if ($courseFilter !== '') { $preservedParams['course'] = $courseFilter; }
    $start = $total === 0 ? 0 : $offset + 1;
    $end = min($offset + $perPage, $total);
?>
    <section class="card">
        <div class="page-heading">
            <h1>Students</h1>
            <a class="btn" href="/swd/05-student-records/create.php">Add student</a>
        </div>

        <form method="get" action="/swd/05-student-records/index.php" class="filters">
            <div class="filter-field">
                <label for="q">Search</label>
                <input id="q" type="search" name="q" value="<?= e($q) ?>" placeholder="Name or email">
            </div>
            <div class="filter-field">
                <label for="course">Course</label>
                <select id="course" name="course">
                    <option value="">All courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= e($course) ?>" <?= $courseFilter === $course ? 'selected' : '' ?>><?= e($course) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn" type="submit">Apply</button>
            <?php if ($q !== '' || $courseFilter !== ''): ?>
                <a class="btn btn-secondary" href="/swd/05-student-records/index.php">Clear</a>
            <?php endif; ?>
        </form>

        <p class="results-summary">Showing <?= $start ?>–<?= $end ?> of <?= $total ?> <?= $total === 1 ? 'student' : 'students' ?></p>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Course</th><th>Enrolled</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (!$students): ?>
                    <tr><td class="empty" colspan="5">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <?php $date = DateTime::createFromFormat('Y-m-d', $student['enrolled_on']) ?: new DateTime($student['enrolled_on']); ?>
                        <tr>
                            <td><?= e($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td><?= e($student['email']) ?></td>
                            <td><?= e($student['course']) ?></td>
                            <td><?= e($date->format('d M Y')) ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn btn-secondary" href="/swd/05-student-records/show.php?id=<?= (int) $student['id'] ?>">View</a>
                                    <a class="btn btn-secondary" href="/swd/05-student-records/edit.php?id=<?= (int) $student['id'] ?>">Edit</a>
                                    <form method="post" action="/swd/05-student-records/delete.php" onsubmit='return confirm(<?= json_encode('Delete ' . $student['first_name'] . ' ' . $student['last_name'] . '?', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);'>
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
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

        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Student pages">
            <?php
                $previousParams = $preservedParams + ['page' => max(1, $page - 1)];
                $nextParams = $preservedParams + ['page' => min($totalPages, $page + 1)];
            ?>
            <a class="btn btn-secondary <?= $page <= 1 ? 'disabled' : '' ?>" <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : 'href="/swd/05-student-records/index.php?' . e(http_build_query($previousParams)) . '"' ?>>Previous</a>
            <?php for ($number = 1; $number <= $totalPages; $number++): ?>
                <?php $linkParams = $preservedParams + ['page' => $number]; ?>
                <a class="page-link <?= $number === $page ? 'active' : '' ?>" href="/swd/05-student-records/index.php?<?= e(http_build_query($linkParams)) ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a>
            <?php endfor; ?>
            <a class="btn btn-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>" <?= $page >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : 'href="/swd/05-student-records/index.php?' . e(http_build_query($nextParams)) . '"' ?>>Next</a>
        </nav>
        <?php endif; ?>
    </section>
<?php endif;

require __DIR__ . '/partials/footer.php';
