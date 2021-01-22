<?php

include("../DataBase.php"); 
include("evalRequest.php");

$function_map = array(
    "fun_bookList" => array(
        "name"          => "api_getBookList",
        "arg_c"         => "2",
        "args_types"    => array("str", "str")
        ),

    "fun_noteList" => array(
        "name"          => "api_getNoteList",
        "arg_c"         => "2",
        "args_types"    => array("str", "str")
        ),
    
    "fun_paperList" => array(
        "name"          => "api_getPaperList",
        "arg_c"         => "2",
        "args_types"    => array("str", "str")
        ),
    );

# Main -------------------------------------------------------------------
evalRequest($function_map);
# Api Functions ----------------------------------------------------------

function api_getBookList($args) {
    $branch = $args["p0"];
    $sem = $args["p1"];

    $file = "Data/pages/".$branch.$sem."_books.json";
    echo file_get_contents($file);
}

function api_getNoteList($args) {
    $branch = $args["p0"];
    $sem = $args["p1"];

    $file = "Data/pages/".$branch.$sem."_notes.json";
    echo file_get_contents($file);
}

function api_getPaperList($args) {
    $branch = $args["p0"];
    $sem = $args["p1"];

    $file = "Data/pages/".$branch.$sem."_papers.json";
    echo file_get_contents($file);
}

?>
