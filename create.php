<?php

require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/student-validation.php';

$pageTitle = 'Add student';
$student = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'enrolled_on' => date('Y-m-d'),
];
$errors = [];
$databaseError = false;

try {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $student = studentFormValues($_POST);

        if (!hasValidCsrfToken($_POST['_token'] ?? null)) {
            http_response_code(403);
            $errors['_form'] = 'Your form session expired. Please review the form and submit it again.';
        } else {
            $errors = validateStudent($student, $pdo);

            if ($errors === []) {
                try {
                    $statement = $pdo->prepare(
                        'INSERT INTO students (first_name, last_name, email, phone, course, enrolled_on)
                         VALUES (:first_name, :last_name, :email, :phone, :course, :enrolled_on)'
                    );
                    $statement->execute([
                        'first_name' => $student['first_name'],
                        'last_name' => $student['last_name'],
                        'email' => $student['email'],
                        'phone' => $student['phone'],
                        'course' => $student['course'],
                        'enrolled_on' => $student['enrolled_on'],
                    ]);
                } catch (PDOException $exception) {
                    if ((string) $exception->getCode() === '23000'
                        && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
                        $errors['email'] = 'This email address is already in use.';
                    } else {
                        throw $exception;
                    }
                }

                if ($errors === []) {
                    flash('success', 'Student added');
                    redirect('index.php');
                }
            }
        }
    }
} catch (PDOException $exception) {
    http_response_code(503);
    $databaseError = true;
}

$formAction = 'create.php';
$submitLabel = 'Add student';
require __DIR__ . '/partials/header.php';
?>

<?php if ($databaseError): ?>
    <section class="error-panel" role="alert">
        <h1>Student records are unavailable</h1>
        <p>We couldn't connect to the student records database. Please check that MySQL is running, then try again.</p>
    </section>
<?php else: ?>
    <section class="page-heading">
        <div>
            <h1>Add student</h1>
            <p class="page-description">Enter the student's details below.</p>
        </div>
    </section>
    <?php require __DIR__ . '/partials/student-form.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
