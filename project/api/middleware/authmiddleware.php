<?php
class AuthMiddleware {

    public static function handle() {

        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            Response::json(401, "Authorization token missing");
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $user  = JWT::verify($token);

        if (!$user) {
            Response::json(401, "Invalid or expired token");
        }

        return $user; 
    }
}