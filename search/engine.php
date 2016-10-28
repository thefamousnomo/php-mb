<?php
session_start();
/* --- mysql conn obj */
function mysqlConnObj() {
$dbc = parse_ini_file("../../c.ini");
$servername = $dbc['servername'];
$username = $dbc['username'];
$password = $dbc['password'];
$dbname = $dbc['dbname'];
return mysqli_connect($servername, $username, $password, $dbname);
}
/* --- mysql conn obj end */

/* --- return favourite function */
function favReturn($a, $b) {
$sql = "SELECT sku FROM favs where account_ref = '".$_SESSION['logged_in']['ACCOUNT_REF']."' and sku = '$b'";
$result = @mysqli_query($a, $sql);
return ( @mysqli_num_rows($result) > 0 ) ? 'glyphicon-star' : 'glyphicon-star-empty';
}
/* --- return favourite function ends */

/* --- log out function */
if ( $_GET['action'] == '_lo' ) {
echo 'You are now logged out.';
unset($_SESSION['logged_in']);
exit;
}
/* --- log out function ends */

/* --- display function */
function arrayReturn($z, $head, $qCount=1) {
//
$conn = @mysqlConnObj();
//
@$searchMessage = ( $_GET['aon'] == 1 && $qCount > 1 ) ? ' best matches' : ' matches';
@$searchMessage = ( $head == 'Your Order' ) ? ' items in trolley' : $searchMessage;
$p = ( is_array(@$_SESSION['logged_in']) ) ? 4 : 5;
$m = ( is_array(@$_SESSION['logged_in']) ) ? 'Unit Price' : 'RRP';
echo "<h3>$head - ".count($z)."$searchMessage</h3><br>";
	if ( count($z) > 0 ) {
		$output = '<table class="table table-striped">
		<thead>
    		<tr>
    			<th>Product Image</th><th>Product Code</th><th>Description</th><th>Quantity</th><th>'.$m.'</th><th>In Stock</th><th></th>
    			</tr>
		</thead>
		<tbody>';
		foreach ( $z as $x ) {
		$ordered_qty = ( @array_key_exists($x[2], $_SESSION['order_items']) ) ? $_SESSION['order_items'][$x[2]] : 0;
			$output.= '<tr><td align="center"><img src="http://www.mylesbros.co.uk/search/images/th_'.strtolower($x[2]).'.jpg"></td><td>'.$x[2].'</td><td>'.$x[3].'</td><td><input type="number" class="aSpinEdit form-control form-control-inline" id="spinner-'.$x[2].'" value="'.$ordered_qty.'" min="0" data-sku="'.$x[2].'"></td><td>&pound;'.$x[$p].'</td><td>'.$x[6].'</td>';
			$output .= ( is_array(@$_SESSION['logged_in']) ) ? '<td><span id="'.$x[2].'" class="glyphicon '.favReturn($conn, $x[2]).' glyphlink glyphfav"></span></td></tr>' : '</tr>';
		}
		$output.='</tbody></table>';
		if ( @$_GET['aon'] == 1 && $qCount > 1 ) $output.='<br<br><a href="#" style="float: right;" data-aon="0" id="showAll">show all</a>';
		return $output;
	}
mysqli_close($conn);
}
/* --- display function ends */

/* --- favourite block */
if ( $_GET['action'] == '_fav' ) {
$conn = @mysqlConnObj();
$sql = ( $_GET['fav'] == 'true' ) ? "INSERT INTO favs (ACCOUNT_REF, SKU) VALUES ('".$_SESSION['logged_in']['ACCOUNT_REF']."', '".$_GET['f']."')" : "DELETE FROM favs where ACCOUNT_REF ='".$_SESSION['logged_in']['ACCOUNT_REF']."' AND SKU ='".$_GET['f']."';";
$result = @mysqli_query($conn, $sql);
echo $result;
//mysqli_free_result($result);
mysqli_close($conn);
exit;
}
/* --- favourite block ends */

/* --- favourite list block */
if ( $_GET['action'] == '_favlist' ) {
$conn = @mysqlConnObj();
$sql = "SELECT sku FROM favs where account_ref = '".$_SESSION['logged_in']['ACCOUNT_REF']."';";
$result = @mysqli_query($conn, $sql);
if ( mysqli_num_rows($result) == 0 ) { echo 'No Favourites to show'; exit; }
while($row = mysqli_fetch_array($result)){
    $favourites[] = $row[0];
}
mysqli_free_result($result);
mysqli_close($conn);
$file = fopen("pricelist.dat", "r");
while ( ! feof($file) ) {
	$line = fgetcsv($file);
	if ( in_array($line[2], $favourites) ) $favList[]=$line;
}
echo arrayReturn($favList, 'Favourites List');
exit;
}
/* --- favourite list block ends */

