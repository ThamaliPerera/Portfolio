<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./SideNav.css">
</head>
<body>
    <div>
        <div class="sidenav">
            <img src="./me.jpg" alt="Profile Picture" class="profile">
            <?php
                // Get the current filename
                $currentPage = basename($_SERVER['PHP_SELF']);
            ?>
            <a href="./index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">Introduction</a>
            <a href="./projects.php" class="<?php echo ($currentPage == 'projects.php') ? 'active' : ''; ?>">Projects</a>
            <a href="./skills.php" class="<?php echo ($currentPage == 'skills.php') ? 'active' : ''; ?>">Skills</a>
            <a href="./contact.php" class="<?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>">Contacts</a>
        </div>
    </div>
</body>
</html>
