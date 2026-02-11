<?php

class User {

    public static function findByEmail($email) {
        $db = Database::connect();

        $stmt = $db->prepare(
            "SELECT * FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($name, $email, $password) {
        $db = Database::connect();

        $stmt = $db->prepare(
            "INSERT INTO users (name, email, password)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sss", $name, $email, $password);

        return $stmt->execute();
    }

    // 🔽 ===== ADDITIONAL METHODS (refresh token) =====

    public static function updateRefreshToken($userId, $token) {
        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE users SET refresh_token = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $token, $userId);

        return $stmt->execute();
    }

    public static function findByRefreshToken($token) {
        $db = Database::connect();

        $stmt = $db->prepare(
            "SELECT * FROM users WHERE refresh_token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public static function clearRefreshToken($token) {
        $db = Database::connect();

        $stmt = $db->prepare(
            "UPDATE users SET refresh_token = NULL WHERE refresh_token = ?"
        );
        $stmt->bind_param("s", $token);

        return $stmt->execute();
    }

} 
