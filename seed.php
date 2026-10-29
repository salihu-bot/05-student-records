<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/student-validation.php';

$pageTitle = 'Seed test students';
$inserted = false;
$databaseError = false;

if (($_GET['confirm'] ?? null) === 'yes') {
    $firstNames = [
        'Adaeze', 'Adebayo', 'Aisha', 'Amaka', 'Babatunde',
        'Chiamaka', 'Chinedu', 'Damilola', 'Emeka', 'Fatima',
        'Ifeanyi', 'Ijeoma', 'Kelechi', 'Musa', 'Ngozi',
        'Oluwaseun', 'Oluwatobi', 'Somto', 'Tunde', 'Yusuf',
    ];
    $lastNames = [
        'Adebayo', 'Adeyemi', 'Ajayi', 'Balogun', 'Bello',
        'Eze', 'Ibrahim', 'Nwosu', 'Obi', 'Okafor',
        'Okonkwo', 'Olawale', 'Osei', 'Suleiman', 'Uche',
    ];

    try {
        $pdo = db();
        $insert = $pdo->prepare(
            'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
             VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
        );
        $pdo->beginTransaction();

        for ($number = 1; $number <= 50; $number++) {
            $phonePrefix = ['070', '080', '081', '090', '091'][random_int(0, 4)];
            $insert->execute([
                'first_name' => $firstNames[($number - 1) % count($firstNames)],
                'last_name' => $lastNames[($number * 7) % count($lastNames)],
                'email' => sprintf('student-%s-%02d@example.com', bin2hex(random_bytes(6)), $number),
                'phone' => $phonePrefix . random_int(10000000, 99999999),
                'course' => STUDENT_COURSES[($number - 1) % count(STUDENT_COURSES)],
                'enrolled_on' => (new DateTimeImmutable('-' . random_int(0, 1500) . ' days'))->format('Y-m-d'),
            ]);
        }

        $pdo->commit();
        $inserted = true;
    } catch (PDOException $exception) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(503);
        $databaseError = true;
    }
}

require __DIR__ . '/partials/header.php';
?>

<?php if ($databaseError): ?>
    <section class="error-panel" role="alert">
        <h1>Test students could not be added</h1>
        <p>We couldn't write to the student records database. Please check that MySQL is running, then try again.</p>
    </section>
<?php elseif ($inserted): ?>
    <section class="notice notice-success" role="status">50 test students were added successfully.</section>
    <p><a class="button" href="index.php">View students</a></p>
<?php else: ?>
    <section class="error-panel">
        <h1>Add test students</h1>
        <p>This will insert 50 fictional student records into the database.</p>
        <p><a class="button" href="seed.php?confirm=yes">Confirm and add 50 students</a></p>
        <p><a href="index.php">Cancel</a></p>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
