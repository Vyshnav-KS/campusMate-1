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
//---------------------------------
// Notes dict
//  note_name ... Note name
//  subject ..... Note Subject
//  details ..... Note info
//  branch ...... Branch
//---------------------------------

include("../../DataBase.php");

$logger         = new Logger();
$base_dir       = $_SERVER['DOCUMENT_ROOT'];
$target_file    = "";
$error          = "";
$error = "";


function checkErrors() {
    global $error;
    if (empty($_POST["note_name"])) {
        $error = "Note Name is required ";
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
    $folder_path = "$base_dir/Data/files/s$sem".$branch."/";

    // Select available filename
    $i = 0;
    $target_file = $folder_path . basename($_FILES["file"]["name"]);
    while(file_exists($target_file)) {
        $target_file = $folder_path . "$i". basename($_FILES["file"]["name"]);
        $i += 1;
    }
    // Create Folder if not exists
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }
    // Save File
    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $error = "Error Failed to upload file";
        $logger->addLog("Error : Submitted file  was not uploaded", 'e');
        return false;
    }
    else {
        $note_name = $_POST["note_name"];
        $logger->addLog("Notes uploaded : uploaded notes $note_name", '+');
        return true;
    }
    return false;
}


function saveFileEntry() {
    global $error, $base_dir, $logger, $target_file;
    $sem = $_POST['sem'];
    $branch = $_POST['branch'];
    $file_data = array();
    $file_data['sem']       = $sem;
    $file_data['note_name'] = $_POST["note_name"];
    $file_data['subject']   = $_POST["subject"];
    $file_data['details']   = $_POST["details"];
    $file_data['file']      = $target_file;
    $file_data['visible']   = "false";

    // Save Config
    $count = 1;
    $database_path  = "$base_dir/Data/pages/$branch".$sem."_notes.json"; // Data/pages/cse3_books.json
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


<form name = "myform" class="uploader_form-page" method="post" enctype="multipart/form-data">
        <span class="error"><?php echo $error;?></span>
        <div class="wrapper">
        <div class="title">
          UPLOAD YOUR NOTES HERE
        </div>
        <div class="form">
           <div class="inputfield">
              <label>Book Name</label>
              <input type="text" name="book_name" class="input">
           </div>  
            <div class="inputfield">
              <label>Subject Name</label>
              <input type="text" name="author_name" class="input">
           </div>  
    
           <div class="inputfield">
            <label>Branch</label>
            <div class="custom_select">
              <select name="">
                <option value="">Select</option>
                <option value="civil">Civil Engineering</option>
                <option value="cse">Computer Science Engineering</option>
                <option value="eee">Electrial and Enlectronics Engineering</option>
                <option value="ece">Electronics and Communication Engineering</option>
                <option value="it">Information Technology</option>
                <option value="me">Mechanical Engineering</option>
              </select>
            </div>
         </div> 
           
            <div class="inputfield">
              <label>Semester</label>
              <div class="custom_select">
                <select name="sem">
                  <option value="">Select</option>
                  <option value="S1">SEMESTER 1</option>
                  <option value="S2">SEMESTER 2</option>
                  <option value="S3">SEMESTER 3</option>
                  <option value="S4">SEMESTER 4</option>
                  <option value="S5">SEMESTER 5</option>
                  <option value="S5">SEMESTER 5</option>
                  <option value="S6">SEMESTER 6</option>
                  <option value="S7">SEMESTER 7</option>
                  <option value="S8">SEMESTER 8</option>
                </select>
              </div>
           </div> 
            <div class="inputfield">
              <label>Email Address</label>
              <input type="text" class="input">
           </div> 
          <div class="inputfield">
              <label>Book Info</label>
              <textarea class="textarea" name="details" ></textarea>
           </div> 
    
           <div class="uploader_file inputfield">
            <label for=""> Select Document</label>
            <input type="file" class="btn" name="file" id="file" onchange="fileSelected();"/>
            
            <div id="fileName"></div>
            <div id="fileSize"></div>
            <div id="fileType"></div>
            </div>
           
          <div class="inputfield">
            <input type="button" value="Upload" class="btn" onclick="uploadFile()">
          </div>
          <div id="progressNumber"></div>
        </div>
    </div>
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
