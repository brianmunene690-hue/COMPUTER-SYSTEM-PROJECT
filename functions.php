<?php
namespace Kamau\Helpers;

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function successResponse($data = null, $message = 'Success') {
    return jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}

function errorResponse($message, $statusCode = 400, $errors = null) {
    return jsonResponse([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], $statusCode);
}

function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $errors[] = "{$field} is required";
        }
    }
    return $errors;
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function getInput() {
    $data = json_decode(file_get_contents('php://input'), true);
    return $data ? sanitizeInput($data) : [];
}