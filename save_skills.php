<?php
require_once("./connection.php");

$data = json_decode(file_get_contents('php://input'), true);
$skills = $data['skills'];

$response = ['success' => false];

if (!empty($skills)) {
    // Clear existing skills
    mysqli_query($connection, "DELETE FROM skills");

    // Insert new skills
    foreach ($skills as $skill) {
        $skillId = $skill['skillId'];
        $skillTypeId = $skill['skillTypeId'];
        $skillName = mysqli_real_escape_string($connection, $skill['skillName']);

        $query = "INSERT INTO skills (skill_id, skill_type_id, skill_name) VALUES ('$skillId', '$skillTypeId', '$skillName')";
        if (!mysqli_query($connection, $query)) {
            $response['error'] = 'Failed to save skills.';
            echo json_encode($response);
            exit;
        }
    }
    $response['success'] = true;
}

echo json_encode($response);
?>
