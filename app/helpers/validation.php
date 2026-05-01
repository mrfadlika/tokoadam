<?php
/**
 * Input Validation & Sanitization Helpers
 */

/**
 * Sanitize input string
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get sanitized POST value
 */
function post($key, $default = '') {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Get sanitized GET value
 */
function get($key, $default = '') {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Simple validation class
 */
class Validator {
    private $errors = [];
    private $data = [];
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function required($field, $label = null) {
        $label = $label ?? $field;
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = "$label wajib diisi.";
        }
        return $this;
    }
    
    public function numeric($field, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && trim($this->data[$field]) !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "$label harus berupa angka.";
        }
        return $this;
    }
    
    public function minValue($field, $min, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && is_numeric($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field] = "$label minimal $min.";
        }
        return $this;
    }
    
    public function maxLength($field, $max, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = "$label maksimal $max karakter.";
        }
        return $this;
    }
    
    public function unique($field, $table, $column, $excludeId = null, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && trim($this->data[$field]) !== '') {
            $db = getDB();
            $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
            $params = [$this->data[$field]];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->fetchColumn() > 0) {
                $this->errors[$field] = "$label sudah digunakan.";
            }
        }
        return $this;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function getError($field) {
        return $this->errors[$field] ?? '';
    }
    
    public function getFirstError() {
        return reset($this->errors) ?: '';
    }
}
