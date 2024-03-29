<?php
date_default_timezone_set('Europe/London');
session_start();
require_once('rm.php');
$basket_qty = @count($_SESSION['order_items']);
$admin = @$_SESSION['logged_in']['ADMIN'] == 1;
if ( empty($_SESSION['categories']) ) {
  $file = fopen("pricelist.dat", "r");
  while ( ! feof($file) ) {
    $line = fgetcsv($file);
    $catArray[] = $line[8];
  }
sort($catArray);
$_SESSION['categories'] = array_values(array_unique($catArray, SORT_REGULAR));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Myles Bros Ltd</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/search/css/bootstrap.min.css">
  <link href="/search/css/bootstrap-tour.min.css" rel="stylesheet">
  <link href="http://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
  <link href="http://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
  <script src="search/js/bootstrap.min.js"></script>
  <script src="search/js/bootstrap-tour.min.js"></script>
  <script src="search/js/typeahead.js"></script>
<style>
// * { border: 1px solid black; }
.jumbotron {
    background-color: #3498db; /* Peter River */
    color: #ffffff;
    font-family: Montserrat, sans-serif;
}
.container {
font: 400 15px Lato, sans-serif;
}
.rowNoMargin {
    margin-left: 0 !important;
    margin-right: 0 !important;
}
    .form-control-inline {
    min-width: 0;
    width: auto;
    display: inline;
    }
        .aSpinEdit{
    width: 80px;
    }
.table tbody>tr>td{
    vertical-align: middle;
}
.glyphlink{
color: #3498db;
}
.jumbosmallmargin{
margin-bottom: 20px;
}
#logger, #passwd, #user {
width: 30%;
}
#logger *{
//margin-top: 10px;
}
.glyphfav {
font-size:1.2em;
}
.pwError {
  color: red;
  font-weight: bold;
}
.oldPWError::-webkit-input-placeholder {
    color: red;
}
.sticky {
  background-color: #3498db;
  padding: 2px 0px;
  position: fixed;
  top: 0;
  z-index: 100;
}
.stickyfox {
  width: 100%;
}
.tt-menu {
  background-color: #fff;
  border: 1px solid #ccc;
}
.tt-suggestion {
  padding: 6px 12px;
}
.tt-selectable:hover{
  cursor: pointer;
}
.scrollable-menu {
    height: auto;
    max-height: 200px;
    overflow-x: hidden;
}
#SOButton{
  background-color: red;
  border-radius: 4px;
  cursor: pointer;
  display: block;
  padding: 5px;
  position: fixed;
  top: 45%;
  right: 0;
  white-space: nowrap;
  writing-mode: vertical-rl;
}
.history{
  display: none !important;
}
</style>
<script type="text/javascript">
$(document).ready(function(){

//typeahead>>
function typeaheadOn() {

  // Constructing the suggestion engine
  var customers = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.whitespace,
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      prefetch: 'search/engine.php?action=__customers'
  });

  // Initializing the typeahead
  $('.typeahead').typeahead({
      hint: true,
      highlight: true, /* Enable substring highlighting */
      minLength: 1 /* Specify minimum characters required for showing suggestions */
  },
  {
      name: 'customers',
      source: customers
  });

}
//typeahead<<

var featured = new Tour({
  steps: [
    {
      element: "#catdd",
      title: "Now search via category!",
      content: "List all items in a category."
    }
  ]
});

