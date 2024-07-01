<?php
require_once("./connection.php");

$data = json_decode(file_get_contents('php://input'), true);
$skillType = $data['skillType'];
$skills = $data['skills'];

$response = ['success' => false];

if (!empty($skillType) && !empty($skills)) {
    // Insert new skill type
    $skillTypeEscaped = mysqli_real_escape_string($connection, $skillType);
    $query = "INSERT INTO skills_type (skill_type) VALUES ('$skillTypeEscaped')";
    if (mysqli_query($connection, $query)) {
        $skillTypeId = mysqli_insert_id($connection);

        // Insert new skills
        foreach ($skills as $skill) {
            $skillEscaped = mysqli_real_escape_string($connection, $skill);
            $query = "INSERT INTO skills (skill_type_id, skill_name) VALUES ('$skillTypeId', '$skillEscaped')";
            if (!mysqli_query($connection, $query)) {
                $response['error'] = 'Failed to add skills.';
                echo json_encode($response);
                exit;
            }
        }
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to add skill type.';
    }
}

echo json_encode($response);
?>
