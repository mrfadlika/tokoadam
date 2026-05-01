<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        return $this->db->query("SELECT id, nama, username, role, is_active, created_at FROM users ORDER BY created_at DESC")->fetchAll();
    }
    
    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO users (nama, username, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['nama'],
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role']
        ]);
        return $this->db->lastInsertId();
    }
    
    public function update($id, $data) {
        $fields = ['nama = ?', 'username = ?', 'role = ?'];
        $params = [$data['nama'], $data['username'], $data['role']];
        
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }
    
    public function delete($id) {
        return $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$id]);
    }
    
    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password_hash']);
    }
}
