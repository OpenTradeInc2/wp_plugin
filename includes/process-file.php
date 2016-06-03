<?php

    function readAndProcessFile($fullPatch, $filename, $current_user, $formatDate, $distributorID){
    
        $allDataInSheet = readContentFile($fullPatch);
        $arrayCount = count($allDataInSheet)-1;
    
        if(validateQuantityHeaders($allDataInSheet) and validateNameHeaders($allDataInSheet)){

            if(validatePositionOFHeaders($allDataInSheet)){
    
                $products = getProductsList($allDataInSheet, $arrayCount);
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

    function getProductsList($allDataInSheet, $arrayCount){

        $products = array();
        for ($i = 2; $i <= $arrayCount+1; $i++) {
            $product = array();
            
			/*$skuID = trim($allDataInSheet[$i]["A"]);
            $product[1] = $skuID;
            $skuDescription = trim($allDataInSheet[$i]["B"]);
            $product[2] = $skuDescription;
            $productLine = trim($allDataInSheet[$i]["C"]);
            $product[3] = $productLine;
            $lot = trim($allDataInSheet[$i]["D"]);
            $product[4] = $lot;
            $issueType = trim($allDataInSheet[$i]["E"]);
            $product[5] = $issueType;
            $liSpecialist = trim($allDataInSheet[$i]["F"]);
            $product[6] = $liSpecialist;
            $warehouse = trim($allDataInSheet[$i]["G"]);
            $product[7] = $warehouse;
            $city = trim($allDataInSheet[$i]["H"]);
            $product[8] = $city;
            $zipCode = trim($allDataInSheet[$i]["I"]);
            $product[9] = $zipCode;
            $lmd = trim($allDataInSheet[$i]["J"]);
            $product[10] = $lmd;
            $idMonth = trim($allDataInSheet[$i]["K"]);
            $product[11] = $idMonth;
            $daysUnderCurrentPath = trim($allDataInSheet[$i]["L"]);
            $product[12] = $daysUnderCurrentPath;
            $qty = trim($allDataInSheet[$i]["M"]);
            $product[13] = $qty;
            $tc = trim($allDataInSheet[$i]["N"]);
            $product[14]=$tc;
            $category = trim($allDataInSheet[$i]["O"]);
            $product[15]=$category;*/
	
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
            $product[10] = $packagingWeightLb;
            $packagingWeightKg = trim($allDataInSheet[$i]["K"]);
            $product[11] = $packagingWeightKg;
            $quantity = trim($allDataInSheet[$i]["L"]);
            $product[12] = $quantity;
            $totalWeightLb = trim($allDataInSheet[$i]["M"]);
            $product[13] = $totalWeightLb;
            $totalWeightKg = trim($allDataInSheet[$i]["N"]);
            $product[14]=$totalWeightKg;
            $priceUnit = trim($allDataInSheet[$i]["O"]);
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

				$totalWeightLb = $product[13];
				$totalWeightKg = $product[14];

				$priceUnit = str_replace("$", "", $product[15]);
                $priceLb = str_replace("$", "", $product[16]);
				$priceKg = str_replace("$", "", $product[17]);
				
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
													  `warehouse_location_address`) 
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
													  '$totalWeightLb',
													  '$totalWeightKg',													  
													  '$priceUnit',
													  '$priceLb',
													  '$priceKg',
													  '$product[18]',
													  '$product[19]')");
				
            }
        }
    }
