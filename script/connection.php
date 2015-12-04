<!--Shamelessly copied from our Tasks. No sense in writing what's already done! -->
<?php 

  $db_hostname = 'KC-SCE-APPDB01.kc.umkc.edu';
  $db_database = "jrkn87";
  $db_username = "jrkn87";
  $db_password = "fGJqTUgGKzhgziv";
  

 $connection = mysqli_connect($db_hostname, $db_username,$db_password,$db_database);
 
 if (!$connection)
    die("Unable to connect to MySQL: " . mysqli_error($connection));


?>