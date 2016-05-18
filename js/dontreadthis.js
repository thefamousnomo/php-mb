    $(document).ready(function(){
    	$("#leave").click(function() {
    	window.location.href = 'http://www.prestigeleisure.com';
    	});
    	$("#searchButton").click(function() {
        	$.get( "search.php", { sku: $("#searchText").val()} ).done(function( data ) {
        		$( "#resultsDiv" ).html( data );
        	});
    	});
    	$("#rmaDisplay").click(function() {
        	$.get( "rma.php", { action_: "display" } ).done(function( data ) {
        		$( "#rmaSummary" ).html( data );
        	});
    	});
    	$("#conan").click(function() {
        	$.get( "rma.php", { action_: "destroy" } ).done(function( data ) {
        		alert("logged out");
        		window.location.reload(true);
        	});
    	});
    	$('#rmaSummary').on('click', '.delete', function() {
    	   	$.get( "rma.php", { action_: "delete", delete_: $(this).data("delete") } ).done(function( data ) {
       		var json_obj = JSON.parse(data);
       		$("#rmaCount").html(json_obj.count);
       		$("#rmaDisplay").click();
   		   	alert("item removed from RMA");
        	});
    	});
    	$('#resultsDiv').on('click', '[id^=button-]', function(){
    	var id = $(this).data('id');
    	var spinner_max = parseInt($("#spinner-" + id).attr("max"));
    	var spinner_current = parseInt($("#spinner-" + id).val());
    	var select = $("#select-" + id).val();
    	if ( spinner_current > spinner_max ) {
    	$("#spinner-" + id).val(spinner_max);
    	}
    	var spinner = $("#spinner-" + id).val();
       		$.get( "rma.php", { id_: id, spinner_: spinner, select_: select, action_ : "add"} ).done(function( data ) {
        	var json_obj = JSON.parse(data);
        	$("#rmaCount").html(json_obj.count);
        	alert("item added to RMA");
        	});
    	});
    });
    function theWalkingButton(ind, elem) {
    var togButton = document.getElementById("button-" + elem);
    var warDiv = document.getElementById("warn-" + elem);
        if ( ind == 0 ) {
        warDiv.style.visibility='hidden';
        togButton.disabled = true;
        //togButton.className = 'dead'; 
    	}	else	{
    		if ( ind == 1 ) {
    		warDiv.style.visibility='visible';
    		}	else {
    				warDiv.style.visibility='hidden';
    				}
    		togButton.disabled = false;
    		//togButton.className = 'alive';
    		}
    }
