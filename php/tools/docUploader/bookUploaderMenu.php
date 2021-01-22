
<!DOCTYPE HTML>  
<html>
<head>
    <link rel="stylesheet" href="uploader.css">
<style>
.error {color: #FF0000;}
</style>
</head>
<body>  

<?php

include("../../DataBase.php");

$logger         = new Logger();
$base_dir       = $_SERVER['DOCUMENT_ROOT'];
$target_file    = "";
$error          = "";

function checkErrors() {
    global $error;
    if (empty($_POST["book_name"])) {
        $error = "Book Name is required ";
    }
    if (empty($_POST["author_name"])) {
        $error = $error."Author name is required ";
    }
    if (empty($_POST["details"])) {
        $error = $error."Info is required ";
    }
    if (empty($_POST["branch"])) {
        $error = $error."Branch is required ";
    }
    if (empty($_FILES["file"]["name"])){
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
    // Try to Save File
    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $error = "Error Failed to upload file";
        $logger->addLog("Error : Submitted file  was not uploaded", 'e');
    }
    else {
        $book_name = $_POST["book_name"];
        $logger->addLog("Book uploaded : uploaded book $book_name", '+');
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
    $file_data['book_name']     = $_POST["book_name"];
    $file_data['author_name']   = $_POST["author_name"];
    $file_data['subject_name']  = "";
    $file_data['details']       = $_POST["details"];
    $file_data['file']          = $target_file;
    $file_data['visible']       = "false";

    // Save Config
    $count = 1;
    $database_path  = "$base_dir/Data/pages/$branch".$sem."_books.json"; // Data/pages/cse3_books.json
    if (file_exists($database_path)) {
        $database       = file_get_contents($database_path);
        $database       = json_decode($database, true);
        $count          = sizeof($database) + 1;
    }

    $database["b$count"] = $file_data;

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


<div class="uploader">
<h1 class="uploader_title">Upload your e-book here.</h1>
<div class="uploader_form">
<form name = "myform" class="uploader_form-page" method="post" enctype="multipart/form-data">
<span class="error"><?php echo $error;?></span>
<br><br>
<div class="uploader_info">
&ensp; <input type="text" name="book_name" value="Book name">
<br><br>
&ensp; <input type="text" name="author_name" value="Author name:">
<br><br>
Info: <br>
&ensp; <textarea name="details" rows="5" cols="40"></textarea>
<br><br>
</div>
<div class="uploader_branch">
<span class="branch">Branch</span><br>&ensp;
<input type="radio" name="branch" value="cse">CSE
<input type="radio" name="branch" value="it">IT
<input type="radio" name="branch" value="me">Mech
<input type="radio" name="branch" value="civil">CIVIL
<input type="radio" name="branch" value="ece">ECE
<input type="radio" name="branch" value="eee">EEE
</div>
<br><br>

<div class="uploader_semester">

<select name = "sem">
<?php
for ($i = 1; $i <= 8; $i += 1)
{
    echo "<option value = \"$i\"> SEMESTER $i</option>";
}
?>
</select>
</div>

<br><br>
<div class="uploader_file">
Select Document <br>
<input type="file" name="file" id="file" onchange="fileSelected();"/>
<br><br>
<div id="fileName"></div>
<div id="fileSize"></div>
<div id="fileType"></div>
</div>
<div class="row uploader_btn">
<input type="button" class="uploader_button" onclick="uploadFile()" value="Upload" />
</div>
<div id="progressNumber"></div>
</form>
</div>
</div>

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
