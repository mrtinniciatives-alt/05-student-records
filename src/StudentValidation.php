<?php

function enumCourses(PDO $pdo): array
{
    $column = $pdo->query("SHOW COLUMNS FROM students LIKE 'course'")->fetch();
    if (!is_array($column) || !preg_match("/^enum\\((.*)\\)$/i", $column['Type'], $matches)) {
        return [];
    }

    preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $matches[1], $values);
    return array_map(static fn(string $value): string => stripcslashes($value), $values[1]);
}

function validDateNotFuture(string $value): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return false;
    }

    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false
        && $date->format('Y-m-d') === $value
        && $date->format('Y-m-d') <= date('Y-m-d');
}

function validateStudent(array $input, array $courses, PDO $pdo, ?int $excludeId = null): array
{
    $errors = [];
    $student = [
        'first_name' => trim((string) ($input['first_name'] ?? '')),
        'last_name' => trim((string) ($input['last_name'] ?? '')),
        'email' => trim((string) ($input['email'] ?? '')),
        'phone' => trim((string) ($input['phone'] ?? '')),
        'course' => trim((string) ($input['course'] ?? '')),
        'enrolled_on' => trim((string) ($input['enrolled_on'] ?? '')),
    ];

    foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
        $value = $student[$field];
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length === 0) {
            $errors[$field] = $label . ' is required.';
        } elseif ($length < 2 || $length > 60) {
            $errors[$field] = $label . ' must be between 2 and 60 characters.';
        } elseif (!preg_match('/^[\p{L} -]+$/u', $value)) {
            $errors[$field] = $label . ' may contain only letters, spaces, and hyphens.';
        }
    }

    if ($student['email'] === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($student['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address.';
    } else {
        $sql = 'SELECT COUNT(*) FROM students WHERE LOWER(email) = LOWER(:email)' . ($excludeId !== null ? ' AND id <> :exclude_id' : '');
        $check = $pdo->prepare($sql);
        $params = ['email' => $student['email']];
        if ($excludeId !== null) {
            $params['exclude_id'] = $excludeId;
        }
        $check->execute($params);
        if ((int) $check->fetchColumn() > 0) {
            $errors['email'] = 'This email is already registered.';
        }
    }

    if ($student['phone'] !== '' && !preg_match('/^0[789][01]\d{8}$/', $student['phone'])) {
        $errors['phone'] = 'Phone must match ^0[789][01]\d{8}$.';
    }

    if ($student['course'] === '') {
        $errors['course'] = 'Course is required.';
    } elseif (!in_array($student['course'], $courses, true)) {
        $errors['course'] = 'Select a valid course.';
    }

    if ($student['enrolled_on'] === '') {
        $errors['enrolled_on'] = 'Enrolled date is required.';
    } elseif (!validDateNotFuture($student['enrolled_on'])) {
        $errors['enrolled_on'] = 'Enter a valid date that is not in the future.';
    }

    return [$student, $errors];
}