var tour = new Tour({
  steps: [
  {
    element: "#searchText",
    title: "Search",
    content: "Search our database for items... for example 'MUGS'",
    placement: "bottom",
    onShown: function (tour) {if ($("#searchText").val() == '') $("#searchText").val("MUGS")}
  },
  {
    element: ".input-group-btn",
    title: "Filter",
    content: "You can filter your results."
  },
  {
    element: "#searchButton",
    title: "GO!",
    content: "Hit the button!",
    placement: "bottom",
    onShown: function (tour) {$("#searchButton").click()}
  },
  {
    element: "#resultsDiv",
    title: "Results",
    content: "Here you will find your search results",
    placement: "top",
    onShown: function (tour) {$("#resultsDiv input:first").addClass("firstfortest")}
  },
  {
    element: ".firstfortest",
    title: "Add to trolley",
    content: "Simply change the quantity to automatically add it to your trolley",
    placement: "right",
    onShown: function (tour) {$("#resultsDiv input:first").val(6).change();}
  },
  {
    element: "#badge",
    title: "Trolley",
    content: "Click here to review your order",
    placement: "left",
    onShown: function (tour) {$("#badge").click()}
  },
  {
    element: "#refresh",
    title: "Refresh your trolley",
    content: "Any changes to quantities you make here will be reflected on your order, but may not be shown here... if you do make changes, please refresh your trolley.<br><br>PRO-TIP - want to remove an item? Change the quantity to 0",
    placement: "left"
  },
  {
    element: "#conan",
    title: "Abandon order!",
    content: "Click here to empty your trolley - THIS IS NON REVERSABLE, PLEASE BE CAREFUL!",
    placement: "left"
  },
  {
    element: "#orderSubmit",
    title: "Submit your order",
    content: "Fill in your details and your order will be placed.<br><br><strong>Additional points</strong><br><br>Your order will remain in your trolley until you close your browser, but should remain if you navigate away and come back. Please forward any comments or feedback to <a href='mailto:info@mylesbros.co.uk'>info@mylesbros.co.uk</a>"
  }
]});

  function SOButton(e) {
    orig = $("#filter").data("field");  
    $("#filter").data("field", 8);
    $("#searchText").val(e);
    $("#searchButton").click();
    $("#filter").data("field", orig);    
  }
  $(".special-button").click(function(){
    SOButton($(this).data("term"));
  });
	$("li > a[id^=filter-]").click(function(){
		if ( $(this).attr('class') == 'special-category' ) {
    SOButton($(this).data("term"));
		} else {
		$("#filter").text(this.text).data("field", $(this).data("field"));
		}
	$("#searchText").select();
	});
  $(".category").click(function(){
    orig = $("#filter").data("field");
    $("#searchText").val(this.text);
    $("#filter").data("field", 8);
    $("#searchButton").click();
    $("#filter").data("field", orig);
  });
	function ajaxSend(ele, act, flag) {
		 $.get( "/search/engine.php", { action: act, q: $("#searchText").val(), g: $("#filter").data("field"), aon: $(ele).data("aon"), qty: $(ele).val(), sku: $(ele).data("sku"), customer: $('#shopname').val(), email: $('#emailaddress').val(), order_number: $('#ordernumber').val(), special_instructions: $('#specialinstructions').val(), f: $(ele).attr('id'), fav: (!$(ele).hasClass('glyphicon-star')), uuid: $(ele).data('uuid'), ref: $(ele).html(), oref: $(ele).data('ref') } ).done(function( data ) {
    		if ( flag == 1 ) {
    			$( "#resultsDiv" ).html( data );
    		} else if ( flag == 0 ) {
    			obj = JSON.parse(data);
    			$("#basket_qty").html(obj.count);
    			if ( obj.message ) $( "#resultsDiv" ).html(obj.message);
    		}
    		if ( act == '_fav' ) {
    		$(ele).toggleClass('glyphicon-star glyphicon-star-empty');
    		if ( data != 1 ) alert('oops, there looks to be something wrong with the favourites engine... please let use know!');
    		}
    		if ( act == '_save' ) {
    		console.log(data);
    		if ( data == 1 ) {
    			alert('order successfully saved');
    			location.reload();
    		}
    		}
    		if ( act == '_savedDelete' || act == '_orderRef' ) {
    		console.log(data);
    		location.reload();
    		}
    		if ( act == '_savedLoad' ) {
    		console.log(data);
    		$('#badge').click();
    		}
    		if ( act == '_startNewOrder' ) {
    		console.log(data);
    		location.reload();
    		}
    	});
	}
    $("#searchButton").click(function() {
    	if ( $('#searchText').val() == '' ) {
    	    	$('#searchText').attr('placeholder', 'please enter a search term');
    	} else	{
    		$(this).addClass('btn-warning');
    		ajaxSend(this, '_search', 1);
    			}
    });
    $("#login").click(function(e){
    	e.preventDefault();
    	$("#logger").slideToggle("slow");
    });
    $('#resultsDiv').on('click', '.toAdd', function(){
    	var prod = $(this).attr('id');
    	var qty = $("#spinner-"+prod).val();
    	alert(qty + prod);
    });
    $('#resultsDiv').on('click', '#showAll', function(e){
       	e.preventDefault();
    	ajaxSend(this, '_search', 1);
    });
    $("#searchText").on("keyup", function(e) {
    if ( e.which === 13 ) $("#searchButton").click();
    });
    $('#resultsDiv').on('change', '.aSpinEdit', function(){
    	ajaxSend(this, '_add', 0);
    });
    $("#trolley").click(function(e){
    	e.preventDefault();
    	ajaxSend($(this), '_trolley', 1);
    });
    $('#resultsDiv').on('click', '#conan', function(){
    	ajaxSend(this, '_destroy', 0);
    	$("#trolley").click();
    });
    $('#searchText').click(function(){
    	this.select();
    });
    $('#resultsDiv').on('click', '[id^=spinner-]', function(){
    	this.select();
    });
    $('#resultsDiv').on('click', '#refresh', function(){
    	$('#badge').click();
    });
    $('#resultsDiv').on('click', '#save', function(e){
      e.preventDefault();
    	console.log('saved');
    	ajaxSend($(this), '_save', 2);
    });
    $('#resultsDiv').on('click', '#orderSubmit', function(){
    if ( ! <?php echo ( is_array(@$_SESSION['logged_in']) && count(@$_SESSION['logged_in']) == 10 ) ? 'true' : 'false' ?> ) {
        alert('Please log in before submitting your order!')
        $("html, body").animate({ scrollTop: 0 }, "slow");
        $("#login").click();
        return false;
    }
    if ( $('#shopname').val() && $('#emailaddress').val() ) {
    	$('#orderSubmit').addClass('btn-warning');
    	ajaxSend($(this), '_order', 0);
    }
    });
    $('#resultsDiv').on('click', '.glyphfav', function(){
    	console.log($(this).attr('id'));
    	ajaxSend($(this), '_fav', 2);
    });
    $('#acc_fav').click(function(e){
    	e.preventDefault();
    	ajaxSend($(this), '_favlist', 1);
    });
    $('#help').click(function(){
    if ( tour ) {
      tour.restart();
    } else	{
      tour.init(true);
      tour.start(true);
    }
    });
    $("#chpasswd").click(function(){
    $("#oldPw").removeClass('oldPWError');
    $.post( "/search/engine.php", {action: '_chpasswd', o: $("#oldPw").val(), n: $("#NewPw").val(), c: $("#CNewPw").val()} ).done(function(data){
      console.log(data);
      data = JSON.parse(data);
      $.each(data, function(index, item) {
        addOrRemove = ( $('#'+index).length && item === 0 );
        $('#'+index).toggleClass('pwError', addOrRemove);
        if ( index === 'oldPW' && item === 0 ) {
          $("#oldPw").val('').attr("placeholder", "Incorrect Old Password").addClass('oldPWError');
        }
      });
      if ( data.pwCh === 1 ) {
        alert('Password successfully changed!');
        location.reload();
      }
    });
    });
    $("#resetpw").click(function(){
      if ($("#user").val() == '') {
        alert('Please fill in Account Ref');
        return;
      }
      $.post( "/search/engine.php", {action: '__reset', user: $("#user").val()} ).done(function(data){
        console.log(data);
        if ( data == 1 ) alert('Password successfully cleared (if account exists!)\n\nPlease issue new password, or customer can do this themselves!');
        $("#user").val('');
      });
    });
    $("#verify").click(function(){
    $.post( "/search/l.php", {u: $("#ver-u").val(), p: $("#ver-p").val(), r: $("#remember_me").prop('checked')} ).done(function(data){
    alert(data);
    location.reload();
    });
    });
    $("#log-out").click(function(){
    $.get( "/search/engine.php", { action: '_lo' }).done(function( data ) {
    alert(data);
    location.reload();
    });
    });
    $(".savedLoad, .savedDelete").click(function(e){
    e.preventDefault();
    if ( $(this).hasClass('savedLoad') ) {
    console.log('load ' +$(this).data('uuid'));
    ajaxSend(this, '_savedLoad', 0);
    }
    if ( $(this).hasClass('savedDelete') ) {
    if ( confirm("are you sure you want to delete this order?") ) {
    console.log($(this).data('uuid'));
    ajaxSend(this, '_savedDelete', 2);
    }
    }
    });
    $(".updateOrderNumber").click(function(){
    console.log('click');
    $(this).data("initialText", $(this).html());
    document.execCommand('selectAll',false,null);
    }).blur(function(){
    console.log('blur');
    if ($(this).data("initialText") !== $(this).html() && $(this).html() !== '') {
    console.log(this);
    ajaxSend(this, '_orderRef', 2);
    } else if ($(this).html() == '') {
    $(this).html($(this).data("initialText"));
    }
    });
    $("#startNewOrder").click(function(e){
    e.preventDefault();
    ajaxSend(this, '_startNewOrder', 2);
    });
    $("#viewOrderHistory").click(function(e){
    e.preventDefault();
    ajaxSend(this, '_viewOrderHistory', 2);
    location.reload();
    });
    function getStuck() {
      var fox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
      if ( window.pageYOffset > sticky ) {
        header.classList.add("sticky");
        if ( fox ) header.classList.add("stickyfox");
      } else {
        header.classList.remove("sticky");
        header.classList.remove("stickyfox");
      }
    }
    window.onscroll = function() {getStuck()};
    var header = document.getElementById("stick");
    var sticky = header.offsetTop;
    $('#ad').click(function() {
    typeaheadOn();
    $("#emulateuser").click(function(e){
    e.preventDefault();
    $.post( "/search/engine.php", {action: '__emulate', user: $("#emulate").val()} ).done(function(data){
    alert('Now emulating ' + data);
    location.reload();
    });
    });    
    });
