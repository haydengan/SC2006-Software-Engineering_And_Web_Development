<?php
session_start();

// php file that contains the common database connection code
include "dbFunctions.php";
if(isset($_SESSION['user_id'])){
    $value=$_SESSION['user_id'];
    $querySelect = "SELECT * FROM activity where creator_id =".$value."";
    $resultSelect = mysqli_query($link, $querySelect) or 
            die (mysqli_error($link));

    while ($row = mysqli_fetch_array($resultSelect)) {
        $arrResult[] = $row;
    }

    if(isset($arrResult)&&$arrResult!=NULL) {
    $number = count($arrResult);
    }
    else{
        $number = 0;
    }
}else{
    header('Location: login.php');
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>

    </head>
    <body class="indexbackground">
        <link href="stylesheets/style.css" rel="stylesheet" type="text/css"/>
        <?php include "navbar.php" ?>
        
        <h1 class="activity-header">Activity Records</h1>
        <p class="activity-count">There are <?php echo $number ?> recorded activities.</p>
        <br/>
        <?php if($number > 0){?>
        <table border='1' class="box-table1">
            <tr>
                <th>Activity Identifier</th>
                <th>Suggested Location</th>
                <th>Created On</th>
                <th>View Map</th>
                <th> Sharing Link</th>
            </tr>
                <?php
    for ($i = 0;$i < count($arrResult);$i++){
        $AID = $arrResult[$i]['activity_id'];
        $AN = $arrResult[$i]['suggestedActivityName'];
        $uploaded_on = $arrResult[$i]['date_created'];
        ?>
        
            <tr>
                <td> <?php echo $AID; ?></td>
                <td> <?php echo $AN; ?> </td>
                <td> <?php echo $uploaded_on; ?> </td>
                <td> <?php echo "<form action='viewActivity.php?' enctype='multipart/form-data' method='GET'><input type ='hidden' name='id' value = $AID required/>
        <input type='submit' class='style2' value='Go to Map'/></form>"?> </td>
                <td><a href="<?php echo "http://localhost/viewActivity.php?id=".$AID;?>"><?php echo "http://localhost/viewActivity.php?id=".$AID;?></a></td>
            </tr>  
        <?php
            }
        }
        ?>
        </table>
    </body>
</html>
