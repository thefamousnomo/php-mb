<?php
if (!@is_array($_SESSION['logged_in']) && @$_COOKIE['remember_me'] !==':') {
    @list($selector, $authenticator) = explode(':', @$_COOKIE['remember_me']);

    $selector = substr($selector, 0, 12);

    $dbc = parse_ini_file("../../c.ini");
    $servername = $dbc['servername'];
    $username = $dbc['username'];
    $password = $dbc['password'];
    $dbname = $dbc['dbname'];
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error)
    {
       die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users where selector = '$selector';";
    $result = $conn->query($sql);
  	$hash = $result->fetch_assoc();

    if ( $hash['token'] === hash('sha256', base64_decode($authenticator)) ) {
      unset($hash['PW']);
      $_SESSION['logged_in'] = $hash;
      $_SESSION['logged_in']['REF'] = '';
      $_SESSION['logged_in']['UUID'] = '';
      $selector = $token = null;
      $selector = base64_encode(substr(md5(uniqid(mt_rand(), true)), 4, 13));
      $authenticator = substr(md5(uniqid(mt_rand(), true)), 4, 37);
      setcookie('remember_me', $selector.':'.base64_encode($authenticator), time() + 864000, '/');
      $update = "UPDATE users SET last_login = null, selector = '".$selector."', token = '".hash('sha256', $authenticator)."' WHERE account_ref = '".$hash['ACCOUNT_REF']."';";
      $conn->query($update);
      $sql = "SELECT order_date, ref, order_lines, uuid from downstreamHeaders where customer = '".$hash['ACCOUNT_REF']."' and status = 0;"; // see also engine.php 104
      $result = $conn->query($sql);
      while ($row = mysqli_fetch_assoc($result)) {
      $_SESSION['saved_orders'][] = $row;
    }
    }

mysqli_close($conn);
}
 ?>
