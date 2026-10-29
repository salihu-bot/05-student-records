<?php

const STUDENT_COURSES = [
    'Web Development',
    'Software Engineering',
    'Data Analysis',
    'UI/UX Design',
];

function studentFormValues(array $input): array
{
    $values = [];

    foreach (['first_name', 'last_name', 'email', 'phone', 'course', 'enrolled_on'] as $field) {
        $values[$field] = isset($input[$field]) && is_string($input[$field])
            ? trim($input[$field])
            : '';
    }

    return $values;
}

function validateStudent(array $student, PDO $pdo, ?int $excludeId = null): array
{
    $errors = [];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        $value = $student[$field];
        if ($value === '') {
            $errors[$field] = $label . ' is required.';
        } elseif (preg_match('/\A[\p{L} -]{2,60}\z/u', $value) !== 1) {
            $errors[$field] = $label . ' must be 2-60 letters, spaces, or hyphens.';
        }
    }

    if ($student['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (filter_var($student['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address.';
    } else {
        $sql = 'SELECT id FROM students WHERE email = :email';
        $parameters = ['email' => $student['email']];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $excludeId;
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);

        if ($statement->fetchColumn() !== false) {
            $errors['email'] = 'This email address is already in use.';
        }
    }

    if ($student['phone'] !== '' && preg_match('/\A0[789][01]\d{8}\z/', $student['phone']) !== 1) {
        $errors['phone'] = 'Enter a valid phone number or leave this field blank.';
    }

    if (!in_array($student['course'], STUDENT_COURSES, true)) {
        $errors['course'] = 'Select a valid course.';
    }

    $enrolledOn = DateTimeImmutable::createFromFormat('!Y-m-d', $student['enrolled_on']);
    $dateErrors = DateTimeImmutable::getLastErrors();
    $validDate = $enrolledOn !== false
        && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
        && $enrolledOn->format('Y-m-d') === $student['enrolled_on'];

    if (!$validDate) {
        $errors['enrolled_on'] = 'Enter a valid enrollment date.';
    } elseif ($enrolledOn->format('Y-m-d') > (new DateTimeImmutable('today'))->format('Y-m-d')) {
        $errors['enrolled_on'] = 'Enrollment date cannot be in the future.';
    }

    return $errors;
}
