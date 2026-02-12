<?php

class AuthController {

    // ==============================
    // 🔹 REGISTER
    // ==============================
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
    }


    // ==============================
    // 🔹 LOGIN
    // ==============================
    public static function login($data)
{
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

    // 🔥 Generate Refresh Token
    $refreshToken = bin2hex(random_bytes(40));

    // 🔥 Hash refresh token for DB
    $refreshTokenHash = password_hash($refreshToken, PASSWORD_DEFAULT);

    // Save refresh token in DB (update your table accordingly)
    RefreshToken::deleteByUserId($user['id']);
    RefreshToken::create($user['id'], $refreshTokenHash);

    // 🔥 Create HMAC binding
    $binding = hash_hmac('sha256', $refreshToken, JWT_SECRET);

    // 🔥 Generate Access Token
    $accessToken = JWT::generate([
        "user_id" => $user['id'],
        "bind"    => $binding,
        "iat"     => time(),
        "exp"     => time() + JWT_EXPIRY
    ]);

    // 🔥 Store refresh token in cookie
    setcookie("refresh_token", $refreshToken, [
        "expires"  => time() + (60*60*24*7),
        "httponly" => true,
        "secure"   => false,
        "path"     => "/",
        "samesite" => "Strict"
    ]);

    $expiryTime = time() + JWT_EXPIRY;

Response::json(200, "Login success", [
    "access_token" => $accessToken,
    "expires_in"   => JWT_EXPIRY,          // seconds
    
]);

    
}

    // ==============================
    // 🔹 REFRESH TOKEN
    // ==============================
    public static function refresh()
{
    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if (!$refreshToken) {
        Response::json(401, "Refresh token missing");
    }

    $db = Database::connect();
    $result = $db->query("SELECT * FROM refresh_tokens");

    // ✅ If table empty
    if ($result->num_rows === 0) {
        Response::json(401, "refresh token not found ");
    }

    $validToken = null;

    while ($row = $result->fetch_assoc()) {
        if (password_verify($refreshToken, $row['token_hash'])) {
            $validToken = $row;
            break;
        }
    }

    // ✅ If rows exist but none matched
    if (!$validToken) {
        Response::json(401, "refresh token not found");
    }

    // Optional expiry check
    if (isset($validToken['expires_at']) && 
        strtotime($validToken['expires_at']) < time()) {
        Response::json(401, "Refresh token expired");
    }

    $binding = hash_hmac('sha256', $refreshToken, JWT_SECRET);

    $newAccessToken = JWT::generate([
        "user_id" => $validToken['user_id'],
        "bind"    => $binding,
        "iat"     => time(),
        "exp"     => time() + JWT_EXPIRY
    ]);

    Response::json(200, "Token refreshed", [
        "access_token" => $newAccessToken,
        "expires_in" => JWT_EXPIRY
    ]);
}




    // ==============================
    // 🔹 LOGOUT
    // ==============================
    public static function logout()
{
    $refreshToken = $_COOKIE['refresh_token'] ?? null;

    if ($refreshToken) {
        RefreshToken::deleteByToken($refreshToken); // ✅ correct
    }

    setcookie("refresh_token", "", time() - 3600, "/");

    Response::json(200, "Logged out");
}

}
