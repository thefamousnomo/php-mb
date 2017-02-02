<?php
date_default_timezone_set('Europe/London');
session_start();
$basket_qty = @count($_SESSION['order_items']);
?>
<!DOCTYPE html>
<HTML LANG="en">
<HEAD>
  <TITLE>Myles Bros Ltd</TITLE>
  <META CHARSET="utf-8">
  <META NAME="viewport" CONTENT="width=device-width, initial-scale=1">
  <LINK REL="stylesheet" HREF="/search/css/bootstrap.min.css">
  <LINK HREF="/search/css/bootstrap-tour.min.css" REL="stylesheet">
  <LINK HREF="http://fonts.googleapis.com/css?family=Montserrat" REL="stylesheet" TYPE="text/css">
  <LINK HREF="http://fonts.googleapis.com/css?family=Lato" REL="stylesheet" TYPE="text/css">
  <SCRIPT SRC="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></SCRIPT>
  <SCRIPT SRC="search/js/bootstrap.min.js"></SCRIPT>
  <SCRIPT SRC="search/js/bootstrap-tour.min.js"></SCRIPT>
<STYLE>
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
#logger {
width: 30%;
}
#logger *{
//margin-top: 10px;
}
.glyphfav {
font-size:1.2em;
}
</STYLE>
<SCRIPT TYPE="text/javascript">
$(document).ready(function(){

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
    content: "Any changes to quantities you make here will be reflected on your order, but may not be shown here... if you do make changes, please refresh your trolley.<BR><BR>PRO-TIP - want to remove an item? Change the quantity to 0",
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
    content: "Fill in your details and your order will be placed.<BR><BR><STRONG>Additional points</STRONG><BR><BR>Your order will remain in your trolley until you close your browser, but should remain if you navigate away and come back. Please forward any comments or feedback to <A HREF='mailto:info@mylesbros.co.uk'>info@mylesbros.co.uk</A>"
  }
]});

	$("li > a[id^=filter-]").click(function(){
		if ( this.id == 'filter-specialoffers' ) {
		$("#searchText").val("*SO*");
		$("#searchButton").click();
		} else {
		$("#filter").text(this.text).data("field", $(this).data("field"));
		}
	$("#searchText").select();
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
    $("#verify").click(function(){
    $.post( "/search/l.php", {u: $("#ver-u").val(), p: $("#ver-p").val()} ).done(function(data){
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
});
$(document).ajaxStop(function () {
$("#searchButton").removeClass('btn-warning');
});
</SCRIPT>
</HEAD>
<BODY>
<DIV STYLE="overflow-x: hidden;">
<DIV CLASS="jumbotron text-center jumbosmallmargin">
  <H1>Myles Bros Ltd</H1>
  <P>Wholesale Hardware Factors</P><BR>
<DIV CLASS="row rowNoMargin">
  <DIV CLASS="col-sm-6 col-sm-offset-3">
    <DIV CLASS="input-group">
      <INPUT TYPE="text" CLASS="form-control" ARIA-LABEL="..." PLACEHOLDER="search our database" ID="searchText">
      <DIV CLASS="input-group-btn">
        <BUTTON TYPE="button" CLASS="btn btn-default dropdown-toggle" DATA-TOGGLE="dropdown" ARIA-HASPOPUP="true" ARIA-EXPANDED="false"><SPAN ID="filter" DATA-FIELD="3">Description</SPAN> <SPAN CLASS="caret"></SPAN></BUTTON>
        <UL CLASS="dropdown-menu dropdown-menu-right">
          <LI><A ID="filter-code" DATA-FIELD="2" HREF="#">Code</A></LI>
          <LI><A ID="filter-supplier" DATA-FIELD="1" HREF="#">Supplier</A></LI>
          <LI><A ID="filter-description" DATA-FIELD="3" HREF="#">Description</A></LI>
          <LI ROLE="separator" CLASS="divider"></LI>
          <LI><A ID="filter-specialoffers" HREF="#">Special Offers</A></LI>
        </UL>
      </DIV><!-- /btn-group -->
    </DIV><!-- /input-group -->
  </DIV><!-- /.col-sm-6 col-sm-offset-3 -->
</DIV>
<BR><BUTTON TYPE="button" CLASS="btn btn-lg btn-success" ID="searchButton" DATA-AON="1"><SPAN CLASS="glyphicon glyphicon-search"></SPAN> Search</BUTTON>
</DIV>
<DIV CLASS="container">
<DIV STYLE="float: right; text-align: right;"><A ID="trolley" HREF="#"><SPAN ID="badge" CLASS="badge" STYLE="background-color: #3498db;"><SPAN ID="basket_qty"><?php echo $basket_qty;?></SPAN>&nbsp;items in trolley&nbsp;<SPAN CLASS="glyphicon glyphicon-shopping-cart"></SPAN></SPAN></A></DIV>
<DIV STYLE="float: left; width: 100%;"><A HREF="./"><SPAN CLASS="glyphicon glyphicon-home glyphlink"></SPAN></A>&nbsp;&nbsp;
<?php if (! is_array(@$_SESSION['logged_in']) ) echo '<a href="#"><span id="login" class="glyphicon glyphicon-log-in glyphlink"></span></a>'; ?>
<H4><?php echo @$_SESSION['logged_in']['NAME'];?></H4>
<DIV STYLE="width: 100%;">
<DIV ID="logger" STYLE="display: none;">
<INPUT TYPE="text" CLASS="form-control" ID="ver-u" PLACEHOLDER="username">
<INPUT STYLE="margin-top: 10px;" TYPE="password" CLASS="form-control" ID="ver-p" PLACEHOLDER="password">
<A HREF="#" ID="verify"><SPAN CLASS="badge" STYLE="background-color: #3498db; margin-top: 10px;">Log in</SPAN></A>
</DIV>
</DIV>
</DIV>
<DIV ID="resultsDiv" STYLE="clear: both; padding-top: 1px; width: 100%;">
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
echo '<p>MY ACCOUNT</p>
<p>Click <a id="acc_fav" href="#">here</a> to list your favourite products.</p>';
echo '<p>You have '.@count($_SESSION['saved_orders']).' saved orders</p><p>';
echo ( $_SESSION['logged_in']['REF'] !== '' && $_SESSION['logged_in']['UUID'] !== '' ) ? 'You are currently working on order reference - '.$_SESSION['logged_in']['REF'] : 'You aren\'t currently working on any orders';
echo '<p><a href="#" id="startNewOrder">Start New Order</a></p>';
echo '</p><div class="table-responsive"><table class="table table-hover">
<thead>
	<tr>
		<td>Order date</td><td>Order Reference</td><td>Number of lines</td><td>Load to trolley</td><td>Delete</td></tr></thead><tbody>
';
if ( @count($_SESSION['saved_orders']) ) {
foreach ( @$_SESSION['saved_orders'] as $order ) {
echo '<tr><td>'.$order['order_date'].'</td><td contenteditable="true" class="updateOrderNumber" data-uuid="'.$order['uuid'].'">'.$order['ref'].'</td><td>'.$order['order_lines'].'</td><td><a href="#"><span data-ref="'.$order['ref'].'" data-uuid="'.$order['uuid'].'" class="savedLoad glyphicon glyphicon-floppy-open glyphlink"></span></a></td><td><a href="#"><span data-uuid="'.$order['uuid'].'" class="savedDelete glyphicon glyphicon-floppy-remove glyphlink"></span></a></td></tr>';
}
}
echo '</tbody></table>';
echo '<a href="#" id="log-out"><span class="badge" style="background-color: #3498db; margin-top: 10px;">Log out</span></a>';
}
?>
</DIV><BR>
<HR STYLE="clear: both;">
<DIV CLASS="container">
<DIV CLASS="row">
		<DIV CLASS="col-xs-6">
		<DIV>Contact</DIV><BR>
		<SPAN CLASS="glyphicon glyphicon-envelope"></SPAN> Sandy Myles - Director<BR><SPAN CLASS="glyphicon glyphicon-envelope"></SPAN> Peter Myles - Director<BR><SPAN CLASS="glyphicon glyphicon-envelope"></SPAN> Linsey McGill - Accounts<BR><BR>
		<DIV><SPAN CLASS="glyphicon glyphicon-earphone"></SPAN> 01506-859158<BR><SPAN CLASS="glyphicon glyphicon-print"></SPAN> 01506-853618</DIV>		
        </DIV>
        <DIV CLASS="col-xs-6 text-right">
        <DIV>Office</DIV><BR>
        Myles Bros Ltd<BR>Unit 1, Greendykes Ind Est<BR>Broxburn<BR>West Lothian<BR>EH52 6PG
        </DIV>
</DIV>
</DIV>
</DIV>
<HR STYLE="clear: both;">
<?php
//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>';
?>
</BODY>
</HTML>