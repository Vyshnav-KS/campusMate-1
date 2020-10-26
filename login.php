<!DOCTYPE HTML>  
<html>
<head>
<style>
.error {color: #FF0000;}
</style>

<?php

  include('php/utility.php');

  $err = "";
  $name = "";

  if ($_SERVER["REQUEST_METHOD"] == "POST") 
  {
	if (empty($_POST["name"]) || empty($_POST["pass"]) ) 
	{
		$err = "*Please fill data";
	} 
	else 
	{
	  $name = formatString($_POST["name"]);
	  $pass = formatString($_POST["pass"]);
	  $hash = crypt($name.$pass, "ZRsuP1Gi2112");

	  if (!file_exists("Data/users/".$name."/".$hash))
	  {
	  	$err = "*Wrong username or password";
	  }
	  else
	  {
			$ip = getClientIP();
			$IP_list_folder = "Data/IP_lists/";

			if (!file_exists("Data/users/".$name."/".$hash))
			{
				mkdir($IP_list_folder, 0777, true);
			}

			$file = fopen($IP_list_folder.$ip, 'w');
			fwrite($file, $name);
			fclose($file);

			// Redirect to index
			header("Location: index.html");
			exit;
	  }
	}
  }
  

?>

</head>
	<body>  
	<h2>Login</h2>
	<p><span class="error"><?php echo $err;?></span></p>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
	  Username : <input type="text" name="name" value="<?php echo $name;?>">
	  <br><br>
	  Password : <input type="password" name="pass" value="">
	  <br><br>
	 	 <input type="submit" name="submit" value="Submit">  
	</form>
	</body>
</html>