/* --- search block */
if ( $_GET['action'] == '_search' ) {

$q = strtoupper($_GET['q']);
$q = explode(' ', $q);
$g = $_GET['g'];
$file = fopen("pricelist.dat", "r");

function searchQuery($haystack, $needle) {
	global $results, $g;
	array_walk($needle, function(&$value, &$key){
	$value = (strlen($value)>3) ? '/('.rtrim($value, 'S').')(S*)/' : '/('.$value.')/';
	});
	preg_replace($needle, '$1$2', $haystack[$g], 1, $count);
	if ( $count > 0 && $count == count($needle) && $_GET['aon'] == 1 ) {
		$haystack[] = $count;
		$results[] = $haystack;
	} elseif ( $count > 0 && $count <= count($needle) && $_GET['aon'] == 0 ) {
		$haystack[] = $count;
		$results[] = $haystack;
	}

}

$q = array_filter($q, function($value){
	global $ltt;
	if ( strlen($value) < 3 ) $ltt[] = $value;
	return ($value&&strlen($value)>2);
});

/* --- restricted characters block */
array_walk($q, function(&$value){
$restrictedChars=array('/', '*');
foreach ($restrictedChars as $char) {
$replacementChars[]='\\'.$char;
}
$value = str_replace($restrictedChars, $replacementChars, $value);
});
/* --- restricted characters block ends */

while ( ! feof($file) ) {
	$line = fgetcsv($file);
	searchQuery($line, $q);
}

function removedTerms($ltt) {
	return ( is_array($ltt) ) ? '<i>terms '.implode($ltt, ', ').' removed from search</i>' : '';
}

if ( count($results) == 0 ) {
echo removedTerms($ltt);
echo "<h3>Sorry, no matches found</h3>";
echo '<br<br><a href="#" style="float: right;" data-aon="0" id="showAll">show all</a>';
exit;
}

//usort($results, function($a, $b) { relevance engine
//return $a[7] < $b[7];
//});

usort($results, function($a, $b) {
return $a[2] > $b[2];
});

echo removedTerms($ltt);
echo arrayReturn($results, 'Search Results', count($q));
//print_r($results);

fclose($file);
exit;
}
/* --- search block ends */

/* --- add to order block */
if ( $_GET['action'] == '_add' ) {
	if ( $_GET['qty'] == 0 ) {
		if ( @array_key_exists($_GET['sku'], $_SESSION['order_items']) ) unset($_SESSION['order_items'][$_GET['sku']]);
	} else	{
			$_SESSION['order_items'][$_GET['sku']]=$_GET['qty'];
			}
echo json_encode(array('count' => count($_SESSION['order_items'])));
exit;
}
/* --- add to order block ends */

/* --- trolley display block */
if ( $_GET['action'] == '_trolley' ) {
if ( @count($_SESSION['order_items']) == 0 )  {
echo '<h3>No items in trolley</h3>';
exit;
}
$file = fopen("pricelist.dat", "r");
while ( ! feof($file) ) {
	$line = fgetcsv($file);
	if ( array_key_exists($line[2], $_SESSION['order_items']) ) {
	$line[]=0;
	$results[]=$line;
	}
}
echo arrayReturn($results, 'Your Order', 0);
echo '<a style="float: right;" href="#" id="refresh">refresh trolley</a>';
echo '<br><br><br><a style="float: right;" href="#" id="conan">empty trolley</a>';
$shopname = @$_SESSION['logged_in']['NAME'];
$shopemail = @$_SESSION['logged_in']['E_MAIL'];
echo <<<HIDDENDIV
<table>
<tr><td style="clear: both; text-align: right;">Shop Name:&nbsp;&nbsp;</td><td><input id="shopname" type="text" class="form-control form-control-inline" name="details[]" value="$shopname"></td>
</tr>
<tr><td style="text-align: right;">Email Address:&nbsp;&nbsp;</td><td><input id="emailaddress" type="text" class="form-control form-control-inline" name="details[]" value="$shopemail"></td>
</tr>
<tr><td colspan="2" style="text-align: right;"><br><button class="btn btn-success" id="orderSubmit">Submit Order</button></td>
</tr></table>
HIDDENDIV;
fclose($file);
exit;
}
/* --- trolley display block ends */

/* --- destroy block begins */
if ( $_GET['action'] == '_destroy' ) {
	unset($_SESSION['order_items']);
	echo json_encode(array('count' => 0));
	exit;
}
/* --- destroy block end */

/* --- order block begins */
if ( $_GET['action'] == '_order' ) {
$file = fopen("pricelist.dat", "r");
while ( ! feof($file) ) {
	$line = fgetcsv($file);
	$list[$line[2]]=$line[3];
}
fclose($file);
	//ini_set('SMTP','192.168.0.21');
	//ini_set('sendmail_from','despatch@prestigeleisure.com');
	//ini_set('smtp_port',25);
	//$debug[] = @$_SERVER['REQUEST_TIME'];
	//$debug[] = @$_SERVER['HTTP_USER_AGENT'];
	//$debug[] = @$_SERVER['REMOTE_ADDR'];
	//$debug[] = @$_SERVER['REMOTE_HOST'];
	//$email = "----- debug block -----\r\n";
	//$email .= print_r($debug, true);
	//$email .= "----- customer details -----\r\n";
	//$email .= print_r($_GET, true);
	//$email .= "----- order items -----\r\n";
	//$email .= print_r($_SESSION['order_items'], true);
	$email = 'Name: ' . $_GET['customer']."\r\n";
	$email .= 'Name: ' . $_GET['email']."\r\n\r\n";	
	foreach ( $_SESSION['order_items'] as $key => $value ) {
	$email .= $key.' - '.$list[$key]." x $value\r\n";
	}
	if ( mail('orders@mylesbros.co.uk', 'Myles Bros Online Order', $email, "From: info@mylesbros.co.uk", '-f info@mylesbros.co.uk') ) {
		unset($_SESSION['order_items']);
		echo json_encode(array('count' => 0, 'message' => '<h3>Order submitted successfully</h3>'));
		} else	{
			echo json_encode(array('count' => count($_SESSION['order_items']), 'message' => '<h3>Issue with order</h3>'));
				}
exit;
}
/* --- order block end */
?>