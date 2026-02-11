<?php

class AuthController {

    // 🔹 REGISTER
    public static function register($data) {

        if (!$data) {
            Response::json(400, "Request body missing");
        }

        if (
            empty($data['name']) ||
            empty($data['email']) ||
            empty($data['password'])
        ) {
            Response::json(400, "Name, email and password required");
        }

        if (User::findByEmail($data['email'])) {
            Response::json(400, "Email already exists");
        }

        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        User::create(
            $data['name'],
            $data['email'],
            $hash
        );

        Response::json(201, "User registered");
    } // ✅ register closed properly


    // 🔹 LOGIN (access + refresh token)
    public static function login($data) {

        if (!$data) {
            Response::json(400, "Request body missing");
        }

        if (empty($data['email']) || empty($data['password'])) {
            Response::json(400, "Email and password required");
        }

        $user = User::findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::json(401, "Invalid credentials");
        }

        // ACCESS TOKEN
        $accessToken = JWT::generate([
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + JWT_EXPIRY
        ]);

        // REFRESH TOKEN
        $refreshToken = bin2hex(random_bytes(40));
        User::updateRefreshToken($user['id'], $refreshToken);

        setcookie("refresh_token", $refreshToken, [
            "httponly" => true,
            "secure"   => false,
            "path"     => "/",
            "samesite" => "Strict"
        ]);

        Response::json(200, "Login success", [
            "access_token" => $accessToken,
            "expires_in"   => JWT_EXPIRY
        ]);
    } // ✅ login closed properly


    // 🔹 REFRESH TOKEN
    public static function refresh() {

        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if (!$refreshToken) {
            Response::json(401, "Refresh token missing");
        }

        $user = User::findByRefreshToken($refreshToken);

        if (!$user) {
            Response::json(401, "Invalid refresh token");
        }

        $newAccessToken = JWT::generate([
            "user_id" => $user['id'],
            "email"   => $user['email'],
            "iat"     => time(),
            "exp"     => time() + JWT_EXPIRY
        ]);

        $newRefreshToken = bin2hex(random_bytes(40));
        User::updateRefreshToken($user['id'], $newRefreshToken);

        setcookie("refresh_token", $newRefreshToken, [
            "httponly" => true,
            "secure"   => false,
            "path"     => "/",
            "samesite" => "Strict"
        ]);

        Response::json(200, "Token refreshed", [
            "access_token" => $newAccessToken,
            "expires_in"   => JWT_EXPIRY
        ]);
    } // ✅ refresh closed properly


    // 🔹 LOGOUT
    public static function logout() {

        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if ($refreshToken) {
            User::clearRefreshToken($refreshToken);
        }

        setcookie("refresh_token", "", time() - 3600, "/");

        Response::json(200, "Logged out");
    } // ✅ logout closed properly

} // ✅ CLASS CLOSED
