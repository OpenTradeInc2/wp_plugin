<?php

    function readAndProcessFile($fullPatch, $filename, $current_user, $formatDate, $distributorID){
    
        $allDataInSheet = readContentFile($fullPatch);
        $arrayCount = count($allDataInSheet)-1;
    
        if(validateQuantityHeaders($allDataInSheet) and validateNameHeaders($allDataInSheet)){

            if(validatePositionOFHeaders($allDataInSheet)){
    
                $products = getProductsList($allDataInSheet, $arrayCount, $fullPatch);
                saveProducts($products, $filename, $arrayCount, $current_user, $formatDate, $distributorID);

                $_GET['message-success']='Upload success, please approve the file to update de inventory.';
                $_GET['message-file-name'] = $filename;
                $_GET['message-total-products'] = $arrayCount;
            }else{
                $_GET['message-error']= 'The Format File not contain the headers required.';
            }
        }else{
            $_GET['message-error']= 'The Format File not contain the headers required.';
        }
        return $products;
    }

    function readContentFile($fullPatch){        

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($fullPatch);
        } catch (Exception $e) {
            $_GET['message-error']='Error loading file "' . pathinfo($fullPatch, PATHINFO_BASENAME) . '": ' . $e->getMessage();
        }
        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        return $allDataInSheet;
    }

    function getValue($fullPatch,$cell, $row){

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($fullPatch);
        } catch (Exception $e) {
            $_GET['message-error']='Error loading file "' . pathinfo($fullPatch, PATHINFO_BASENAME) . '": ' . $e->getMessage();
        }

        return $objPHPExcel->getActiveSheet()->getCell($cell.$row)->getOldCalculatedValue();
    }

    function validateQuantityHeaders($allDataInSheet){

        $headersQuantity = count($allDataInSheet[1]);

		//if($headersQuantity == 15 ){
        if($headersQuantity == 19){
            $result = true;
        }else{
            $result= false;
        }
        return $result;
    }

    function validateNameHeaders($allDataInSheet){

        $headers = $allDataInSheet[1];
        $result = true;

        /*if (!in_array('SKU ID', $headers)) {
            $result = false;
        }
        if (!in_array('SKU Description', $headers)) {
            $result = false;
        }
        if (!in_array('Product Line', $headers)) {
            $result = false;
        }
        if (!in_array('Lot#', $headers)) {
            $result = false;
        }
        if (!in_array('Issue Type', $headers)) {
            $result = false;
        }
        if (!in_array('LI Specialist', $headers)) {
            $result = false;
        }
        if (!in_array('Warehouse', $headers)) {
            $result = false;
        }
        if (!in_array('City', $headers)) {
            $result = false;
        }
        if (!in_array('Zip Code', $headers)) {
            $result = false;
        }
        if (!in_array('LMD', $headers)) {
            $result = false;
        }
        if (!in_array('ID Month', $headers)) {
            $result = false;
        }
        if (!in_array('Days Under Current Path', $headers)) {
            $result = false;
        }
        if (!in_array('Qty', $headers)) {
            $result = false;
        }
        if (!in_array('TC', $headers)) {
            $result = false;
        }
        if (!in_array('Category', $headers)) {
            $result = false;
        }*/

		if (!in_array('Line #', $headers)) {
            $result = false;
        }
        if (!in_array('Distributor ID', $headers)) {
            $result = false;
        }
        if (!in_array('Distributor Name', $headers)) {
            $result = false;
        }
        if (!in_array('Distributor SKU ID', $headers)) {
            $result = false;
        }
        if (!in_array('Distributor SKU Description', $headers)) {
            $result = false;
        }
        if (!in_array('Lot#', $headers)) {
            $result = false;
        }
        if (!in_array('PackagingType', $headers)) {
            $result = false;
        }
        if (!in_array('Packaging Unit', $headers)) {
            $result = false;
        }
        if (!in_array('Packaging Measure', $headers)) {
            $result = false;
        }
        if (!in_array('Packaging Weight (lb)', $headers)) {
            $result = false;
        }
        if (!in_array('Packaging Weight (kg)', $headers)) {
            $result = false;
        }
        if (!in_array('Quantity', $headers)) {
            $result = false;
        }
        if (!in_array('Total Weight (lb)', $headers)) {
            $result = false;
        }
        if (!in_array('Total Weight (Kg)', $headers)) {
            $result = false;
        }
        if (!in_array('Price / Unit', $headers)) {
            $result = false;
        }		
		if (!in_array('Price / lb', $headers)) {
            $result = false;
        }
		if (!in_array('Price / Kg', $headers)) {
            $result = false;
        }
		if (!in_array('Warehouse location ID', $headers)) {
            $result = false;
        }		
		if (!in_array('Warehouse Location Address', $headers)) {
            $result = false;
        }	
        return $result;
    }

    function validatePositionOFHeaders($allDataInSheet){

        $headers = $allDataInSheet[1];
        $result = true;

		/*
        if ($headers['A'] !== 'SKU ID') {
            $result = false;
        }
        if ($headers['B'] !== 'SKU Description') {
            $result = false;
        }
        if ($headers['C'] !== 'Product Line') {
            $result = false;
        }
        if ($headers['D'] !== 'Lot#') {
            $result = false;
        }
        if ($headers['E'] !== 'Issue Type') {
            $result = false;
        }
        if ($headers['F'] !== 'LI Specialist') {
            $result = false;
        }
        if ($headers['G'] !== 'Warehouse') {
            $result = false;
        }
        if ($headers['H'] !== 'City') {
            $result = false;
        }
        if ($headers['I'] !== 'Zip Code') {
            $result = false;
        }
        if ($headers['J'] !== 'LMD') {
            $result = false;
        }
        if ($headers['K'] !== 'ID Month') {
            $result = false;
        }
        if ($headers['L'] !== 'Days Under Current Path') {
            $result = false;
        }
        if ($headers['M'] !== 'Qty') {
            $result = false;
        }
        if ($headers['N'] !== 'TC') {
            $result = false;
        }
        if ($headers['O'] !== 'Category') {
            $result = false;
        }
		*/
		if ($headers['A'] !== 'Line #') {
            $result = false;
        }
        if ($headers['B'] !== 'Distributor ID') {
            $result = false;
        }
        if ($headers['C'] !== 'Distributor Name') {
            $result = false;
        }
        if ($headers['D'] !== 'Distributor SKU ID') {
            $result = false;
        }
        if ($headers['E'] !== 'Distributor SKU Description') {
            $result = false;
        }
        if ($headers['F'] !== 'Lot#') {
            $result = false;
        }
        if ($headers['G'] !== 'PackagingType') {
            $result = false;
        }
        if ($headers['H'] !== 'Packaging Unit') {
            $result = false;
        }
        if ($headers['I'] !== 'Packaging Measure') {
            $result = false;
        }
        if ($headers['J'] !== 'Packaging Weight (lb)') {
            $result = false;
        }
        if ($headers['K'] !== 'Packaging Weight (kg)') {
            $result = false;
        }
        if ($headers['L'] !== 'Quantity') {
            $result = false;
        }
        if ($headers['M'] !== 'Total Weight (lb)') {
            $result = false;
        }
        if ($headers['N'] !== 'Total Weight (Kg)') {
            $result = false;
        }
        if ($headers['O'] !== 'Price / Unit') {
            $result = false;
        }
		if ($headers['P'] !== 'Price / lb') {
            $result = false;
        }
		if ($headers['Q'] !== 'Price / Kg') {
            $result = false;
        }
		if ($headers['R'] !== 'Warehouse location ID') {
            $result = false;
        }
		if ($headers['S'] !== 'Warehouse Location Address') {
            $result = false;
        }

        return $result;
    }

    function getProductsList($allDataInSheet, $arrayCount, $fullPatch){

        $products = array();
        for ($i = 2; $i <= $arrayCount+1; $i++) {
            $product = array();

			$lineNumber = trim($allDataInSheet[$i]["A"]);
            $product[1] = $lineNumber;
            $distributorID = trim($allDataInSheet[$i]["B"]);
            $product[2] = $distributorID;
            $distributorName = trim($allDataInSheet[$i]["C"]);
            $product[3] = $distributorName;
            $distributorSkuId = trim($allDataInSheet[$i]["D"]);
            $product[4] = $distributorSkuId;
            $distributorSkuDescription = trim($allDataInSheet[$i]["E"]);
            $product[5] = $distributorSkuDescription;
            $lotNumber = trim($allDataInSheet[$i]["F"]);
            $product[6] = $lotNumber;
            $packagingType = trim($allDataInSheet[$i]["G"]);
            $product[7] = $packagingType;
            $packagingUnit = trim($allDataInSheet[$i]["H"]);
            $product[8] = $packagingUnit;
            $packagingMeasure = trim($allDataInSheet[$i]["I"]);
            $product[9] = $packagingMeasure;
            $packagingWeightLb = trim($allDataInSheet[$i]["J"]);
            if($packagingWeightLb=='na'){
                $packagingWeightLb = getValue($fullPatch,"J",$i).".0";
            }
            $product[10] = $packagingWeightLb;
            $packagingWeightKg = trim($allDataInSheet[$i]["K"]);
            if($packagingWeightKg=='na'){
                $packagingWeightKg = getValue($fullPatch,"K",$i).".0";
            }
            $product[11] = $packagingWeightKg;
            $quantity = trim($allDataInSheet[$i]["L"]);
            $product[12] = $quantity;
            $totalWeightLb = trim($allDataInSheet[$i]["M"]);
            if($totalWeightLb=='#VALUE!'){
                $totalWeightLb = getValue($fullPatch,"M",$i).".0";
            }
            $product[13] = $totalWeightLb;
            $totalWeightKg = trim($allDataInSheet[$i]["N"]);
            if($totalWeightKg=='#VALUE!'){
                $totalWeightKg = getValue($fullPatch,"N",$i).".0";
            }
            $product[14]=$totalWeightKg;
            $priceUnit = trim($allDataInSheet[$i]["O"]);
            if($priceUnit=='$0'){
                $priceUnit = "$".getValue($fullPatch,"O",$i);
            }
            $product[15]=$priceUnit;
			$priceLb = trim($allDataInSheet[$i]["P"]);
            $product[16]=$priceLb;
			$priceKg = trim($allDataInSheet[$i]["Q"]);
            $product[17]=$priceKg;
			$warehouseLocationId = trim($allDataInSheet[$i]["R"]);
            $product[18]=$warehouseLocationId;
			$warehouseLocationAddress = trim($allDataInSheet[$i]["S"]);
            $product[19]=$warehouseLocationAddress;			
            $products[$i-1]=$product;
        }
        return $products;
    }

    function saveProducts($products, $filename, $arrayCount, $current_user, $formatDate, $distributorID){

        global $wpdb;

        $isConnected = $wpdb->check_connection();

        if($isConnected){

            $wpdb->query("INSERT INTO ot_custom_inventory_file 
                                      (`file_md5`,`items_count`,`added_by`,`added_date`,`deleted`,`status`) 
                                      VALUES 
                                      ('$filename', '$arrayCount', '$current_user', '$formatDate',0 , 'pending_approval')");
            $idProductFile = $wpdb->insert_id;

            foreach ($products as $product){
				/*	
                $price = str_replace("$", "", $product[14]);
                $wpdb->query("INSERT INTO ot_custom_inventory_file_items 
                                                      (`inventory_file_id`, `sku_id`, `sku_description`, `product_line`, `lot_number`, `issue_type`, `li_specialist`, `warehouse`, `city`, `zipcode`, `lmd`, `id_month`, `days_under_current_path`, `quantity_libs`, `sum_quantity`, `total_cost`, `added_by`, `added_date`, `deleted`, `status`, `category`,`distributor_id`) 
                                           VALUES 
                                                      ('$idProductFile','$product[1]','$product[2]','$product[3]','$product[4]','$product[5]','$product[6]','$product[7]','$product[8]','$product[9]','$product[10]','$product[11]','$product[12]',0 ,$product[13],$price,'$current_user', '$formatDate',0 , 'pending_approval', '$product[15]',$distributorID)");													  
													  
				*/	

				$totalWeightLb = tofloat($product[13]);
				$totalWeightKg = tofloat($product[14]);

                $product[15] = str_replace("$", "", $product[15]);
                $product[16] = str_replace("$", "", $product[16]);
                $product[17] = str_replace("$", "", $product[17]);

                $priceUnit = tofloat($product[15]);
                $priceLb = tofloat($product[16]);
                $priceKg = tofloat($product[17]);
				
				$wpdb->query("INSERT INTO ot_custom_inventory_file_items 
                                                      (`inventory_file_id`, 
													  `distributor_id`,
													  `added_by`, 
													  `added_date`, 
													  `deleted`, 
													  `status`,
													  `line_number`,													  
													  `distributor_name`, 
													  `distributor_sku_id`, 
													  `distributor_sku_description`, 
													  `lot_number`, 
													  `packaging_type`, 
													  `packaging_unit`, 
													  `packaging_measure`, 
													  `packaging_weight_lb`, 
													  `packaging_weight_kg`, 
													  `quantity`, 
													  `total_weight_lb`, 
													  `total_weight_kg`, 
													  `price_unit`, 													  
													  `price_lb`,
													  `price_kg`,
													  `warehouse_location_id`,
													  `warehouse_location_address`,
													  `distributor_file_id`) 
                                           VALUES 
                                                      ('$idProductFile',
													  $distributorID,
													  '$current_user', 
													  '$formatDate',
													  0, 
													  'pending_approval',
													  '$product[1]',													  
													  '$product[3]',
													  '$product[4]',
													  '$product[5]',
													  '$product[6]',
													  '$product[7]',
													  '$product[8]',
													  '$product[9]',
													  '$product[10]',
													  '$product[11]',
													  '$product[12]',													 
													  $totalWeightLb,
													  $totalWeightKg,													  
													  '$priceUnit',
													  '$priceLb',
													  '$priceKg',
													  '$product[18]',
													  '$product[19]',
													  '$product[2]')");
				
            }
        }
    }

    function tofloat($num)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
        );
    }

    function validateQuantityHeadersToUpdate($allDataInSheet){

        $headersQuantity = count($allDataInSheet[1]);

        //if($headersQuantity == 15 ){
        if($headersQuantity == 20){
            $result = true;
        }else{
            $result= false;
        }
        return $result;
    }

    function validateNameHeadersToUpdate($allDataInSheet){

    $headers = $allDataInSheet[1];
    $result = true;

    if (!in_array('Post ID', $headers)) {
        $result = false;
    }
    if (!in_array('Line#', $headers)) {
        $result = false;
    }
    if (!in_array('Distributor ID', $headers)) {
        $result = false;
    }
    if (!in_array('Distributor Name', $headers)) {
        $result = false;
    }
    if (!in_array('Distributor SKU ID', $headers)) {
        $result = false;
    }
    if (!in_array('Distributor SKU Description', $headers)) {
        $result = false;
    }
    if (!in_array('Lot#', $headers)) {
        $result = false;
    }
    if (!in_array('PackagingType', $headers)) {
        $result = false;
    }
    if (!in_array('Packaging Unit', $headers)) {
        $result = false;
    }
    if (!in_array('Packaging Measure', $headers)) {
        $result = false;
    }
    if (!in_array('Packaging Weight (lb)', $headers)) {
        $result = false;
    }
    if (!in_array('Packaging Weight (kg)', $headers)) {
        $result = false;
    }
    if (!in_array('Quantity', $headers)) {
        $result = false;
    }
    if (!in_array('Total Weight (lb)', $headers)) {
        $result = false;
    }
    if (!in_array('Total Weight (Kg)', $headers)) {
        $result = false;
    }
    if (!in_array('Price / Unit', $headers)) {
        $result = false;
    }
    if (!in_array('Price / lb', $headers)) {
        $result = false;
    }
    if (!in_array('Price / Kg', $headers)) {
        $result = false;
    }
    if (!in_array('Warehouse location ID', $headers)) {
        $result = false;
    }
    if (!in_array('Warehouse Location Address', $headers)) {
        $result = false;
    }
    return $result;
}

    function validatePositionOFHeadersToUpdate($allDataInSheet){

    $headers = $allDataInSheet[1];
    $result = true;

    if ($headers['A'] !== 'Post ID') {
        $result = false;
    }
    if ($headers['B'] !== 'Line#') {
        $result = false;
    }
    if ($headers['C'] !== 'Distributor ID') {
        $result = false;
    }
    if ($headers['D'] !== 'Distributor Name') {
        $result = false;
    }
    if ($headers['E'] !== 'Distributor SKU ID') {
        $result = false;
    }
    if ($headers['F'] !== 'Distributor SKU Description') {
        $result = false;
    }
    if ($headers['G'] !== 'Lot#') {
        $result = false;
    }
    if ($headers['H'] !== 'PackagingType') {
        $result = false;
    }
    if ($headers['I'] !== 'Packaging Unit') {
        $result = false;
    }
    if ($headers['J'] !== 'Packaging Measure') {
        $result = false;
    }
    if ($headers['K'] !== 'Packaging Weight (lb)') {
        $result = false;
    }
    if ($headers['L'] !== 'Packaging Weight (kg)') {
        $result = false;
    }
    if ($headers['M'] !== 'Quantity') {
        $result = false;
    }
    if ($headers['N'] !== 'Total Weight (lb)') {
        $result = false;
    }
    if ($headers['O'] !== 'Total Weight (Kg)') {
        $result = false;
    }
    if ($headers['P'] !== 'Price / Unit') {
        $result = false;
    }
    if ($headers['Q'] !== 'Price / lb') {
        $result = false;
    }
    if ($headers['R'] !== 'Price / Kg') {
        $result = false;
    }
    if ($headers['S'] !== 'Warehouse location ID') {
        $result = false;
    }
    if ($headers['T'] !== 'Warehouse Location Address') {
        $result = false;
    }

    return $result;
}

    function readAndProcessFileToUpdate($fullPatch, $filename, $current_user, $formatDate){

        $allDataInSheet = readContentFile($fullPatch);
        $arrayCount = count($allDataInSheet)-1;

        if(validateQuantityHeadersToUpdate($allDataInSheet) and validateNameHeadersToUpdate($allDataInSheet)){

            if(validatePositionOFHeadersToUpdate($allDataInSheet)){

                $products = getProductsListToUpdate($allDataInSheet, $arrayCount);

                saveProductsUpdate($products);

                $_GET['message-success']='Upload success!';
                $_GET['message-file-name'] = $filename;
                $_GET['message-total-products'] = $arrayCount;
            }else{
                $_GET['message-error']= 'The Format File not contain the headers required.';
            }
        }else{
            $_GET['message-error']= 'The Format File not contain the headers required.';
        }
        return $products;
    }

    function getProductsListToUpdate($allDataInSheet, $arrayCount){

    $products = array();
    for ($i = 2; $i <= $arrayCount+1; $i++) {
        $product = array();

        $postID= trim($allDataInSheet[$i]["A"]);
        $product[1] = $postID;
        $lineNumber = trim($allDataInSheet[$i]["B"]);
        $product[2] = $lineNumber;
        $distributorID = trim($allDataInSheet[$i]["C"]);
        $product[3] = $distributorID;
        $distributorName = trim($allDataInSheet[$i]["D"]);
        $product[4] = $distributorName;
        $distributorSkuId = trim($allDataInSheet[$i]["E"]);
        $product[5] = $distributorSkuId;
        $distributorSkuDescription = trim($allDataInSheet[$i]["F"]);
        $product[6] = $distributorSkuDescription;
        $lotNumber = trim($allDataInSheet[$i]["G"]);
        $product[7] = $lotNumber;
        $packagingType = trim($allDataInSheet[$i]["H"]);
        $product[8] = $packagingType;
        $packagingUnit = trim($allDataInSheet[$i]["I"]);
        $product[9] = $packagingUnit;
        $packagingMeasure = trim($allDataInSheet[$i]["J"]);
        $product[10] = $packagingMeasure;
        $packagingWeightLb = trim($allDataInSheet[$i]["K"]);
        $product[11] = $packagingWeightLb;
        $packagingWeightKg = trim($allDataInSheet[$i]["L"]);
        $product[12] = $packagingWeightKg;
        $quantity = trim($allDataInSheet[$i]["M"]);
        $product[13] = $quantity;
        $totalWeightLb = trim($allDataInSheet[$i]["N"]);
        $product[14] = $totalWeightLb;
        $totalWeightKg = trim($allDataInSheet[$i]["O"]);
        $product[15]=$totalWeightKg;
        $priceUnit = trim($allDataInSheet[$i]["P"]);
        $product[16]=$priceUnit;
        $priceLb = trim($allDataInSheet[$i]["Q"]);
        $product[17]=$priceLb;
        $priceKg = trim($allDataInSheet[$i]["R"]);
        $product[18]=$priceKg;
        $warehouseLocationId = trim($allDataInSheet[$i]["S"]);
        $product[19]=$warehouseLocationId;
        $warehouseLocationAddress = trim($allDataInSheet[$i]["T"]);
        $product[20]=$warehouseLocationAddress;
        $products[$i-1]=$product;
    }
    return $products;
}

    function saveProductsUpdate($products){

        foreach ($products as $product){

            $product[16] = str_replace("$", "", $product[16]);
            $product[17] = str_replace("$", "", $product[17]);
            $product[18] = str_replace("$", "", $product[18]);

            $line_number  = array( 'name' => 'Line #', 'value' => $product[2], 'position'=>'2', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_id  = array( 'name' => 'Distributor ID', 'value' => $product[3], 'position'=>'3', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_name  = array( 'name' => 'Distributor Name', 'value' => $product[4], 'position'=>'4', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_sku_id  = array( 'name' => 'Distributor SKU ID', 'value' => $product[5], 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_sku_description  = array( 'name' => 'Distributor SKU Description', 'value' => $product[6], 'position'=>'6', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lot_number  = array( 'name' => 'Lot#', 'value' => $product[7], 'position'=>'7', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_type  = array( 'name' => 'Packaging Type', 'value' => $product[8], 'position'=>'8', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_unit  = array( 'name' => 'Packaging Unit', 'value' => $product[9], 'position'=>'9', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_measure  = array( 'name' => 'Packaging Measure', 'value' => $product[10], 'position'=>'10', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_weight_lb  = array( 'name' => 'Packaging Weight (lb)', 'value' => $product[11], 'position'=>'11', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_weight_kg  = array( 'name' => 'Packaging Weight (kg)', 'value' => $product[12], 'position'=>'12', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $quantity  = array( 'name' => 'Quantity', 'value' => $product[13], 'position'=>'13', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $total_weight_lb  = array( 'name' => 'Total Weight (lb)', 'value' => $product[14], 'position'=>'14', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $total_weight_kg  = array( 'name' => 'Total Weight (Kg)', 'value' => $product[15], 'position'=>'15', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_unit  = array( 'name' => 'Price / Unit', 'value' => $product[16], 'position'=>'16', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_lb  = array( 'name' => 'Price / lb', 'value' => $product[17], 'position'=>'17', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_kg  = array( 'name' => 'Price / Kg', 'value' => $product[18], 'position'=>'18', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse_location_id  = array( 'name' => 'Warehouse Location ID', 'value' => $product[19], 'position'=>'19', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse_location_address  = array( 'name' => 'Warehouse Location Address', 'value' => $product[20], 'position'=>'20', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );

            $product_attributes = array($line_number, $distributor_id, $distributor_name, $distributor_sku_id, $distributor_sku_description, $lot_number, $packaging_type,
            $packaging_unit, $packaging_measure, $packaging_weight_lb,$packaging_weight_kg, $quantity, $total_weight_lb, $total_weight_kg, $price_unit, $price_lb, $price_kg, $warehouse_location_id, $warehouse_location_address );

            $price = str_replace("$", "", $product[16]);


            update_post_meta( $product[1], '_regular_price', $price );
            update_post_meta( $product[1], '_sale_price', $price );
            update_post_meta( $product[1], '_price', $price );

            update_post_meta($product[1],'_product_attributes',$product_attributes);

            $current_user =  wp_get_current_user();
            $user_id = $current_user->ID;

            if(strtolower($product[10]) =='kg'){
                $weight = $product[12];
            }else{
                $weight = $product[11];
            }

            $post = array(
                'ID'=> $product[1],
                'post_author' => $user_id,
                'post_content' => $product[6].' '.$product[8].' '.$product[9].' '.$weight.' '.$product[10],
                'post_status' => "publish",
                'post_title' => $product[6],
                'post_parent' => '',
                'post_type' => "product",
                'post_name' => $product[5]
            );

            wp_update_post($post);

        }

    }
