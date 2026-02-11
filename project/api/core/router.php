<?php
class Router {

    public static function route($uri, $method, $body) {

        // ---------- AUTH ROUTES ----------
        if ($uri === '/api/register' && $method === 'POST') {
            AuthController::register($body);
            return;
        }

        if ($uri === '/api/login' && $method === 'POST') {
            AuthController::login($body);
            return;
        }
        if ($uri === '/api/token/refresh' && $method === 'POST') {
            AuthController::refresh();
            return;
        }
        
        if ($uri === '/api/logout' && $method === 'POST') {
            AuthController::logout();
            return;
        }
        

        // ---------- PATIENT ROUTES ----------

        // GET ALL
        if ($uri === '/api/patients' && $method === 'GET') {
            PatientController::index();
            return;
        }

        // POST
        if ($uri === '/api/patients' && $method === 'POST') {
            PatientController::store($body);
            return;
        }

        // ROUTES WITH ID
        if (preg_match('#^/api/patients/(\d+)$#', $uri, $matches)) {

            $id = $matches[1];

            if ($method === 'GET') {
                PatientController::show($id);
                return;
            }

            if ($method === 'PUT') {
                PatientController::update($id, $body);
                return;
            }

            if ($method === 'PATCH') {
                PatientController::patch($id, $body);
                return;
            }

            if ($method === 'DELETE') {
                PatientController::delete($id);
                return;
            }
        }

        // ---------- NO ROUTE ----------
        Response::json(404, "Route not found");
    }
}
