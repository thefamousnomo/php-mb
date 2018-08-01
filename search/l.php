<?php
session_start();
if ( @$_GET['clear'] == 1 ) {
  setcookie('remember_me', null, -1, '/');
  unset($_SESSION['logged_in']);
  unset($_SESSION['saved_orders']);
}
require('password.php');
$dbc = parse_ini_file("../../c.ini");
$servername = $dbc['servername'];
$username = $dbc['username'];
$password = $dbc['password'];
$dbname = $dbc['dbname'];
$conn = new mysqli($servername, $username, $password, $dbname);
$id = @$conn->real_escape_string($_POST['u']);
$pw = @$conn->real_escape_string($_POST['p']);
// Check connection
if ($conn->connect_error)
{
   die("Connection failed: " . $conn->connect_error);
}
	$sql = "SELECT * FROM users where account_ref = '$id';";
	$result = $conn->query($sql);
	$hash = $result->fetch_assoc();
	if ( ! empty($hash['ACCOUNT_REF']) )
	{
		if ( empty($hash['E_MAIL']) )
		{
			echo 'Please contact Myles Bros and update your account with a current email address.';
			exit;
		}
		if ( empty($hash['PW']) )
		{
			$p =  substr(md5(uniqid(mt_rand(), true)), 4, 8);
			$h = password_hash($p, PASSWORD_DEFAULT);
			$sql = "UPDATE users set pw = '$h' where account_ref = '$id'";
			$conn->query($sql);
			//ini_set('SMTP','192.168.0.21');
			//ini_set('sendmail_from','despatch@prestigeleisure.com');
			//ini_set('smtp_port',25);
			mail($hash['E_MAIL'], 'Myles Bros Online password reset', 'Hello! Your Myles Bros Ltd password is '.$p.', please keep this safe!', "From: info@mylesbros.co.uk", '-f info@mylesbros.co.uk');
			echo "Your password has been emailed to your email address. Please keep this password safe.";
			exit;
		}
		if (password_verify($pw, $hash['PW']))
		{
      echo $hash['NAME'].' logged in successfully.';
			unset($hash['PW']);
			$_SESSION['logged_in'] = $hash;
			$_SESSION['logged_in']['REF'] = '';
			$_SESSION['logged_in']['UUID'] = '';
      $selector = $authenticator = null;
      if ( $_POST['r'] == 'true' ) {
        $selector = base64_encode(substr(md5(uniqid(mt_rand(), true)), 4, 13));
        $authenticator = substr(md5(uniqid(mt_rand(), true)), 4, 37);
        setcookie('remember_me', $selector.':'.base64_encode($authenticator), time() + 864000, '/');
  			$update = "UPDATE users SET last_login = null, selector = '".$selector."', token = '".hash('sha256', $authenticator)."' WHERE account_ref = '".$hash['ACCOUNT_REF']."';";
  			$conn->query($update);
      }
      $sql = "UPDATE users SET last_login = null WHERE account_ref = '".$hash['ACCOUNT_REF']."';";
      $conn->query($sql);
			$sql = "SELECT order_date, ref, order_lines, uuid from downstreamHeaders where customer = '".$hash['ACCOUNT_REF']."' and status = 0;"; // see also engine.php
			$result = $conn->query($sql);
			while ($row = mysqli_fetch_assoc($result)) {
			$_SESSION['saved_orders'][] = $row;
			}
		} else
		{
			echo 'Incorrect password for '.$hash['NAME'].'.';
			unset($_SESSION['logged_in']);
		}
	} else
	{
		echo 'Please contact Myles Bros to open an account.';
		unset($_SESSION['logged_in']);
	}
mysqli_free_result($result);
mysqli_close($conn);
?>
