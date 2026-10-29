<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/StudentValidation.php';

$pageTitle = 'Seed students';

try {
    $pdo = db();

    if (($_GET['confirm'] ?? '') !== 'yes') {
        $seeded = null;
    } else {
        $courses = enumCourses($pdo);
        if (!$courses) {
            throw new RuntimeException('No course ENUM values are available.');
        }

        $firstNames = ['Chinedu', 'Adaeze', 'Oluwaseun', 'Fatima', 'Ibrahim', 'Ngozi', 'Tunde', 'Kemi', 'Emeka', 'Halima', 'Obinna', 'Zainab', 'Bola', 'Chiamaka', 'Yusuf', 'Nneka', 'Ayo', 'Aisha', 'Uche', 'Sade'];
        $lastNames = ['Okafor', 'Adeyemi', 'Bello', 'Balogun', 'Eze', 'Mohammed', 'Nwosu', 'Ibrahim', 'Adebayo', 'Okoro', 'Abubakar', 'Udo', 'Lawal', 'Onu', 'Yusuf', 'Bakare', 'Nwachukwu', 'Danladi', 'Garba', 'Obi'];

        $insert = $pdo->prepare(
            'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
             VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)' 
        );
        $check = $pdo->prepare('SELECT COUNT(*) FROM students WHERE email = :email');

        $pdo->beginTransaction();
        $inserted = 0;
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $email = strtolower($firstName . '.' . $lastName . '.seed' . $i . '@example.ng');

            $check->execute([':email' => $email]);
            if ((int) $check->fetchColumn() > 0) {
                continue;
            }

            $prefixes = ['070', '080', '081', '090', '091'];
            $phone = $prefixes[array_rand($prefixes)] . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
            $course = $courses[array_rand($courses)];
            $day = random_int(1, 28);
            $month = random_int(1, 12);
            $year = random_int(2019, 2026);
            $enrolledOn = sprintf('%04d-%02d-%02d', $year, $month, $day);
            if ($enrolledOn > date('Y-m-d')) {
                $enrolledOn = date('Y-m-d');
            }

            $insert->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':course' => $course,
                ':enrolled_on' => $enrolledOn,
            ]);
            $inserted++;
        }
        $pdo->commit();
        $seeded = $inserted;
        flash('success', $inserted . ' fake Nigerian students added.');
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $databaseError = true;
}

require __DIR__ . '/partials/header.php';
?>
<section class="card error-page">
    <?php if (!empty($databaseError)): ?>
        <h1>Database unavailable</h1>
        <p>Please make sure MySQL is running in XAMPP, then try again.</p>
    <?php elseif ($seeded !== null): ?>
        <h1>Seeding complete</h1>
        <p><?= $seeded ?> fake students were inserted.</p>
    <?php else: ?>
        <h1>Seed 50 student records?</h1>
        <p>This will add 50 realistic fake Nigerian students to the database.</p>
        <a class="btn" href="/swd/05-student-records/seed.php?confirm=yes">Confirm and insert</a>
    <?php endif; ?>
    <a class="btn btn-secondary" href="/swd/05-student-records/index.php">View students</a>
</section>
<?php require __DIR__ . '/partials/footer.php';
