<?php
class Patient {

    // GET ALL
    public static function all() {
        $db = Database::connect();
        return $db->query("SELECT * FROM patients");
    }

    // GET BY ID
    public static function find($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // CREATE
    public static function create($data) {
        $db = Database::connect();

        $name    = $data['name'];
        $age     = $data['age'];
        $gender  = $data['gender'];
        $phone   = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        $stmt = $db->prepare(
            "INSERT INTO patients (name, age, gender, phone, address)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sisss",
            $name,
            $age,
            $gender,
            $phone,
            $address
        );

        return $stmt->execute();
    }

    // PUT (FULL UPDATE)
    public static function update($id, $data) {
        $db = Database::connect();

        $name    = $data['name'];
        $age     = $data['age'];
        $gender  = $data['gender'];
        $phone   = $data['phone'] ?? null;
        $address = $data['address'] ?? null;

        $stmt = $db->prepare(
            "UPDATE patients
             SET name = ?, age = ?, gender = ?, phone = ?, address = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "sisssi",
            $name,
            $age,
            $gender,
            $phone,
            $address,
            $id
        );

        return $stmt->execute();
    }

    // PATCH (PARTIAL UPDATE)
    public static function patch($id, $data) {
        $db = Database::connect();

        $fields = [];
        $types  = "";
        $values = [];

        if (array_key_exists('name', $data)) {
            $fields[] = "name = ?";
            $types .= "s";
            $values[] = $data['name'];
        }

        if (array_key_exists('age', $data)) {
            $fields[] = "age = ?";
            $types .= "i";
            $values[] = $data['age'];
        }

        if (array_key_exists('gender', $data)) {
            $fields[] = "gender = ?";
            $types .= "s";
            $values[] = $data['gender'];
        }

        if (array_key_exists('phone', $data)) {
            $fields[] = "phone = ?";
            $types .= "s";
            $values[] = $data['phone'];
        }

        if (array_key_exists('address', $data)) {
            $fields[] = "address = ?";
            $types .= "s";
            $values[] = $data['address'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE patients SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);

        return $stmt->execute();
    }

    // DELETE
    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
