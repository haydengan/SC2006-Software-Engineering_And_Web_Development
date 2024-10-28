<div id="menu">
    <ul>
        <?php if ( isset($_SESSION['user_id']) ) { ?>
            <li><a href="../../../../index.php">View Activities</a></li>
            <li><a href="createActivity.php">Create New Activity</a></li>
            <li><a href="logout.php">logout</a></li>
        <?php } else { ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
        
        <?php } ?>
        <?php if (isset($_SESSION['username'])) { echo "<div style='text-align:right'><i> Welcome " . $_SESSION['username'] . " " . " (" . $_SESSION['role'] . ")" ." ". "</i>";}?>
    </ul>
</div>
