<?php
require_once("./connection.php");
require_once("./SideNav.php");

// Fetch skill types
$skillTypes = [];
$skillTypesQuery = "SELECT * FROM skillstype";
$result = mysqli_query($connection, $skillTypesQuery);
while ($row = mysqli_fetch_assoc($result)) {
    $skillTypes[$row['skill_type_id']] = $row['skill_type'];
}

// Fetch skills with status
$skills = [];
$skillsQuery = "SELECT * FROM skills";
$result = mysqli_query($connection, $skillsQuery);
while ($row = mysqli_fetch_assoc($result)) {
    $skills[$row['skill_type_id']][] = [
        'skill_id' => $row['skill_id'],
        'skill_name' => $row['skill_name'],
        'status' => $row['status']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skills of Thamali Perera</title>
    <link rel="stylesheet" href="skills.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="skills-info">
            <h2>Skills</h2>
            <div class="technologies">
                <?php foreach ($skillTypes as $typeId => $typeName) : ?>
                <div class="skill-type">
                    <p><strong><?php echo htmlspecialchars($typeName); ?>:</strong></p>
                    <select data-skill-type-id="<?php echo $typeId; ?>">
                        <option value="">Select</option>
                        <?php foreach ($skills[$typeId] as $skill) : ?>
                            <option value="<?php echo htmlspecialchars($skill['skill_name']); ?>"><?php echo htmlspecialchars($skill['skill_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="selected-skills" class="selected-skills">
                <!-- Selected skills will be dynamically added here -->
            </div>
            <div class="buttons">
                <button class="save-btn"><i class="fas fa-save"></i> Save</button>
            </div>
            <button class="add-skill-type-btn"><i class="fas fa-plus"></i> Add New Skill Type</button>
        </div>
    </div>

    <!-- Modal for adding new skill type and skills -->
    <div id="add-skill-type-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Add New Skill Type</h2>
            <form id="add-skill-type-form">
                <div class="form-group">
                    <label for="new-skill-type">Skill Type:</label>
                    <input type="text" id="new-skill-type" name="new_skill_type" required>
                </div>
                <div class="form-group">
                    <label for="new-skills">Skills (comma separated):</label>
                    <input type="text" id="new-skills" name="new_skills" required>
                </div>
                <button type="submit">Add Skill Type</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const skillSelects = document.querySelectorAll('select[data-skill-type-id]');
            const selectedSkillsContainer = document.getElementById('selected-skills');
            const saveBtn = document.querySelector('.save-btn');
            const addSkillTypeBtn = document.querySelector('.add-skill-type-btn');
            const modal = document.getElementById('add-skill-type-modal');
            const closeModal = document.querySelector('.close-modal');
            const addSkillTypeForm = document.getElementById('add-skill-type-form');

            // Initialize visibility based on existing selected skills
            updateSelectedSkillsVisibility();

            // Event listeners for select dropdowns
            skillSelects.forEach(select => {
                select.addEventListener('change', function () {
                    handleSkillSelection(this.dataset.skillTypeId, this.value);
                });
            });

            // Function to handle skill selection
            function handleSkillSelection(skillTypeId, skillName) {
                if (skillName) {
                    // Check if the skill is already selected
                    if (!isSkillSelected(skillTypeId, skillName)) {
                        const selectedSkillElement = document.createElement('div');
                        selectedSkillElement.classList.add('selected-skill-item');
                        selectedSkillElement.dataset.skillTypeId = skillTypeId;
                        selectedSkillElement.textContent = skillName;

                        // Add close button
                        const closeButton = document.createElement('span');
                        closeButton.classList.add('close-btn');
                        closeButton.innerHTML = '&times;';
                        closeButton.addEventListener('click', function () {
                            selectedSkillElement.remove();
                            updateSelectedSkillsVisibility(); // Update visibility after removal
                            updateSkillStatus(skillTypeId, skillName, 'notSelected'); // Update skill status in database
                        });
                        selectedSkillElement.appendChild(closeButton);

                        selectedSkillsContainer.appendChild(selectedSkillElement);
                        updateSelectedSkillsVisibility(); // Update visibility after addition

                        // Update skill status in database to 'selected'
                        updateSkillStatus(skillTypeId, skillName, 'selected');
                    }
                    // Clear the select dropdown after selection
                    clearSelectDropdown(skillTypeId);
                }
            }

            // Function to check if skill is already selected
            function isSkillSelected(skillTypeId, skillName) {
                const selectedItems = selectedSkillsContainer.querySelectorAll('.selected-skill-item');
                for (let item of selectedItems) {
                    if (item.dataset.skillTypeId === skillTypeId && item.textContent.trim() === skillName) {
                        return true;
                    }
                }
                return false;
            }

            // Function to clear select dropdown after selection
            function clearSelectDropdown(skillTypeId) {
                const select = document.querySelector(`select[data-skill-type-id="${skillTypeId}"]`);
                if (select) {
                    select.value = '';
                }
            }

            // Function to update visibility of selected skills container
            function updateSelectedSkillsVisibility() {
                const hasSelectedSkills = selectedSkillsContainer.querySelector('.selected-skill-item') !== null;
                selectedSkillsContainer.style.display = hasSelectedSkills ? 'block' : 'none';
            }

            // Function to update skill status in database via AJAX
            function updateSkillStatus(skillTypeId, skillName, status) {
                const skillId = getSkillId(skillTypeId, skillName); // Function to get skill ID based on skill type and name

                // AJAX request to update skill status
                fetch('update_skill_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ skillId, status })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        console.error('Failed to update skill status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Function to get skill ID based on skill type ID and name
            function getSkillId(skillTypeId, skillName) {
                const skill = skills[skillTypeId].find(skill => skill.skill_name === skillName);
                return skill ? skill.skill_id : null;
            }

            // Save button click event
            saveBtn.addEventListener('click', function () {
                saveSkills();
            });

            // Function to save skills
            function saveSkills() {
                const selectedItems = selectedSkillsContainer.querySelectorAll('.selected-skill-item');
                const skills = Array.from(selectedItems).map(item => ({
                    skillTypeId: item.dataset.skillTypeId,
                    skillName: item.textContent.trim()
                }));

                // Send AJAX request to save skills
                fetch('save_skills.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ skills })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Skills saved successfully.');
                    } else {
                        alert('Failed to save skills.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Show modal
            addSkillTypeBtn.addEventListener('click', function () {
                modal.style.display = 'block';
            });

            // Close modal
            closeModal.addEventListener('click', function () {
                modal.style.display = 'none';
            });

            // Add skill type form submit event
            addSkillTypeForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const newSkillType = document.getElementById('new-skill-type').value;
                const newSkills = document.getElementById('new-skills').value.split(',').map(skill => skill.trim());

                // Send AJAX request to add new skill type and skills
                fetch('add_skill_type.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ skillType: newSkillType, skills: newSkills })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Skill type and skills added successfully.');
                        location.reload(); // Reload the page to update the skills list
                    } else {
                        alert('Failed to add skill type and skills.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>
