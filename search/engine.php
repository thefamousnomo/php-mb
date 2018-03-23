<?php
session_start();

/* --- logged in? */
function loggedIN() {
return ( is_array(@$_SESSION['logged_in']) && count(@$_SESSION['logged_in']) == 9 );
}
/* -- logged in? ends */

/* --- mysql conn obj */
function mysqlConnObj() {
if ( loggedIN() ) {
	$dbc = parse_ini_file("../../c.ini");
	$servername = $dbc['servername'];
	$username = $dbc['username'];
	$password = $dbc['password'];
	$dbname = $dbc['dbname'];
	return mysqli_connect($servername, $username, $password, $dbname);
} else {
	return false;
}
exit;
}
/* --- mysql conn obj end */

/* --- saved order to session */
function savedOrderToSession() {
unset($_SESSION['saved_orders']);
$conn = mysqlConnObj();
$sql = "SELECT order_date, ref, order_lines, uuid from downstreamHeaders where customer = '".$_SESSION['logged_in']['ACCOUNT_REF']."' and status = 0;"; // see also l.php 45
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)) {
	$_SESSION['saved_orders'][] = $row;
}
mysqli_close($conn);
}
/* --- save order to session ends */

/* --- return favourite function */
function favReturn($a, $b) {
if ( loggedIN() ) {
	$sql = "SELECT sku FROM favs where account_ref = '".$_SESSION['logged_in']['ACCOUNT_REF']."' and sku = '$b'";
	$result = @mysqli_query($a, $sql);
	return ( @mysqli_num_rows($result) > 0 ) ? 'glyphicon-star' : 'glyphicon-star-empty';
}
}
/* --- return favourite function ends */

/* --- log out function */
if ( $_GET['action'] == '_lo' ) {
echo 'You are now logged out.';
unset($_SESSION['logged_in']);
unset($_SESSION['saved_orders']);
setcookie('remember_me', null, -1, '/');
exit;
}
/* --- log out function ends */

/* --- start new order block */
if ( $_GET['action'] == '_startNewOrder' && loggedIN() ) {
$_SESSION['logged_in']['UUID'] = '';
$_SESSION['logged_in']['REF'] = '';
}
/* --- start new order block ends */

/* --- display function */
function arrayReturn($z, $head, $qCount=1) {
//
$conn = @mysqlConnObj();
//
@$searchMessage = ( $_GET['aon'] == 1 && $qCount > 1 ) ? ' best matches' : ' matches';
@$searchMessage = ( $head == 'Your Order' ) ? ' items in trolley' : $searchMessage;
$p = ( loggedIN() ) ? 4 : 5;
$m = ( loggedIN() ) ? 'Unit Price' : 'RRP';
echo "<h3>$head - ".count($z)."$searchMessage</h3><br><p>";
if ( loggedIN() ) {
echo ( @$_SESSION['logged_in']['REF'] !== '' && @$_SESSION['logged_in']['UUID'] !== ''  ) ? 'You are currently working on order reference - '.@$_SESSION['logged_in']['REF'].'</p>' : 'You aren\'t currently working on any orders</p>';
}
	if ( count($z) > 0 ) {
		$output = '<div class="table-responsive"><table class="table table-striped">
		<thead>
    		<tr>
    			<th>Product Image</th><th>Product Code</th><th>Description</th><th>Quantity</th><th>'.$m.'</th><th>In Stock</th><th></th>
    			</tr>
		</thead>
		<tbody>';
		foreach ( $z as $x ) {
		$ordered_qty = ( @array_key_exists($x[2], $_SESSION['order_items']) ) ? $_SESSION['order_items'][$x[2]] : 0;
			$output.= '<tr><td align="center"><img src="http://www.mylesbros.co.uk/search/images/th_'.strtolower($x[2]).'.jpg"></td><td align="center"><strong>'.$x[2].'</strong><br><br><img width="100" onerror="this.style.display = \'none\';" alt="Barcoded value '.$x[7].'" src="http://bwipjs-api.metafloor.com/?bcid=ean13&text='.$x[7].'&includetext"></td><td>'.$x[3].'<br><br>Barcode: '.$x[7].'</td><td><input type="number" class="aSpinEdit form-control form-control-inline" id="spinner-'.$x[2].'" value="'.$ordered_qty.'" min="0" data-sku="'.$x[2].'"></td><td>&pound;'.$x[$p].'</td><td>'.$x[6].'</td>';
			$output .= ( is_array(@$_SESSION['logged_in']) ) ? '<td><span id="'.$x[2].'" class="glyphicon '.favReturn($conn, $x[2]).' glyphlink glyphfav"></span></td></tr>' : '</tr>';
		}
		$output.='</tbody></table></div>';
		if ( @$_GET['aon'] == 1 && $qCount > 1 ) $output.='<br<br><a href="#" style="float: right;" data-aon="0" id="showAll">show all</a>';
		return $output;
	}
mysqli_close($conn);
}
/* --- display function ends */

