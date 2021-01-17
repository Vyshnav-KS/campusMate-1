
<!DOCTYPE HTML>  
<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  

<?php

//---------------------------------
// Paper dict
//  paper_name .. paper name
//  subject ..... paper Subject
//  details ..... paper info
//  branch ...... Branch
//---------------------------------
include("../../DataBase.php");

$logger         = new Logger();
$base_dir       = $_SERVER['DOCUMENT_ROOT'];
$target_file    = "";
$error          = "";


function checkErrors() {
    global $error;
    if (empty($_POST["paper_name"])) {
        $error = "question paper Name is required ";
    }
    if (empty($_POST["subject"])) {
        $error = $error."subject is required ";
    }
    if (empty($_POST["details"])) {
        $error = $error."Info is required ";
    }
    if (empty($_POST["branch"])) {
        $error = $error."Branch is required ";
    }
    if (empty($_FILES["file"]["name"])) {
        $error = $error."file not uploaded ";
    }
    if($error != "") {
        return false;
    }
    return true;
}

function saveFile() {
    global $error, $base_dir, $logger, $target_file;
    $sem = $_POST['sem'];
    $branch = $_POST['branch'];
    $base_dir =  $_SERVER['DOCUMENT_ROOT'];
    $folder_path = "$base_dir/Data/files/s$sem".$branch."/";

    // Get available filename
    $i = 0;
    $target_file = $folder_path . basename($_FILES["file"]["name"]);
    while(file_exists($target_file)) {
        $target_file = $folder_path . "$i". basename($_FILES["file"]["name"]);
        $i += 1;
    }
    // Create Folder to file if not exists
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }
    // Save File
    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $error = "Error Failed to upload file";
        $logger->addLog("Error : Submitted file  was not uploaded", 'e');
    }
    else {
        $paper_name = $_POST["paper_name"];
        $logger->addLog("Notes uploaded : uploaded notes $paper_name", '+');
        return true;
    }
    return false;
}

function saveFileEntry() {
    global $base_dir, $logger, $target_file;
    $sem = $_POST['sem'];
    $branch = $_POST['branch'];

    $file_data = array();
    $file_data['sem']           = $sem;
    $file_data['paper_name']     = $_POST["paper_name"];
    $file_data['subject']   = $_POST["subject"];
    $file_data['details']       = $_POST["details"];
    $file_data['file']          = $target_file;
    $file_data['visible']       = "false";

    // Save Config
    $count = 1;
    $database_path  = "$base_dir/Data/pages/$branch".$sem."_papers.json"; // Data/pages/cse3_books.json
    if (file_exists($database_path)) 
    {
        $database       = file_get_contents($database_path);
        $database       = json_decode($database, true);
        $count          = sizeof($database) + 1;
    }

    $database["n$count"] = $file_data;

    // Trucate file
    $file = fopen($database_path, 'w');
    fclose($file);

    $file = fopen($database_path, 'w');
    fwrite($file, json_encode($database));
    fclose($file);
    return true;
}

function main(){
    global $logger;
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        return;
    }

    if (checkErrors() && saveFile() && saveFileEntry()) {
        $logger->addLog("Book uploaded : Success", '+');
    }
}

main();

?>


<h1>Question Paper Upload Menu</h1>
<form name = "myform" method="post" enctype="multipart/form-data">
<span class="error"><?php echo $error;?></span>
<br><br>
Paper name:&ensp; <input type="text" name="paper_name" value="">
<br><br>
Subject name:&ensp; <input type="text" name="subject" value="">
<br><br>
Info:&ensp; <textarea name="details" rows="5" cols="40"></textarea>
<br><br>
Branch :&ensp;
<input type="radio" name="branch" value="cse">CSE
<input type="radio" name="branch" value="it">IT
<input type="radio" name="branch" value="me">Mech
<input type="radio" name="branch" value="civil">CIVIL
<input type="radio" name="branch" value="ece">ECE
<input type="radio" name="branch" value="eee">EEE
<br><br>

Semester :
<select name = "sem">
<?php
for ($i = 1; $i <= 8; $i += 1)
{
    echo "<option value = \"$i\"> Sem $i</option>";
}
?>
</select>

<br><br><br><br>
Upload Document :
<input type="file" name="file" id="file" onchange="fileSelected();"/>
<br><br>
<div id="fileName"></div>
<div id="fileSize"></div>
<div id="fileType"></div>
<div class="row">
<input type="button" onclick="uploadFile()" value="Upload" />
</div>
<div id="progressNumber"></div>
</form>

<script type="text/javascript">
function fileSelected() {
    var file = document.getElementById('file').files[0];
    if (file) {
        var fileSize = 0;
        if (file.size > 1024 * 1024)
            fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
        else
            fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';

        document.getElementById('fileName').innerHTML = 'Name: ' + file.name;
        document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize;
        document.getElementById('fileType').innerHTML = 'Type: ' + file.type;
    }
}

function uploadFile() {
    var fd = new FormData(document.forms.myform);
    //fd.append("file", document.getElementById('file').files[0]);
    var xhr = new XMLHttpRequest();
    xhr.upload.addEventListener("progress", uploadProgress, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);
    xhr.open("POST", <?php echo "\"".htmlspecialchars($_SERVER["PHP_SELF"])."\"";?>);
    xhr.send(fd);
    return false;
}

function uploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        document.getElementById('progressNumber').innerHTML = "Uploaded : "+ percentComplete.toString() + '%';
    }
    else {
        document.getElementById('progressNumber').innerHTML = 'unable to compute';
    }
}

function uploadComplete(evt) 
{
    //alert();
    document.body.innerHTML = evt.target.responseText;
}

function uploadFailed(evt) {
    alert("There was an error attempting to upload the file.");
}

function uploadCanceled(evt) {
    alert("The upload has been canceled by the user or the browser dropped the connection.");
}
</script>


</body>
</html>
