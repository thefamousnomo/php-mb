<?php
date_default_timezone_set('Europe/London');
session_start();
$basket_qty = @count($_SESSION['order_items']);
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
#logger *{
margin-top: 10px; 
}
</style>
<script type="text/javascript">
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
		 $.get( "/search/engine.php", { action: act, q: $("#searchText").val(), g: $("#filter").data("field"), aon: $(ele).data("aon"), qty: $(ele).val(), sku: $(ele).data("sku"), customer: $('#shopname').val(), email: $('#emailaddress').val() } ).done(function( data ) {
    		if ( flag == 1 ) {
    			$( "#resultsDiv" ).html( data );
    		} else {
    			obj = JSON.parse(data);
    			$("#basket_qty").html(obj.count);
    			if ( obj.message ) $( "#resultsDiv" ).html(obj.message);
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
    $('#resultsDiv').on('click', '#orderSubmit', function(){
    if ( $('#shopname').val() && $('#emailaddress').val() ) {
    	$('#orderSubmit').addClass('btn-warning');
    	ajaxSend($(this), '_order', 0);
    }
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
});
$(document).ajaxStop(function () {
$("#searchButton").removeClass('btn-warning');
});
</script>
</head>
<body>
<div style="overflow-x: hidden;">
<div class="jumbotron text-center jumbosmallmargin">
  <h1>Myles Bros Ltd</h1>
  <p>Wholesale Hardware Factors</p><br>
<div class="row rowNoMargin">
  <div class="col-sm-6 col-sm-offset-3">
    <div class="input-group">
      <input type="text" class="form-control" aria-label="..." placeholder="search our database" id="searchText">
      <div class="input-group-btn">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span id="filter" data-field="3">Description</span> <span class="caret"></span></button>
        <ul class="dropdown-menu dropdown-menu-right">
          <li><a id="filter-code" data-field="2" href="#">Code</a></li>
          <li><a id="filter-supplier" data-field="1" href="#">Supplier</a></li>
          <li><a id="filter-description" data-field="3" href="#">Description</a></li>
          <li role="separator" class="divider"></li>
          <li><a id="filter-specialoffers" href="#">Special Offers</a></li>
        </ul>
      </div><!-- /btn-group -->
    </div><!-- /input-group -->
  </div><!-- /.col-sm-6 col-sm-offset-3 -->
</div>
<br><button type="button" class="btn btn-lg btn-success" id="searchButton" data-aon="1"><span class="glyphicon glyphicon-search"></span> Search</button>
</div>
<div class="container">
<div style="float: right; text-align: right;"><a id="trolley" href="#"><span id="badge" class="badge" style="background-color: #3498db;"><span id="basket_qty"><?php echo $basket_qty;?></span>&nbsp;items in trolley&nbsp;<span class="glyphicon glyphicon-shopping-cart"></span></span></a></div>
<div style="float: left;"><a href="./"><span class="glyphicon glyphicon-home glyphlink"></span></a>&nbsp;&nbsp;<a href="#"><span id="login" class="glyphicon glyphicon-log-in glyphlink"></span></a><h4><?php echo @$_SESSION['logged_in']['NAME'];?></h4>
<div class="input-group">
<div id="logger" style="display: none;">
<input type="text" id="ver-u" placeholder="username">
<input type="password" id="ver-p" placeholder="password">
<a href="#" id="verify"><span class="badge" style="background-color: #3498db;">Log in</span></a>
</div>
</div>
</div>
<div id="resultsDiv" style="clear: both; padding-top: 1px; width: 100%;">
<div style="float: right;"><img width="250" src="/search/ht.jpg" alt="barrow" class="img-responsive"></div>
<h3>Welcome To Myles Brothers Ltd</h3>
<br><p>We are a 3rd generation family run wholesale hardware business with a firm focus on service.</p><p>Please click <a href="#" id="help">here</a> for a quick instructional tour of our ordering system.</p>
<br><br><img width="250" src="/search/brooms-857508_1280.jpg" alt="brooms" class="img-circle img-responsive">
</div>
</div><br>
<hr style="clear: both;">
<div class="container">
<div class="row">
		<div class="col-xs-6">
		<div>Contact</div><br>
		<span class="glyphicon glyphicon-envelope"></span> Sandy Myles - Director<br><span class="glyphicon glyphicon-envelope"></span> Peter Myles - Director<br><span class="glyphicon glyphicon-envelope"></span> Linsey McGill - Accounts<br><br>
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
//print_r($_SESSION);
?>
</body>
</html>