/* --- favourite block */
if ( $_GET['action'] == '_fav' && loggedIN() ) {
$conn = @mysqlConnObj();
$sql = ( $_GET['fav'] == 'true' ) ? "INSERT INTO favs (ACCOUNT_REF, SKU) VALUES ('".$_SESSION['logged_in']['ACCOUNT_REF']."', '".$_GET['f']."')" : "DELETE FROM favs where ACCOUNT_REF ='".$_SESSION['logged_in']['ACCOUNT_REF']."' AND SKU ='".$_GET['f']."';";
$result = @mysqli_query($conn, $sql);
echo $result;
//mysqli_free_result($result);
mysqli_close($conn);
exit;
}
/* --- favourite block ends */

/* --- save block */
if ( $_GET['action'] == '_save' && loggedIN() ) {
$conn = @mysqlConnObj();
unset($_SESSION['saved_orders']);
if ( ! empty($_SESSION['logged_in']['UUID']) ) {
$uuid = $_SESSION['logged_in']['UUID'];
$ref = $_SESSION['logged_in']['REF'];
$sql = "DELETE FROM downstreamHeaders where uuid = '".$uuid."';";
$result = @mysqli_query($conn, $sql);
$sql = "DELETE FROM downstreamLines where uuid = '".$uuid."';";
$result = @mysqli_query($conn, $sql);
//$_SESSION['logged_in']['UUID'] = '';
} else {
$uuid = $_SESSION['logged_in']['ACCOUNT_REF'].date('Ymdhis');
$ref = $uuid;
}
$sql = "INSERT INTO downstreamHeaders (uuid, customer, ref, order_lines) VALUES ('".$uuid."', '".$_SESSION['logged_in']['ACCOUNT_REF']."', '".$ref."', '".count($_SESSION['order_items'])."'); ";
$resultH = @mysqli_query($conn, $sql);
$sql = "INSERT INTO downstreamLines (customer, uuid, qty, code) VALUES ";
$i = 1;
foreach ( $_SESSION['order_items'] as $k => $v ) {
$sql .= "('".$_SESSION['logged_in']['ACCOUNT_REF']."', '".$uuid."', $v, '".$k."')";
$sql .= ( $i == count($_SESSION['order_items']) ) ? ';' : ', ';
$i++;
}
$resultL = @mysqli_query($conn, $sql);
echo ( $resultH && $resultL );
$sql = "SELECT order_date, ref, order_lines, uuid from downstreamHeaders where customer = '".$_SESSION['logged_in']['ACCOUNT_REF']."' and status = 0;"; // see also l.php
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)) {
$_SESSION['saved_orders'][] = $row;
}
mysqli_close($conn);
unset($_SESSION['order_items']);
exit;
}
/* --- save block ends */

/* --- saved order delete block */
if ( $_GET['action'] == '_savedDelete' && loggedIN() && !empty($_GET['uuid']) ) {
$conn = @mysqlConnObj();
$sql = "DELETE FROM downstreamHeaders where uuid = '".$_GET['uuid']."' and customer = '".$_SESSION['logged_in']['ACCOUNT_REF']."';";
$result = @mysqli_query($conn, $sql);
mysqli_close($conn);
savedOrderToSession();
$_SESSION['logged_in']['UUID'] = '';
$_SESSION['logged_in']['REF'] = '';
exit;
}
/* --- saved order delete block ends */

/* --- order ref update block */
if ( $_GET['action'] == '_orderRef' && loggedIN() && !empty($_GET['uuid']) ) {
$conn = @mysqlConnObj();
$sql = "UPDATE downstreamHeaders set ref = '".$_GET['ref']."' where uuid = '".$_GET['uuid']."';";
$result = @mysqli_query($conn, $sql);
mysqli_close($conn);
savedOrderToSession();
$_SESSION['logged_in']['REF'] = $_GET['ref'];
exit;
}
/* --- order ref update block ends */

