<?php
class PatientController {

    // 🔹 GET /api/patients
    public static function index() {
        AuthMiddleware::handle();

        $res = Patient::all();
        Response::json(200, "Patients", $res->fetch_all(MYSQLI_ASSOC));
    }

    // 🔹 GET /api/patients/{id}
    public static function show($id) {
        AuthMiddleware::handle();

        $patient = Patient::find($id);

        if (!$patient) {
            Response::json(404, "Patient not found");
        }

        Response::json(200, "Patient", $patient);
    }

    // 🔹 POST /api/patients
    public static function store($data) {
        AuthMiddleware::handle();

        if (
            empty($data['name']) ||
            empty($data['age']) ||
            empty($data['gender'])
        ) {
            Response::json(400, "Name, age and gender required");
        }

        Patient::create($data);
        Response::json(201, "Patient created");
    }

    // 🔹 PUT /api/patients/{id}
    public static function update($id, $data) {
        AuthMiddleware::handle();

        if (
            empty($data['name']) ||
            empty($data['age']) ||
            empty($data['gender'])
        ) {
            Response::json(400, "PUT requires all fields");
        }

        Patient::update($id, $data);
        Response::json(200, "Patient updated");
    }

    // 🔹 PATCH /api/patients/{id}
    public static function patch($id, $data) {
        AuthMiddleware::handle();
    
        if (!$data || !is_array($data)) {
            Response::json(400, "Request body required");
        }
    
        $updated = Patient::patch($id, $data);
    
        if (!$updated) {
            Response::json(400, "No valid fields to update");
        }
    
        Response::json(200, "Patient partially updated");
    }
    

    // 🔹 DELETE /api/patients/{id}
    public static function delete($id) {
        AuthMiddleware::handle();

        Patient::delete($id);
        Response::json(200, "Patient deleted");
    }
}