//featured.init(true);
//featured.start(true);
//setTimeout(function() { featured.end(true); }, 5000);
});
$(document).ajaxStop(function () {
$("#searchButton").removeClass('btn-warning');
});
</script>
</head>
<body>
<div style="overflow-x: hidden;">
<div class="jumbotron text-center jumbosmallmargin">
  <!--<span id="SOButton">Special Offers</span>-->
  <h1>Myles Bros Ltd</h1>
  <p>Wholesale Hardware Factors</p><br>
<div class="row rowNoMargin" id="stick">
  <div class="col-sm-6 col-sm-offset-3">
    <div class="input-group">
      <input type="text" class="form-control" aria-label="..." placeholder="search our database" id="searchText">
      <div class="input-group-btn">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span id="filter" data-field="3">Description</span> <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a id="filter-code" data-field="2" href="#">Code</a></li>
          <li><a id="filter-supplier" data-field="1" href="#">Supplier</a></li>
          <li><a id="filter-description" data-field="3" href="#">Description</a></li>
          <li><a id="filter-barcode" data-field="7" href="#">Barcode</a></li>
          <li role="separator" class="divider"></li>
          <li><a class="special-category" id="filter-specialoffers" data-term="SPECIALS" href="#">Special Offers</a></li>
          <li><a class="special-category" id="filter-clearance" data-term="CLEARANCE" href="#">Clearance</a></li>
          <li><a class="special-category" id="filter-new" data-term="NEW" href="#">New</a></li>
        </ul>
      </div><!-- /btn-group -->
    </div><!-- /input-group -->
  </div><!-- /.col-sm-6 col-sm-offset-3 -->