/* --- favourite list block */
if ( $_GET['action'] == '_favlist' && loggedIN() ) {
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

/* --- saved load block */
if ( $_GET['action'] == '_savedLoad' && loggedIN() && !empty($_GET['uuid']) ) {
unset($_SESSION['order_items']);
$conn = @mysqlConnObj();
$sql = "SELECT code, qty from downstreamLines where uuid = '".$_GET['uuid']."';";
$result = @mysqli_query($conn, $sql);
while ( $row = mysqli_fetch_array($result) ) {
$_SESSION['order_items'][$row['code']] = $row['qty'];
}
$_SESSION['logged_in']['REF'] = $_GET['oref'];
$_SESSION['logged_in']['UUID'] = $_GET['uuid'];
mysqli_close($conn);
echo json_encode(array('count' => count($_SESSION['order_items'])));
exit;
}
/* --- saved load block ends */

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
if ( loggedIN() ) echo '<a href="#" id="save" style="float: right;"><span class="badge" style="background-color: #5cb85c;">save trolley</span></a>';
//echo '<a style="float: right;" href="#" id="refresh">refresh trolley</a>';
echo '<br><br><a href="#" id="refresh" style="float: right;"><span class="badge" style="background-color: #3498db;">refresh trolley</span></a>';
//echo '<br><br><a style="float: right;" href="#" id="conan">empty trolley</a>';
echo '<br><br><a href="#" id="conan" style="float: right;"><span class="badge" style="background-color: #c0392b;">empty trolley</span></a>';
$shopname = @$_SESSION['logged_in']['NAME'];
$shopemail = @$_SESSION['logged_in']['E_MAIL'];
$ref = @$_SESSION['logged_in']['REF'];
echo <<<HIDDENDIV
<table>
<tr><td style="clear: both; text-align: right;">Shop Name:&nbsp;&nbsp;</td><td><input id="shopname" type="text" class="form-control form-control-inline" name="details[]" value="$shopname"></td>
</tr>
<tr><td style="text-align: right;">Email Address:&nbsp;&nbsp;</td><td><input id="emailaddress" type="text" class="form-control form-control-inline" name="details[]" value="$shopemail"></td>
</tr>
<tr><td style="text-align: right;">Order Ref:&nbsp;&nbsp;</td><td><input id="ordernumber" type="text" class="form-control form-control-inline" name="details[]" value="$ref"></td>
</tr>
<tr><td style="text-align: right;vertical-align:text-top;">Special Instructions:&nbsp;&nbsp;</td><td><textarea id="specialinstructions" class="form-control form-control-inline" name="details[]" rows="5"></textarea></td>
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
	/*ini_set('SMTP','192.168.0.21');
	ini_set('sendmail_from','despatch@prestigeleisure.com');
	ini_set('smtp_port',25);
	$debug[] = @$_SERVER['REQUEST_TIME'];
	$debug[] = @$_SERVER['HTTP_USER_AGENT'];
	$debug[] = @$_SERVER['REMOTE_ADDR'];
	$debug[] = @$_SERVER['REMOTE_HOST'];
	$email = "----- debug block -----\r\n";
	$email .= print_r($debug, true);
	$email .= "----- customer details -----\r\n";
	$email .= print_r($_GET, true);
	$email .= "----- order items -----\r\n";
	$email .= print_r($_SESSION['order_items'], true);
	*/
	$email = 'Name: ' . $_GET['customer']."\r\n";
	$email .= 'Email: ' . $_GET['email']."\r\n\r\n";
	$email .= 'Order Number: ' . $_GET['order_number']."\r\n\r\n";
	$email .= 'Special Instructions: ' . $_GET['special_instructions']."\r\n\r\n";
	$email .= "Order details below: \r\n\r\n";
	foreach ( $_SESSION['order_items'] as $key => $value ) {
	$email .= $key.' - '.$list[$key]." x $value\r\n";
	}
if ( LoggedIN() ) {
$conn = @mysqlConnObj();
$sql = "UPDATE downstreamHeaders set status = 2 where uuid = '".$_SESSION['logged_in']['UUID']."';";
$result = @mysqli_query($conn, $sql);
mysqli_close($conn);
$_SESSION['logged_in']['REF'] = '';
$_SESSION['logged_in']['UUID'] = '';
savedOrderToSession();
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
