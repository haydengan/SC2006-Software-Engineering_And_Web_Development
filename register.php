<?php
session_start();
include "dbFunctions.php" ;
$msg =""; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = sha1($_POST['password']);
    $query = "INSERT INTO user (username,email,password) VALUES('$username','$email','$password')";
    $result = mysqli_query($link, $query) or die(mysqli_error($link)); 

    if ($result) {
        header("location: login.php");
    }
    else{
        $msg.="Failed to sign up. Please try with a different Email or Username.";
    }
    mysqli_close($link);
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Let's Meet signup</title>
        <link href="stylesheets/style.css" rel="stylesheet" type="text/css"/>
        <?php include "navbar.php" ?>
    </head>
    <body>
        <div class="half-background"></div>
        <div class="form-container">
        <form name = "signup"action = "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method='post' class="box-table">
            Username: 
            <input type ="text" name ="username" required/>
            <br>
            Email: 
            <input type ="email" name ="email" pattern="+@." required/>
            <br>
            Password: 
            <input type="password" name="password" minlength="12" required/>
            <input type ="submit" value ="Register">
            <input type ="reset" value ="clear">
        </form>
        <img src="stylesheets/images/car.png" alt="A relevant description" class="login-image" />
        <?php
        // put your code here
        echo $msg;
        ?>
        </div>
    </body>
</html>
