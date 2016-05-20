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

        $products = $wpdb->get_results("SELECT `inventory_file_item_id`, `inventory_file_id`, `sku_id`, `sku_description`, `product_line`, `lot_number`, `issue_type`, `li_specialist`, `warehouse`, `city`, `zipcode`, `lmd`, `id_month`, `days_under_current_path`, `quantity_libs`, `sum_quantity`, `total_cost`, `added_by`, `added_date`,`edited_date`, `edited_by`,`deleted`, `status`, `category`, `distributor_id` 
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

        $totalProducts = $wpdb->get_results("SELECT count(post.`ID`) as total
             FROM `".$wpdb->prefix."posts` as post             
             WHERE post.`post_content`='".$product->sku_description."';");
        // 1 ,2 Description and package size
        if($totalProducts[0]->total == 0){
            $result = true;
        }else{
            //3 location
            $productPost = $wpdb->get_results("SELECT *
             FROM `".$wpdb->prefix."posts` as post             
             WHERE post.`post_content`='".$product->sku_description."';");

            $productAttributes = get_post_meta( $productPost[0]->ID, '_product_attributes', true);
            $warehouse = '';
            $city = '';

            foreach ($productAttributes as $productAttribute){
                if($productAttribute['name'] == 'Warehouse'){
                    $warehouse = $productAttribute['value'];
                }
                if($productAttribute['name'] == 'City'){
                    $city = $productAttribute['value'];
                }
            }
            if($warehouse !== $product->warehouse or $city !== $product->city){
                $result = true;

            }else{
                // 4 distributor
                $totalDistributor = $wpdb->get_results("SELECT count(`id_sku_distributor`) as total
                                                        FROM `ot_custom_distributor_sku`            
                                                        WHERE `id_sku_product`=".$product->sku_id." and `id_distributor` = ".$product->distributor_id.";");
                if($totalDistributor[0]->total == 0){
                    $result = true;
                }
            }
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
            $price = str_replace("$", "", $product->total_cost);

            $product_line  = array( 'name' => 'Product Line', 'value' => $product->product_line, 'position'=>'2', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lot  = array( 'name' => 'Lot #', 'value' => $product->lot_number, 'position'=>'1', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $issue_type  = array( 'name' => 'Issue Type', 'value' => $product->issue_type, 'position'=>'2', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $specialist = array( 'name' => 'LI Specialist', 'value' => $product->li_specialist, 'position'=>'3', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $warehouse = array( 'name' => 'Warehouse', 'value' => $product->warehouse, 'position'=>'4', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $city = array( 'name' => 'City', 'value' => $product->city, 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $zipCode = array( 'name' => 'Zip Code', 'value' => $product->zipcode, 'position'=>'5', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $lmd = array( 'name' => 'LMD', 'value' => $product->lmd, 'position'=>'6', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $id_month = array( 'name' => 'ID Month', 'value' => $product->id_month, 'position'=>'7', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );
            $days_under_current_path = array( 'name' => 'Days Under Current Path', 'value' => $product->days_under_current_path, 'position'=>'8', 'is_visible'=>'1', 'is_variation'=>'0', 'is_taxonomy'=>'0' );

            $product_attributes = array($product_line, $lot, $issue_type, $specialist, $warehouse, $city, $zipCode, $lmd, $id_month, $days_under_current_path);

            $post = array(
                'post_author' => $user_id,
                'post_content' => $product->sku_description,
                'post_status' => "publish",
                'post_title' => $product->sku_description,
                'post_parent' => '',
                'post_type' => "product",
                'post_name' => $product->sku_id
            );

            $oTSkuID = createOTSkuID($product->sku_id, $product->distributor_id);
            $post_id = wp_insert_post( $post, $wp_error );

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
            update_post_meta( $post_id, '_weight', "" );
            update_post_meta( $post_id, '_length', "" );
            update_post_meta( $post_id, '_width', "" );
            update_post_meta( $post_id, '_height', "" );
            update_post_meta($post_id, '_sku', $oTSkuID);
            update_post_meta( $post_id, '_product_attributes', $product_attributes);
            update_post_meta( $post_id, '_sale_price_dates_from', "" );
            update_post_meta( $post_id, '_sale_price_dates_to', "" );
            update_post_meta( $post_id, '_price', $price );
            update_post_meta( $post_id, '_sold_individually', "yes" );
            update_post_meta( $post_id, '_manage_stock', "yes" );
            update_post_meta( $post_id, '_backorders', "no" );
            update_post_meta( $post_id, '_stock', $product->sum_quantity );
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
        $totalStock =$postMeta + $product->sum_quantity;

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

        $distributorInfo = $wpdb->get_results("SELECT *
                                                FROM `ot_custom_distributor_sku`            
                                                WHERE `id_sku_product`=".$product->sku_id." and `id_distributor` = ".$product->distributor_id.";");

        $result = $wpdb->get_results("SELECT post.`ID` as ID
                 FROM `".$wpdb->prefix."posts` as post
                 INNER JOIN `".$wpdb->prefix."postmeta` as post_meta on post.`ID`= post_meta.`post_id`
                 WHERE post.`post_content`='".$product->sku_description."' and post_meta.`meta_value` in('".$distributorInfo[0]->id_sku_distributor."');");

        return $result[0]->ID;
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

    function createOTSkuID($productSku, $distributorID ){
        global $wpdb;

        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("INSERT INTO `ot_custom_distributor_sku`
                        (`id_sku_product`,
                        `id_distributor`,
                        `added_by`,
                        `added_date`)
                        VALUES
                        (".$productSku.", 
                         ".$distributorID.", 
                         ".$userId.", 
                         '".$date."');");
        }
        $result = zerofill($wpdb->insert_id,7);
        return $result;
    }

    function zerofill($mStretch, $iLength = 2)
    {
        $sPrintString = '%0' . (int)$iLength . 's';
        return sprintf($sPrintString, $mStretch);
    }

    function createDistributor($distributorName){
        global $wpdb;

        if($wpdb->check_connection()){
            $userId = getCurrentUser()->ID;
            $date = getFormatDate();
            $wpdb->query("INSERT INTO `ot_custom_distributor`
                        (`distributor_name`, `added_by`, `added_date`)
                        VALUES
                        ('".$distributorName."', ".$userId.", '".$date."');");
        }
    }

    function deleteDistributor($distributorID){
        global $wpdb;

        if($wpdb->check_connection()){
            $wpdb->query("DELETE FROM `ot_custom_distributor` WHERE `distributor_id` = ".$distributorID.";");
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

