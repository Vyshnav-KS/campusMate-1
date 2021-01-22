<?php

include("../DataBase.php"); 
include("evalRequest.php");

$function_map = array(
    "fun_login" => array(
        "name"          => "api_Login",
        "arg_c"         => "2",
        "args_types"    => array("str", "str")
        ),

    "fun_register" => array(
        "name"          => "api_Register",
        "arg_c"         => "2",
        "args_types"    => array("str", "str")
        ),
    );

# Main -------------------------------------------------------------------
evalRequest($function_map);
# Api Functions ----------------------------------------------------------

function api_Register($args) {
    $_POST["name"] = $args["p0"];
    $_POST["pass"] = $args["p1"];
    $_POST["pass2"] = $args["p1"];

    $result = registerUser();
    echo json_encode($result);
}


function api_Login($args) {
    $return_val = array(
        'result' => true,
        'err'    => "",
    );

    $logger = new Logger();

    if (empty($_POST["name"]) || empty($_POST["pass"]) ) {
        $return_val['result'] = false;
        $return_val['err'] = "*Please fill data";
        echo json_encode($return_val);
    } 

    $name = $args["p0"];
    $pass = $args["p1"];
    $hash = password_hash($name.$pass, PASSWORD_DEFAULT);

    $conn = connectToDB();
    if(!$conn) {
        $return_val['result'] = false;
        $return_val['err'] = "*Connection to database failed.";
        echo json_encode($return_val);
    }

    // SQL DB
    $sql = "SELECT name, pass_hash FROM Users WHERE name='$name'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            if(password_verify($name.$pass, $row["pass_hash"])) {
                $logger->addLog("Login success : User $name logged in.");
                echo json_encode($return_val);
            }
        }
    }

    $return_val['result'] = false;
    $return_val['err'] = "*Wrong username or password";
    echo json_encode($return_val);
}