</div>
<br><!--<button type="button" class="btn btn-lg btn-success" id="SOButton" data-aon="1">Special Offers</button>-->
<button type="button" class="btn btn-lg btn-warning special-button" data-term="NEW"> New Products</button>
<button type="button" class="btn btn-lg btn-info special-button" data-term="CLEARANCE"> &nbsp;&nbsp;Clearance&nbsp;&nbsp;</button>
<div class="btn-group">
  <button type="button" class="btn btn-lg btn-success" id="searchButton" data-aon="1"><span class="glyphicon glyphicon-search"></span> Search</button>
  <button type="button" class="btn btn-lg btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <span class="caret"></span>
    <span class="sr-only" id="catdd">Toggle Dropdown</span>
  </button>
  <ul class="dropdown-menu scrollable-menu">
    <h6 class="dropdown-header">Categories</h6>
    <?php
    foreach($_SESSION['categories'] as $category){
      echo '<li><a class="category" href="#">'.$category.'</a></li>';
    }
    ?>
  </ul>   
</div>
<button type="button" class="btn btn-lg btn-danger special-button" data-term="SPECIALS"> Special Offers</button>
</div>
<div class="container">
<div style="float: right; text-align: right;"><a id="trolley" href="#"><span id="badge" class="badge" style="background-color: #3498db;"><span id="basket_qty"><?php echo $basket_qty;?></span>&nbsp;items in trolley&nbsp;<span class="glyphicon glyphicon-shopping-cart"></span></span></a></div>
<div style="float: left; width: 100%;"><a href="./"><span class="glyphicon glyphicon-home glyphlink"></span></a>&nbsp;&nbsp;
<?php if (! is_array(@$_SESSION['logged_in']) ) echo '<a href="#"><span id="login" class="glyphicon glyphicon-log-in glyphlink"></span></a>'; ?>
<h4><?php echo @$_SESSION['logged_in']['NAME'];?></h4>
<div style="width: 100%;">
<div id="logger" style="display: none;">
<input type="text" class="form-control" id="ver-u" placeholder="username">
<input style="margin-top: 10px;" type="password" class="form-control" id="ver-p" placeholder="password">
<a href="#" id="verify"><span class="badge" style="background-color: #3498db; margin-top: 10px;">Log in</span></a>
<div style="float: right; padding: 5px;"><input type="checkbox" id="remember_me">&nbsp;remember me</div>
</div>
</div>
</div>
<div id="resultsDiv" style="clear: both; padding-top: 1px; width: 100%;">
<?php
if (! is_array(@$_SESSION['logged_in']) ) {
echo <<<WELCOME
<div style="float: right;"><img width="250" src="/search/ht.jpg" alt="barrow" class="img-responsive"></div>
<h3>Welcome To Myles Brothers Ltd</h3>
<br><p>We are a 3rd generation family run wholesale hardware business with a firm focus on service.</p><p>Please click <a href="#" id="help">here</a> for a quick instructional tour of our ordering system.</p>
<br><br><img width="250" src="/search/brooms-857508_1280.jpg" alt="brooms" class="img-circle img-responsive">
</div>
WELCOME;
} else {
echo '<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#MyAccount">My Account</a></li>
  <li><a id="acc_fav" href="#">Favourite Products</a></li>
  <li><a data-toggle="tab" href="#ChangePassword">Change Password</a></li>';
if ( $admin ) echo '  <li class="pull-right"><a data-toggle="tab" href="#Admin" id="ad">Admin Tools</a></li>';
echo '</ul>
<br>
<div class="tab-content">
<div id="MyAccount" class="tab-pane fade in active">';
echo '<p>You have '.@count($_SESSION['saved_orders']).' saved orders</p><p>';
echo ( $_SESSION['logged_in']['REF'] !== '' && $_SESSION['logged_in']['UUID'] !== '' ) ? 'You are currently working on order reference - '.$_SESSION['logged_in']['REF'] : 'You aren\'t currently working on any orders';
echo '<p><a href="#" id="startNewOrder">Start New Order</a>&nbsp;|&nbsp<a href="#" id="viewOrderHistory">View Order History</a></p>';
echo '<div class="table-responsive"><table class="table table-hover">
<thead>
	<tr>
		<td>Order date</td><td>Order Reference</td><td>Number of lines</td><td>Load to trolley</td><td>Delete</td></tr></thead><tbody>
';
if ( @count($_SESSION['saved_orders']) ) {
foreach ( @$_SESSION['saved_orders'] as $order ) {
$savedDelete = isset($order['History']) ? 'history' : 'savedDelete';
echo '<tr><td>'.$order['order_date'].'</td><td contenteditable="true" class="updateOrderNumber" data-uuid="'.$order['uuid'].'">'.$order['ref'].'</td><td>'.$order['order_lines'].'</td><td><a href="#"><span data-ref="'.$order['ref'].'" data-uuid="'.$order['uuid'].'" class="savedLoad glyphicon glyphicon-floppy-open glyphlink"></span></a></td><td><a href="#"><span data-uuid="'.$order['uuid'].'" class="'.$savedDelete.' glyphicon glyphicon-floppy-remove glyphlink"></span></a></td></tr>';
}
}
echo '</tbody></table>
</div>
</div>';
echo '<div id="ChangePassword" class="tab-pane fade">
<h4>Change Password</h4>
<div style="float: right">
<h4>Passwords must</h4>
<ul>
  <li id="pwLen">be at least 8 digits in length</li>
  <li id="pwNum">include at least one number</li>
  <li id="pwCap">include at least one capital letter</li>
  <li id="pwMatch">New Password and Confirm New Password must match</li>
</ul>
<p>An email with your IP address will be sent to the address on your account upon password change</p>
</div>
<div id="passwd">
<input type="password" class="form-control" id="oldPw" placeholder="Old Password">
<input type="password" class="form-control" id="NewPw" placeholder="New Password" style="margin-top: 10px;">
<input type="password" class="form-control" id="CNewPw" placeholder="Confirm New Password" style="margin-top: 10px;">
<a href="#" id="chpasswd"><span class="badge" style="background-color: #5cb85c; margin-top: 10px;">Change Password</span></a><br><br>
</div>
</div>';
if ( $admin) echo '<div id="Admin" class="tab-pane fade">
<h4>Admin Tools</h4><br>
<div>
<h5>Password Reset</h5>
<input type="text" class="form-control" id="user" placeholder="Account Ref to reset">
<a href="#" id="resetpw"><span class="badge" style="background-color: #5cb85c; margin-top: 10px;">Reset Password</span></a><br><br>
</div>
<div>
<h5>Emulate Account</h5>
<input type="text" class="form-control typeahead" id="emulate" placeholder="Account Ref to emulate">&nbsp;
<a href="#" id="emulateuser"><span class="badge" style="background-color: #5cb85c; margin-top: 10px;">Emulate</span></a><br><br>
</div>';
echo '</div>';
echo '<a href="#" id="log-out"><span class="badge" style="background-color: #3498db; margin-top: 10px;">Log out</span></a></div>';
}
?>
</div><br>
<hr style="clear: both;">
<div class="container">
<div class="row">
		<div class="col-xs-6">
		<div>Contact</div><br>
		<span class="glyphicon glyphicon-envelope"></span> Sandy Myles - Director<!--<br><span class="glyphicon glyphicon-envelope"></span> Peter Myles - Director--><br><span class="glyphicon glyphicon-envelope"></span> Linsey McGill - Accounts<br><br>
		<div><span class="glyphicon glyphicon-earphone"></span> 01506-859158<br><span class="glyphicon glyphicon-print"></span> 01506-853618</div>
        </div>
        <div class="col-xs-6 text-right">
        <div>Office</div><br>
        Myles Bros Ltd<br>Unit 1, Greendykes Ind Est<br>Broxburn<br>West Lothian<br>EH52 6PG
        </div>
</div>
</div>
</div>
<hr style="clear: both;">
<?php
/*echo '<pre>';
print_r($_COOKIE);
print_r($_SESSION);
echo '</pre>';*/
?>
</body>
</html>
