<?
$keys = Array("SUPP_REF", "SUPP_NAME", "STOCK_CODE", "DESCRIPTION", "SALES", "RRP", "STOCK", "BARCODE", "CATEGORY");
$dbArray = file('search/pricelist.dat');
foreach ( $dbArray as $row ) {
$rowArray = explode(',', $row);
array_walk($rowArray, function(&$cell){$cell = str_replace('"', '', $cell);});
	if ( count($rowArray) > 1 ) {
	$rowArray = @array_combine($keys, $rowArray);
	$dbArray_key[$rowArray['STOCK_CODE']] = $rowArray;
	}
}
echo "THE FOLLOWING IMAGES IS MISSING FROM THE LIBRARY\n\n";
foreach ( $dbArray_key as $product ) {
	if ( file_exists('search/images/'.strtolower($product['STOCK_CODE']).'.jpg') === false ) {
	//echo "THE FOLLOWING IMAGE IS MISSING FROM THE LIBRARY\n\n";
	//print_r($product);
	echo $product['STOCK_CODE']."\n";
	echo $product['DESCRIPTION']."\n";
	echo "\n\n";
	}
}
?>
