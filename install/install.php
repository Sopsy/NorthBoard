<?php

function ImportSqlFile($fileName) {
    // Load and explode the sql file
    $f = fopen($fileName, "r+");
    $sqlFile = fread($f, filesize($fileName));
    $sqlArray = explode(';', $sqlFile);

    //Process the sql file by statements
    foreach ($sqlArray as $stmt) {
        if (strlen($stmt) > 3) {
            $result = mysql_query($stmt);
            if (!$result) {
                $sqlErrorCode = mysql_errno();
                $sqlErrorText = mysql_error();
                echo '<b>Error executing sql file ' . $fileName . ':</b> ' . $sqlErrorCode . ' ' . $sqlErrorText . '<br/>';
                return false;
            }
        }
    }
    return true;
}

function TableExists( $tablename, $database = false )
{
  if( !$database )
  {
    $res = mysql_query( "SELECT DATABASE()" );
    $database = mysql_result( $res, 0 );
  }

  $res = mysql_query("
      SELECT COUNT(*) AS count 
      FROM information_schema.tables 
      WHERE table_schema = '$database' 
      AND table_name = '$tablename'
  ");

  return mysql_result( $res, 0 ) == 1;

}

if( !file_exists( '../inc/config.php' ) )
{
  die( 'Please copy the inc/config.php.sample file to inc/config.php and configure it as needed' );
}


require( '../inc/config.php' );

$mysqlConn = mysql_connect( $cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass'] ) or die( 'Wrong mysql host, username or password!' );
mysql_select_db( $cfg['dbname'] ) or die( 'Couldn\'t select the database, check if it is named correctly.' );

if( !TableExists( 'admin_users' ) )
{
  // Works only for local mysql server, feel free to customize it to suit your needs
  //system( "mysql {$cfg['dbname']} -u{$cfg['dbuser']}  -p{$cfg['dbpass']} < ../dbschema.sql" );
  
  // v2
  ImportSqlFile('../dbschema.sql');
}

require( '../inc/functions_general.php' );

$res = mysql_query( 'SELECT `id` FROM `admin_users`' );
if( mysql_num_rows( $res ) == 0 )
{

  if( empty( $_POST ) ) {
    echo '
      <h2>'. "Create an admin account" .'</h2>
      <form action="install.php" method="post" id="adminform">
      <fieldset>

      <legend>User details</legend>
      <label for="uname">Username:</label>
      <input type="text" name="uname" id="uname" />

      <label for="passwd">Password:</label>
      <input type="text" name="passwd" id="passwd" />

      <label for="email">E-mail:</label>
      <input type="text" name="email" id="email" />

      <label for="class">User class:</label>
      <select name="class" id="class">
      <option value="1">Admin</option>
      <option value="2">SMod</option>
      <option value="3">Mod</option>
      </select>
      <input type="submit" value="Create" name="add" id="add" />

      </fieldset>
      </form>';
    die();
  }
  else
  {
    if(!empty($_POST['uname'])) {
      $uname = mysql_real_escape_string($_POST['uname']);
    }
    else die( "Username is missing." );

    $minpwlen = 6;
    if(!empty($_POST['passwd']) AND strlen($_POST['passwd']) > $minpwlen) {
      $passwd = encrypt_password($_POST['passwd']);
    }
    else die( "Password is missing or is too short (min. $minpwlen characters)." );

    $email = mysql_real_escape_string($_POST['email']);

    if(empty($_POST['class']) OR !is_numeric($_POST['class']))
      die( "User class is missing or is malformed." );
    else
      $class = $_POST['class'];

    $q = mysql_query("INSERT INTO `admin_users`(`name`, `password`, `email`, `user_class`, `added_by`, `added_time`) VALUES ('". $uname ."', '". $passwd ."', '". $email ."', '". $class ."', '0', UNIX_TIMESTAMP())");
    if(!$q)
      die( "Adding the user failed!" );

    echo "User added succesfully!\n";
  }
}
?>

<h1 style="color: red;">REMEMBER TO DELETE OR RENAME THIS FOLDER!</h1>

Now you should add a category to the categories table.<br />
After that add a board with the same category as the id of the category just added.<br />
If the themes don't seem to work just change the $cfg['use_tmpfs'] to false from the config.php.<br />
Play around with the database to customize your board, just use common sense.
