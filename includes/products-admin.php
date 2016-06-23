<?php

    function approveProductsFiles($idProductsFile){

        global $wpdb;

        $isConnected = $wpdb->check_connection();
        $overallProcess = true;

        if($isConnected){

            foreach ($idProductsFile as $productFileID) {
                $products = getProducts($wpdb, $productFileID);
                foreach ($products as $product){
                    if(!createOrUpdateProduct($product)){
                        $overallProcess = false;
                    }
                }
                updateCompletedFile($productFileID, $overallProcess);
                $_GET['message-success'] ='File successfully approved.';
            }
        }

        return $overallProcess;
    }

    function getProducts($wpdb, $productFileID){

        $products = $wpdb->get_results("SELECT * 
                                         FROM `ot_custom_inventory_file_items`
                                         WHERE `inventory_file_id` = " . $productFileID);

        return $products;
    }

    function createOrUpdateProduct($product){

        if(isNewProduct($product)){
            $result = createPost($product);
        }else{
            $result = updateProduct($product);
        }

        updateCompletedProduct($product->inventory_file_item_id,$result);

        return $result;
    }

    function isNewProduct($product){

        global $wpdb;
        $result = false;

        $total = $wpdb->get_results("SELECT count(`product_post_id`) as total
                                     FROM `ot_custom_product_post`
                                     WHERE `sku_distributor` = '".$product->distributor_sku_id."' and `sku_description` = '".$product->distributor_sku_description."' and `distributor_id` = ".$product->distributor_id." and `package_size` = '".$product->packaging_type."' and `warehouse` = '".$product->warehouse_location_id."';");

        if($total[0]->total == 0){
            $result = true;
        }

        return $result;
    }

    function createPost($product){

        global $current_user;

        if (is_user_logged_in())
        {
            $current_user =  wp_get_current_user();

            $wp_error = '';
            $user_id = $current_user->ID;
            $price = str_replace("$", "", $product->price_unit);
            /*
            $product_line  = array( 'name' => 'Product Line', 'value' => $product->product_line, 'position'=>'1', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lot  = array( 'name' => 'Lot #', 'value' => $product->lot_number, 'position'=>'1', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $issue_type  = array( 'name' => 'Issue Type', 'value' => $product->issue_type, 'position'=>'2', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $specialist = array( 'name' => 'LI Specialist', 'value' => $product->li_specialist, 'position'=>'3', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse = array( 'name' => 'Warehouse', 'value' => $product->warehouse, 'position'=>'4', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $city = array( 'name' => 'City', 'value' => $product->city, 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $zipCode = array( 'name' => 'Zip Code', 'value' => $product->zipcode, 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lmd = array( 'name' => 'LMD', 'value' => $product->lmd, 'position'=>'6', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $id_month = array( 'name' => 'ID Month', 'value' => $product->id_month, 'position'=>'7', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $days_under_current_path = array( 'name' => 'Days Under Current Path', 'value' => $product->days_under_current_path, 'position'=>'8', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            */
            $line_number  = array( 'name' => 'Line #', 'value' => $product->line_number, 'position'=>'2', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_id  = array( 'name' => 'Distributor ID', 'value' => $product->distributor_file_id, 'position'=>'3', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_name  = array( 'name' => 'Distributor Name', 'value' => $product->distributor_name, 'position'=>'4', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_sku_id  = array( 'name' => 'Distributor SKU ID', 'value' => $product->distributor_sku_id, 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $distributor_sku_description  = array( 'name' => 'Distributor SKU Description', 'value' => $product->distributor_sku_description, 'position'=>'6', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lot_number  = array( 'name' => 'Lot#', 'value' => $product->lot_number, 'position'=>'7', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_type  = array( 'name' => 'Packaging Type', 'value' => $product->packaging_type, 'position'=>'8', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_unit  = array( 'name' => 'Packaging Unit', 'value' => $product->packaging_unit, 'position'=>'9', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_measure  = array( 'name' => 'Packaging Measure', 'value' => $product->packaging_measure, 'position'=>'10', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_weight_lb  = array( 'name' => 'Packaging Weight (lb)', 'value' => $product->packaging_weight_lb, 'position'=>'11', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $packaging_weight_kg  = array( 'name' => 'Packaging Weight (kg)', 'value' => $product->packaging_weight_kg, 'position'=>'12', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $quantity  = array( 'name' => 'Quantity', 'value' => $product->quantity, 'position'=>'13', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $total_weight_lb  = array( 'name' => 'Total Weight (lb)', 'value' => $product->total_weight_lb, 'position'=>'14', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $total_weight_kg  = array( 'name' => 'Total Weight (Kg)', 'value' => $product->total_weight_kg, 'position'=>'15', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_unit  = array( 'name' => 'Price / Unit', 'value' => $product->price_unit, 'position'=>'16', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_lb  = array( 'name' => 'Price / lb', 'value' => $product->price_lb, 'position'=>'17', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $price_kg  = array( 'name' => 'Price / Kg', 'value' => $product->price_kg, 'position'=>'18', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse_location_id  = array( 'name' => 'Warehouse location ID', 'value' => $product->warehouse_location_id, 'position'=>'19', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse_location_address  = array( 'name' => 'Warehouse Location Address', 'value' => $product->warehouse_location_address, 'position'=>'20', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );

            $product_attributes = array($line_number, $distributor_id, $distributor_name, $distributor_sku_id, $distributor_sku_description, $lot_number, $packaging_type,
                $packaging_unit, $packaging_measure, $packaging_weight_lb,$packaging_weight_kg, $quantity, $total_weight_lb, $total_weight_kg, $price_unit, $price_lb, $price_kg, $warehouse_location_id, $warehouse_location_address );

            if(strtolower($product->packaging_measure) =='kg'){
                $weight = $product->packaging_weight_kg;
            }else{
                $weight = $product->packaging_weight_lb;
            }
            $post = array(
                'post_author' => $user_id,
                'post_content' => $product->distributor_sku_description.' '.$product->packaging_type.' '.$product->packaging_unit.' '.$weight.' '.$product->packaging_measure,
                'post_status' => "publish",
                'post_title' => $product->distributor_sku_description,
                'post_parent' => '',
                'post_type' => "product",
                'post_name' => $product->distributor_sku_id
            );

            $post_id = wp_insert_post( $post, $wp_error );

            $oTSkuID = createOTSkuID($product, $post_id);

            setCategory($post_id, $product);

            wp_set_object_terms($post_id, 'simple', 'product_type');
            update_post_meta( $post_id, '_visibility', 'visible' );
            update_post_meta( $post_id, '_stock_status', 'instock');
            update_post_meta( $post_id, 'total_sales', '0');
            update_post_meta( $post_id, '_downloadable', 'no');
            update_post_meta( $post_id, '_virtual', 'no');
            update_post_meta( $post_id, '_regular_price', $price );
            update_post_meta( $post_id, '_sale_price', $price );
            update_post_meta( $post_id, '_purchase_note', "" );
            update_post_meta( $post_id, '_featured', "no" );
            update_post_meta( $post_id, '_weight', $product->total_weight_lb );
            update_post_meta( $post_id, '_length', "" );
            update_post_meta( $post_id, '_width', "" );
            update_post_meta( $post_id, '_height', "" );
            update_post_meta($post_id,  '_sku', $oTSkuID);
            update_post_meta( $post_id, '_product_attributes', $product_attributes);
            update_post_meta( $post_id, '_sale_price_dates_from', "" );
            update_post_meta( $post_id, '_sale_price_dates_to', "" );
            update_post_meta( $post_id, '_price', $price );
            update_post_meta( $post_id, '_sold_individually', "no" );
            update_post_meta( $post_id, '_manage_stock', "yes" );
            update_post_meta( $post_id, '_backorders', "no" );
            update_post_meta( $post_id, '_stock', $product->quantity );            

            setWareHouse($post_id, $product->warehouse_location_id, $product->warehouse_location_address, $product->distributor_id);

            setPlaceLocator($post_id, $product->distributor_sku_description, $product->warehouse_location_address);
        }
        else
        {
            $errors[] = 'User not fount.';
        }

        return true;
    }

    function setCategory($post_id, $product){

        global $wpdb;

        $product_type = term_exists('simple', 'product_type');
        $category = term_exists($product->category, 'product_cat');

        if ($product_type !== 0 && $product_type !== null) {
            insertTermRelationships($wpdb,$post_id,$product_type[term_id]);
        }else{
            $termID = insertTerm($wpdb, 'simple', 'product_type');
            insertTermRelationships($wpdb,$post_id,$termID);
        }

        if ($category !== 0 && $category !== null) {
            insertTermRelationships($wpdb,$post_id,$category[term_id]);
        }else{
            $termID = insertTerm($wpdb, $product->category, 'product_cat');
            insertTermRelationships($wpdb,$post_id,$termID);
        }
    }

    function updateProduct($product){
        global $wpdb;

        $productID = getProductID($product);
        $postMeta = get_post_meta($productID, '_stock', true);
        $totalStock =$postMeta + $product->quantity;

        $result =$wpdb->query("UPDATE `".$wpdb->prefix."postmeta`
                                        SET
                                        `meta_value` = ".$totalStock."
                                        WHERE `post_id` = ".$productID." and `meta_key` = '_stock';");

        if($result == 1){
            return true;
        }else{
            return false;
        }
    }

    function getProductID($product){
        global $wpdb;

        $result = $wpdb->get_results("SELECT `post_id`
                                      FROM `ot_custom_distributor_sku`            
                                      WHERE `id_distributor` = ".$product->distributor_id." and `id_sku_product` = '".$product->distributor_sku_id."';");

        return $result[0]->post_id;
    }

    function insertTermRelationships($wpdb,$post_id,$term_id){

        $wpdb->query("INSERT INTO `".$wpdb->prefix."term_relationships`
                             (`object_id`,
                             `term_taxonomy_id`,
                             `term_order`)
                             VALUES
                             (".$post_id.",
                              ".$term_id.",
                              0);");
    }

    function insertTerm($wpdb, $term, $taxonomy){

        $wpdb->query("INSERT INTO `".$wpdb->prefix."terms`
                            (`name`,
                            `slug`,
                            `term_group`)
                          VALUES
                            ('".$term."',
                            '".str_replace(" ", "-",strtolower($term))."',
                            0);");

        $termID=$wpdb->insert_id;

        $wpdb->query("INSERT INTO `".$wpdb->prefix."term_taxonomy`
                            (`term_id`,
                            `taxonomy`,
                            `description`,
                            `parent`,
                            `count`)
                          VALUES
                            (".$termID.",
                            '".$taxonomy."',
                            'This is category for open trade',
                            0,
                            0);");

        return $termID;
    }

    function updateCompletedFile($idProductsFile, $result){

        global $wpdb;

        $isConnected = $wpdb->check_connection();

        if($result){
            $status = "file-process-success";
        }else{
            $status = "file-process-error";
        }

        if($isConnected){
            $wpdb->query("UPDATE `ot_custom_inventory_file`
                                       SET
                                       `status` = '$status'
                                       WHERE `inventory_id`=".$idProductsFile);
        }
    }

    function updateCompletedProduct($idProduct, $result){

        global $wpdb;

        $isConnected = $wpdb->check_connection();

        if($result){
            $status = "product-process-success";
        }else{
            $status = "product-process-error";
        }

        global $current_user;
        $current_user =  wp_get_current_user();

        $formatDate = date("Ymdhis");

        if($isConnected){
            $wpdb->query("UPDATE `ot_custom_inventory_file_items`
                                           SET
                                           `status` = '$status',
                                           `edited_date` = '$formatDate',
                                           `edited_by` = $current_user->ID
                                           WHERE `inventory_file_item_id`=".$idProduct);
        }
    }

    function createOTSkuID($product, $postID ){
        global $wpdb;

        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("INSERT INTO `ot_custom_distributor_sku`
                        (`id_sku_product`,
                        `id_distributor`,
                        `post_id`,
                        `added_by`,
                        `added_date`)
                        VALUES
                        ('".$product->distributor_sku_id."', 
                         ".$product->distributor_id.",
                          ".$postID.",
                         ".$userId.", 
                         '".$date."');");

            $result = zerofill($wpdb->insert_id,7);

            $wpdb->query("INSERT INTO `ot_custom_product_post`
                            (`post_id`,
                            `sku_distributor`,
                            `sku_description`,
                            `distributor_id`,
                            `package_size`,
                            `warehouse`,
                            `added_by`,
                            `added_date`)
                          VALUES
                            (".$postID.",
                            '".$product->distributor_sku_id."',
                            '".$product->distributor_sku_description."',
                            ".$product->distributor_id.",
                            '".$product->packaging_type."',
                            '".$product->warehouse_location_id."',
                            ".$userId.",
                            '".$date."');");
        }

        return $result;
    }

    function setPlaceLocator($postId, $skuDescription, $wareHouseLocation){
        global $wpdb;

        if($wpdb->check_connection()){

            //Se hace el primer select para obtener el ID del warehouse
            $warehouse = $wpdb->get_results("SELECT * FROM ot_custom_warehouse_location WHERE location = '".$wareHouseLocation."'");
            $locationId = $warehouse[0]->location_id;

            if(($warehouse[0]->latitude !== 0 || $warehouse[0]->longitude !== 0)&&($warehouse[0]->latitude !== "" && $warehouse[0]->longitude !== "")){
                $userEmail = getCurrentUser()->user_email;

                $wpdb->query("INSERT INTO `".$wpdb->prefix."places_locator`
                            (`post_id`, `feature`, `post_status`, `post_type`, `post_title`, `lat`, `long`, `street_number`, `street_name`, `street`, `apt`, `city`, `state`, `state_long`, `zipcode`, `country`, `country_long`, `address`, `formatted_address`, `phone`, `fax`, `email`, `website`, `map_icon`)
                          VALUES
                            (".$postId.", 0, 'publish', 'product', '".$skuDescription."', ".$warehouse[0]->latitude.", ".$warehouse[0]->longitude.", 0, '".$wareHouseLocation."', '".$wareHouseLocation."', '', '', '', '', '', '', '','".$wareHouseLocation."', '".$wareHouseLocation."', '', '', '".$userEmail."', '', '_default.png');");

            }else{
                $userEmail = getCurrentUser()->user_email;

                $wpdb->query("INSERT INTO `".$wpdb->prefix."places_locator`
                            (`post_id`, `feature`, `post_status`, `post_type`, `post_title`, `lat`, `long`, `street_number`, `street_name`, `street`, `apt`, `city`, `state`, `state_long`, `zipcode`, `country`, `country_long`, `address`, `formatted_address`, `phone`, `fax`, `email`, `website`, `map_icon`)
                          VALUES
                            (".$postId.", 0, 'publish', 'product', '".$skuDescription."', 0, 0, 0, '".$wareHouseLocation."', '".$wareHouseLocation."', '', '', '', '', '', '', '','".$wareHouseLocation."', '".$wareHouseLocation."', '', '', '".$userEmail."', '', '_default.png');");
            }

        }

    }

    function setWareHouse($post_id, $warehouseLocationId, $warehouseLocationAddress, $distributorID){
        global $wpdb;

        $user = getCurrentUser();
        $date = getFormatDate();

        if($wpdb->check_connection()){

            $totalIDs = $wpdb->get_results("SELECT w.`warehouse_id` as warehouse_id
                                                 FROM `ot_custom_warehouse` AS w INNER JOIN `ot_custom_distributor_warehouse` AS dw ON w.`warehouse_id` = dw.`warehouse_id`
                                                 WHERE w.`warehouse_file_id` = '".$warehouseLocationId."' and dw.`distributor_id` = ".$distributorID.";");
            if(sizeof($totalIDs) !== 0){
                $warehouse_id = $totalIDs[0]->warehouse_id;

                $wpdb->query("INSERT INTO `ot_custom_warehouse_product`
                                (`warehouse_id`,
                                `product_id`,
                                `added_by`,
                                `added_date`)
                              VALUES
                                (".$warehouse_id.",
                                ".$post_id.",
                                ".$user->ID.",
                                '".$date."');");

            }else{
                errorMessage('Some warehouses must be updated');
                $wpdb->query("INSERT INTO `ot_custom_warehouse`
                                (`warehouse_name`,
                                `added_by`,
                                `added_date`,
                                `warehouse_file_id`)
                             VALUES
                            ('', 
                            ".$user->ID.",
                            '".$date."',
                            '".$warehouseLocationId."');");

                $warehouseId = $wpdb->insert_id;

                $wpdb->query("INSERT INTO `ot_custom_warehouse_location`
                                (`warehouse_id`,
                                `zipcode`,
                                `latitude`,
                                `longitude`,
                                `location`,
                                `city`,
                                `added_by`,
                                `added_date`)
                              VALUES
                                (".$warehouseId.",
                                '',
                                '',
                                '',
                                '".$warehouseLocationAddress."',
                                '',
                                ".$user->ID.",
                                '".$date."');");

                $wpdb->query("INSERT INTO `ot_custom_distributor_warehouse`
                                (`distributor_id`,
                                `warehouse_id`,
                                `added_by`,
                                `added_date`)
                              VALUES
                                (".$distributorID.",
                                ".$warehouseId.",
                                ".$user->ID.",
                                '".$date."');");

            }
        }

    }

    function zerofill($mStretch, $iLength = 2)
    {
        $sPrintString = '%0' . (int)$iLength . 's';
        return sprintf($sPrintString, $mStretch);
    }

    function createDistributor($distributorName,$locationDistributor, $taxIdDistributor){
        global $wpdb;

        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("INSERT INTO `ot_custom_distributor`
                        (`distributor_name`, `location`,`tax_id`, `added_by`, `added_date`)
                        VALUES
                        ('".$distributorName."', '".$locationDistributor."', '".$taxIdDistributor."', ".$userId.", '".$date."');");
        }
    }

    function deleteDistributor($distributorID){
        global $wpdb;

        if($wpdb->check_connection()){
            $wpdb->query("DELETE FROM `ot_custom_distributor` WHERE `distributor_id` = ".$distributorID.";");
        }
    }

    function approveDistributor($distributorID){
        global $wpdb;

        if($wpdb->check_connection()){
            $wpdb->query(" UPDATE `ot_custom_distributor` SET `status` = 'approved' WHERE `distributor_id` = ".$distributorID.";");
        }
    }

    function addUserDistributor($userId, $distributorID){
        global $wpdb;

        if($wpdb->check_connection()){

            $user = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."users` WHERE `ID` = ".$userId.";");
            $wpdb->query("INSERT INTO `ot_custom_distributor_user`
                                (`distributor_user_username`,
                                `distributor_user_fullname`,
                                `distributor_user_distributor_id`,
                                `distributor_user_userid`,
                                `distributor_user_added_by`,
                                `distributor_user_added_date`)
                         VALUES
                                ('".$user[0]->user_login."',
                                 '".$user[0]->display_name."',
                                  ".$distributorID.",
                                  ".$userId.",
                                  ".getCurrentUser()->ID.",
                                  '".getFormatDate()."');");
        }
    }

    function deleteUserDistributor($userId, $distributorID){
        global $wpdb;

        if($wpdb->check_connection()){            
            $wpdb->query("DELETE FROM `ot_custom_distributor_user`
                          WHERE `distributor_user_userid` = ".$userId." and `distributor_user_distributor_id` =".$distributorID.";");
        }
    }

    function approvedUserDistributor($userId){
        global $wpdb;

        if($wpdb->check_connection()) {
            $wpdb->query("UPDATE `ot_custom_distributor_user`
                          SET
                        `distributor_user_edited_by` = " . getCurrentUser()->ID . ",
                        `distributor_user_edited_date` = '" . getFormatDate() . "',
                        `status` = 'approved'
                        WHERE `distributor_user_userid` = " . $userId . ";");

        }
    }

    function createWarehouse($name, $zipCode, $latitude,$longitude, $location, $city, $distributorID,$file_id){

        global $wpdb;

        if($wpdb->check_connection()){

            $wpdb->query("INSERT INTO `ot_custom_warehouse`
                                (`warehouse_name`,
                                `added_by`,
                                `added_date`,
                                `warehouse_file_id`)
                             VALUES
                            ('".$name."', 
                            ".getCurrentUser()->ID.",
                            '".getFormatDate()."',
                            '".$file_id."');");

            $warehouseId = $wpdb->insert_id;

            $wpdb->query("INSERT INTO `ot_custom_warehouse_location`
                                (`warehouse_id`,
                                `zipcode`,
                                `latitude`,
                                `longitude`,
                                `location`,
                                `city`,
                                `added_by`,
                                `added_date`)
                              VALUES
                                (".$warehouseId.",
                                '".$zipCode."',
                                '".$latitude."',
                                '".$longitude."',
                                '".$location."',
                                '".$city."',
                                ".getCurrentUser()->ID.",
                                '".getFormatDate()."');");

            $wpdb->query("INSERT INTO `ot_custom_distributor_warehouse`
                                (`distributor_id`,
                                `warehouse_id`,
                                `added_by`,
                                `added_date`)
                              VALUES
                                (".$distributorID.",
                                ".$warehouseId.",
                                ".getCurrentUser()->ID.",
                                '".getFormatDate()."');");

            return true;
        }
        return false;
    }

    function deleteWarehouse($warehouseId, $distributorID){
        global $wpdb;

        if($wpdb->check_connection()){
            $wpdb->query("DELETE FROM `ot_custom_distributor_warehouse`
                              WHERE `warehouse_id` = ".$warehouseId." and `distributor_id` =".$distributorID.";");
        }
    }

    function updateDistributor($distributorId,$locationDistributor, $taxIdDistributor){
        global $wpdb;

        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("UPDATE `ot_custom_distributor`
                          SET
                            `location` = '".$locationDistributor."',
                            `tax_id` = '".$taxIdDistributor."',
                            `edited_by` = ".$userId.",
                            `edited_date` = '".$date."'
                          WHERE `distributor_id` =".$distributorId.";");
        }
    }

    function updateWarehouse($IdWarehouse, $zipCode, $latitude,$longitude, $location, $city, $file_id, $name){

        global $wpdb;

        if($wpdb->check_connection()){

            $wpdb->query("UPDATE `ot_custom_warehouse`
                          SET
                            `warehouse_name` = '".$name."',
                            `warehouse_file_id` = '".$file_id."'                            
                          WHERE `warehouse_id` =".$IdWarehouse.";");

            $wpdb->query("UPDATE `ot_custom_warehouse_location`
                          SET                        
                            `zipcode` = '".$zipCode."',
                            `latitude` = '".$latitude."',
                            `longitude` = '".$longitude."',
                            `location` = '".$location."',
                            `city` = '".$city."',
                            `edited_by` = ".getCurrentUser()->ID.",
                            `edited_date` = '".getFormatDate()."'
                          WHERE `warehouse_id` =".$IdWarehouse.";");

            $productsID= $wpdb->get_results("SELECT `product_id` FROM `ot_custom_warehouse_product` WHERE  `warehouse_id` =".$IdWarehouse.";");

            foreach ($productsID as $productID){

                $query = "UPDATE `".$wpdb->prefix."places_locator` SET `lat` = ".$latitude.", `long` = ".$longitude.", `city` = '".$city."', `zipcode` = '".$zipCode."', `address` = '".$location."', `formatted_address` = '".$location."' WHERE `post_id` = ".$productID->product_id.";";

                $wpdb->query($query);
            }

            return true;
        }
        return false;
    }

    function updateProductOfferList($idProductOffer, $status)
    {
        global $wpdb;
        global $current_user;
        $current_user =  wp_get_current_user();
        $formatDate = date("Ymdhis");

        if ($wpdb->check_connection()) {
            $wpdb->query("UPDATE `ot_custom_offer_information`
                                           SET
                                           `status` = '$status',
                                           `edited_date` = '$formatDate',
                                           `edited_by` = $current_user->ID
                                           WHERE `offer_information_id`=" . $idProductOffer);

            $requestInformation = $wpdb->get_results("SELECT * 
                                         FROM `ot_custom_offer_information`
                                         WHERE `offer_information_id` = " . $idProductOffer);

            $productId = $requestInformation[0]->product_id;
            $quantity = $requestInformation[0]->quantity;
            if($status == "approve"){
                $stock = get_post_meta( $productId , '_stock' );
                $newTotal = $stock[0] - $quantity;
                update_post_meta( $productId, '_stock', $newTotal );

            }           
        }
    }

    function updateProductQuantity($productId,$quantity){
        global $wpdb;
    
        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("UPDATE `ot_custom_inventory_file_items`
                                SET
                                `quantity` = ".$quantity.",
                                `edited_date` = '".$date."',
                                `edited_by` = ".$userId."
                                WHERE `inventory_file_item_id` =".$productId.";");
        }
    }

    function updateRequestInformation($requestInformationId, $state){
        global $wpdb;
    
        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("update `ot_custom_request_information` set `status` = '".$state."' where request_information_id = ".$requestInformationId.";");
        }
    }

    function rejectProductsFiles($idProductsFile){

        global $wpdb;

        $isConnected = $wpdb->check_connection();
        $overallProcess = true;

        if($isConnected){

            foreach ($idProductsFile as $productFileID) {
                if($wpdb->check_connection()){
                    $wpdb->query("UPDATE `ot_custom_inventory_file` SET `status` = 'reject' WHERE `inventory_id` = ".$productFileID." ;");
                }
            }
        }

        return $overallProcess;
    }

    function updateProductFileItem($post){
        global $wpdb;

        if($wpdb->check_connection()){

            if($wpdb->check_connection()){
                $wpdb->query("UPDATE `ot_custom_inventory_file_items`
                              SET                             
                                `lot_number` = '".$post['lot']."',                                
                                `quantity` = ".$post['quantity'].",                               
                                `edited_date` = ".getCurrentUser()->ID.",
                                `edited_by` = '".getFormatDate()."', 
                                `distributor_file_id` = '".$post['distributor_id']."',
                                `distributor_name` = '".$post['distributor_name']."',
                                `distributor_sku_id` = '".$post['distributor_sku_id']."',
                                `distributor_sku_description` = '".$post['distributor_sku_description']."',
                                `packaging_type` = '".$post['packaging_type']."',
                                `packaging_unit` = '".$post['packaging_unit']."',
                                `packaging_measure` = '".$post['packaging_measure']."',
                                `packaging_weight_lb` = ".$post['packaging_weight_lb'].",
                                `packaging_weight_kg` = ".$post['packaging_weight_kg'].",
                                `total_weight_lb` = ".$post['total_weight_lb'].",
                                `total_weight_kg` = ".$post['total_weight_kg'].",
                                `price_unit` = ".$post['price_unit'].",
                                `price_lb` = ".$post['price_lb'].",
                                `price_kg` = ".$post['price_kg'].",
                                `warehouse_location_id` = '".$post['warehouse_location_id']."',
                                `warehouse_location_address` = '".$post['warehouse_location_address']."'                                
                                WHERE `inventory_file_item_id` ='".$post['idProduct']."';");
            }
        }
    }

    function downloadInventory(){
        global $wpdb;

        $isConnected = $wpdb->check_connection();

        if ($isConnected) {

            $rows = $wpdb->get_results("SELECT * FROM ot_custom_product_post");

            if($wpdb->num_rows > 0){
                $phpExcel = new PHPExcel();
                $phpExcel->getProperties()->setTitle("Inventory");
                $phpExcel->getActiveSheet()->setTitle('Sheet1');

                $columnsTitles = array('Post ID','Line#', 'Distributor ID', 'Distributor Name', 'Distributor SKU ID', 'Distributor SKU Description', 'Lot#',
                    'PackagingType', 'Packaging Unit', 'Packaging Measure', 'Packaging Weight (lb)', 'Packaging Weight (kg)', 'Quantity',
                    'Total Weight (lb)', 'Total Weight (Kg)', 'Price / Unit', 'Price / lb', 'Price / Kg', 'Warehouse location ID', 'Warehouse Location Address');

                $phpExcel->setActiveSheetIndex(0)->setCellValue("A1",$columnsTitles[0]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('A')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("B1",$columnsTitles[1]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("C1",$columnsTitles[2]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('C')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("D1",$columnsTitles[3]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('D')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("E1",$columnsTitles[4]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("F1",$columnsTitles[5]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("G1",$columnsTitles[6]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("H1",$columnsTitles[7]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("I1",$columnsTitles[8]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('I')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("J1",$columnsTitles[9]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('J')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("K1",$columnsTitles[10]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('K')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("L1",$columnsTitles[11]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('L')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("M1",$columnsTitles[12]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('M')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("N1",$columnsTitles[13]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('N')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("O1",$columnsTitles[14]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('O')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("P1",$columnsTitles[15]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('P')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("Q1",$columnsTitles[16]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('Q')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("R1",$columnsTitles[17]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('R')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("S1",$columnsTitles[18]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('S')->setAutoSize(true);
                $phpExcel->setActiveSheetIndex(0)->setCellValue("T1",$columnsTitles[19]);
                $phpExcel->setActiveSheetIndex(0)->getColumnDimension('T')->setAutoSize(true);

                $rowStart = 2;
                foreach ($rows as $row) {

                    $post = get_post($row->post_id);
                    $post_meta_regular_price = get_post_meta($row->post_id, '_regular_price', true);
                    $post_meta_price = get_post_meta($row->post_id, '_price', true);
                    $post_meta_product_attributes = get_post_meta($row->post_id, '_product_attributes', true);


                    foreach ($post_meta_product_attributes as $product_atributte) {

                        if ($product_atributte["name"] == "Line #") {
                            $line = $product_atributte["value"];
                        } else if ($product_atributte["name"] == "Distributor ID") {
                            $distributorID = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Distributor Name"){
                            $distributorName = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Distributor SKU ID"){
                            $distributorSKUId = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Distributor SKU Description"){
                            $distributorSKUName = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Lot#"){
                            $lotNum = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Packaging Type"){
                            $packingType = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Packaging Unit"){
                            $packingUnit = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Packaging Measure"){
                            $packingMeasure = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Packaging Weight (lb)"){
                            $packingWeightLB = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Packaging Weight (kg)"){
                            $packingWeightKG = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Quantity"){
                            $quantity = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Total Weight (lb)"){
                            $totalWeightLB = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Total Weight (Kg)"){
                            $totalWeightKG = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Price / Unit"){
                                $priceUnit = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Price / lb"){
                            $priceLB = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Price / Kg"){
                            $priceKg = $product_atributte["value"];
                        } else if($product_atributte["name"] == "Warehouse location ID"){
                            $warehouseID = $product_atributte["value"];
                        }else if($product_atributte["name"] == "Warehouse Location Address"){
                            $warehouseName = $product_atributte["value"];
                        }
                    }

                    $phpExcel->setActiveSheetIndex(0)->setCellValue('A'.$rowStart, $row->post_id);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('B'.$rowStart, $line);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('C'.$rowStart, $distributorID);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('D'.$rowStart, $distributorName);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('E'.$rowStart, $distributorSKUId);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('F'.$rowStart, $distributorSKUName);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('G'.$rowStart, $lotNum);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('H'.$rowStart, $packingType);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('I'.$rowStart, $packingUnit);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('J'.$rowStart, $packingMeasure);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('K'.$rowStart, $packingWeightLB);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('L'.$rowStart, $packingWeightKG);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('M'.$rowStart, $quantity);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('N'.$rowStart, $totalWeightLB);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('O'.$rowStart, $totalWeightKG);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('P'.$rowStart, "$".$priceUnit);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('Q'.$rowStart, "$".$priceLB);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('R'.$rowStart, "$".$priceKg);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('S'.$rowStart, $warehouseID);
                    $phpExcel->setActiveSheetIndex(0)->setCellValue('T'.$rowStart, $warehouseName);

                    $rowStart++;

                }

                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="Inventory '.getFormatDate().'.xlsx"');
                header('Cache-Control: max-age=0');

                $objWriter = PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
                $objWriter->save('php://output');
                exit;

            }
        }else{
            $_GET['message-error'] = "No data!";
        }
    }

    function setPlaceLocatorUpload($postId, $skuDescription, $wareHouseLocation){
    global $wpdb;

    if($wpdb->check_connection()){

        $warehouse = $wpdb->get_results("SELECT * FROM ot_custom_warehouse_location WHERE location = '".$wareHouseLocation."'");
        $locationId = $warehouse[0]->location_id;

        if(($warehouse[0]->latitude !== '0' || $warehouse[0]->longitude !== '0')&&($warehouse[0]->latitude !== "" && $warehouse[0]->longitude !== "")){
            $userEmail = getCurrentUser()->user_email;

            $wpdb->query("UPDATE wp_places_locator SET post_title = '".$skuDescription."', lat ='".$warehouse[0]->latitude."' , `long` ='".$warehouse[0]->longitude."' , street_name='".$wareHouseLocation."', street='".$wareHouseLocation."', address='".$wareHouseLocation."', formatted_address='".$wareHouseLocation."' where post_id = ".$postId);

        }else{
            $userEmail = getCurrentUser()->user_email;

            $wpdb->query("UPDATE wp_places_locator SET post_title = '".$skuDescription."', lat ='0' , `long` ='0' , street_name='".$wareHouseLocation."', street='".$wareHouseLocation."', address='".$wareHouseLocation."', formatted_address='".$wareHouseLocation."' where post_id = ".$postId);
        }

    }

}