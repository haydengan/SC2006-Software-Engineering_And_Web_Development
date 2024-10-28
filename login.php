<?php
session_start();
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // php file that contains the common database connection code
    include "dbFunctions.php";

    $enteredUsername = $_POST['username'];
    $enteredPassword = $_POST['password'];

    $queryCheck = "SELECT * FROM user WHERE username = '$enteredUsername' AND password = SHA1('$enteredPassword')";

    $resultCheck = mysqli_query($link, $queryCheck) or 
            die (mysqli_error($link));

    if (mysqli_num_rows($resultCheck) == 1) {
        $row = mysqli_fetch_array($resultCheck);
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['password'] = $row['password'];
        $_SESSION['role'] = $row['role'];
        header("location: index.php");
    } else{
        $msg = "<p class='error-message'>Sorry, you must enter a valid username and password to log in.</p>"."\n";
    }
}
?>

<link href="stylesheets/style.css" rel="stylesheet" type="text/css"/>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

<html>
    <head>
        <meta charset="UTF-8">
        <title>Let's meet!</title>

    </head>
    <body>
        
        <?php include "navbar.php" ?>
        <div class="half-background"></div>
        <h1 class="animated-header">Let's Meet! - Login</h1>
        <p class="welcome-message">Discover the best way to meet friends with an app<br> that finds midpoints and transportation routes.</p>
        <fieldset>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <table class="box-table">
            <tr>
                <td><b>Username:</b></td>
                <td><input type="text" name="username"/></td>
            </tr>
            <tr>
                <td><b>Password:</b></td>
                <td><input type="password" name="password"/></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Login"/></td>
            </tr>
         </table>
        </form>
        </fieldset>
        <img src="stylesheets/images/car.png" alt="A relevant description" class="login-image" />
        <?php
        // put your code here
        echo $msg;
        ?>
    </body>
</html>
