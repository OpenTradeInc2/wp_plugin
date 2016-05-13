<?php
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
require_once( ABSPATH . "wp-includes/pluggable.php" );
/*
Plugin Name: Open Trade 2.0
Plugin URI: http://URI_De_La_P?gina_Que_Describe_el_Plugin_y_Actualizaciones
Description: This plugin allows to process files for open trade products.
Version: 1.0
Author: Zeptoo
Author URI: http://www.zeptoo.com
License: GPL2
*/

/*  Copyright 2016 Zeptoo  (email : info@zeptoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

    /**
     * Register a main menu page.
     */

    add_action( 'admin_menu', 'admin_menu' );

    function admin_menu(){
        add_menu_page(__( 'Open Trade 2.0', 'textdomain' ), 'Open Trade 2.0', 'manage_options', 'open-trade-menu', 'open_trade_admin' , 'dashicons-migrate', 6 );
        add_submenu_page('open-trade-menu', 'Load Inventory', 'Load Inventory', 'manage_options', 'open-trade-menu' );
        add_submenu_page('open-trade-menu', 'Pending Approval', 'Pending Approval', 'manage_options', 'open-trade-approve', 'wpdocs_pending_approval_submenu_page_callback' );
    }

    function wpdocs_pending_approval_submenu_page_callback() {

        ?>
        <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Pending Approval Files</h3>
        <br>
            <?php
            if( isset($_GET['message-success']) ) {
                ?>
                <div id="message" class="updated">
                    <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                </div>
                <br>
                <?php
            }
            if( isset($_GET['message-error']) ) {
                ?>
                <div id="message" class="error">
                    <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                </div>
                <?php
            }
            ?>
        <form action="" method="post" enctype="multipart/form-data">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
            <select name="selectAction" id="bulk-action-selector-top">
                <option value="-1">Actions</option>
                <option value="approve" class="hide-if-no-js">Approve</option>
                <option value="reject">Reject</option>
                <input id="doaction" class="button action" value="Apply" type="submit" name="actionFileSubmit">
            </select>
        </div>
        <br class="clear">
        <table class="widefat" name="tablePendingFiles" id="idTablePendingFiles">
            <thead>
            <tr>
                <th><input type="checkbox"></th>
                <th>File Name</th>
                <th>Quantity of Products</th>
                <th>Upload Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th></th>
                <th>File Name</th>
                <th>Quantity of Products</th>
                <th>Upload Date</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            global $wpdb;

            $isConnected = $wpdb->check_connection();

            if($isConnected){
                $products_files =  $wpdb->get_results("SELECT `inventory_id`, `file_md5`, `items_count`, `added_date` FROM `ot_custom_inventory_file` WHERE `status` = 'pending_approval'");
                foreach ($products_files as $product_file) {
                    ?>
                    <tr>
                        <?php
                        echo "<td><input style='margin-left:8px;' type=\"checkbox\" name=\"idProduct[]\" value=". $product_file->inventory_id ."></td>";
                        echo "<td>" . $product_file->file_md5 . "</td>";
                        echo "<td>" . $product_file->items_count . "</td>";
                        echo "<td>" . $product_file->added_date . "</td>";
                        echo "<td>Pending Approval</td>";
                        echo "<td>".captcha()."</td>";
                        ?>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        </form>
        </div>
        <?php
    }

    function open_trade_admin()
    {
        ?>
        <div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Load New Inventory List</h3>
            <br>
            <form action="" method="post" enctype="multipart/form-data">
                Please select distributor:
                <select name="selectDistributor" id="bulk-action-selector-top">
                    <option value="-1">Distributors</option>
                    <?php
                        global $wpdb;
                        $isConnected = $wpdb->check_connection();
                        if($isConnected){
                            $distributors =  $wpdb->get_results("SELECT `distributor_id`, `distributor_name` FROM `ot_custom_distributor`");
                            foreach ($distributors as $distributor) {
                                echo "<option value=\"$distributor->distributor_id\" class=\"hide-if-no-js\">$distributor->distributor_name</option>";
                            }
                        }
                    ?>
                </select>
                <br>
                Please select file to upload:
                <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls">
                <input type="submit" value="Upload File" name="uploadFileSubmit">
            </form>

        <?php

        if( isset($_GET['message-success']) ) {
            ?>
            <div id="message" class="updated">
                <p><strong><?php _e($_GET['message-success']) ?></strong></p>
            </div>
            <?php
        }
        if( isset($_GET['message-error']) ) {
            ?>
            <div id="message" class="error">
                <p><strong><?php _e($_GET['message-error']) ?></strong></p>
            </div>
            <?php
        }
        if( isset($_GET['message-file-name']) ) {
            ?>
            <div id="message" class="updated">
                <p><strong>File name: <?php _e($_GET['message-file-name']) ?></strong></p>
            </div>
            <?php
        }
        if( isset($_GET['message-total-products']) ) {
            ?>
            <div id="message" class="updated">
                <p><strong>Total Products: <?php _e($_GET['message-total-products']) ?></strong></p>
            </div>
            <?php
        }
        if( isset($_GET['products-list']) ) {
            ?>
            <br>
            <table class="widefat">
                <thead>
                <tr>
                    <th>SKU ID</th>
                    <th>SKU Description</th>
                    <th>Product Line</th>
                    <th>Issue Type</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>SKU ID</th>
                    <th>SKU Description</th>
                    <th>Product Line</th>
                    <th>Issue Type</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
                </tfoot>
                <tbody>
                <?php

                $products = $_GET['products-list'];
                foreach ($products as $product) {
                    ?>
                    <tr>
                        <?php
                        echo "<td>" . $product[1] . "</td>";
                        echo "<td>" . $product[2] . "</td>";
                        echo "<td>" . $product[3] . "</td>";
                        echo "<td>" . $product[5] . "</td>";
                        echo "<td>" . $product[13] . "</td>";
                        echo "<td>" . $product[14] . "</td>";
                        ?>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <?php
        }
    ?>
    </div>
    <?php
    }

    function captcha(){
        return 'Prueba';
    }

    function phpAlert($msg) {
        $_GET['message-error'] = $msg;
    }

    function viewDetails(){
    ?>
        <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Detail</h3>
        <br>
    <?php
    }

    if(isset($_POST["uploadFileSubmit"])) {

        if (isset($_POST["selectDistributor"])) {
            $distributorID = $_POST['selectDistributor'];

            if ($distributorID == -1) {
                phpAlert('Please select one distributor!');
            } else {

                $target_dir = plugin_dir_path(__FILE__) . "uploads/";

                $filename = $_FILES['fileToUpload']['name'];
                $fileType = pathinfo($filename, PATHINFO_EXTENSION);

                $errors = array();
                $maxsize = 2097152;

                if ($fileType != 'xlsx' && $fileType != 'xls') {
                    $errors[] = 'Invalid file type. Only xlsx and xls types are accepted.';
                }

                if (($_FILES['fileToUpload']['size'] >= $maxsize) || ($_FILES["fileToUpload"]["size"] == 0)) {
                    $errors[] = 'File too large. File must be less than 2 megabytes.';
                }

                global $current_user;
                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                } else {
                    $errors[] = 'User not fount.';
                }

                if (count($errors) === 0) {

                    $formatDate = date("Ymdhis");
                    $user_dir = $target_dir . $current_user->user_login;

                    if (!file_exists($user_dir)) {
                        mkdir($user_dir);
                    }

                    $fullPatch = $user_dir . '/' . $formatDate . '_' . $_FILES["fileToUpload"]['name'];

                    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $fullPatch)) {
                        processFile($fullPatch, $filename, $current_user->ID, $formatDate, $distributorID);
                    } else {
                        $_GET['message-error'] = 'File was not uploaded';
                    }
                } else {
                    $_GET['message-error'] = $errors[0];
                }
            }
        }
    }

    if(isset($_POST["viewDetailsSubmit"])){
        viewDetails();
    }

    if(isset($_POST["actionFileSubmit"])){

        if(isset($_POST["selectAction"])) {
            $selectOption = $_POST['selectAction'];

            if ($selectOption == -1) {
                phpAlert('Please select one action!');
            } else if ($selectOption == 'approve') {

                if (isset($_POST['idProduct'])) {
                    $idProductsFile = $_POST["idProduct"];
                    $result = approveProductsFiles($idProductsFile);
                } else {
                    phpAlert('Please select a file to approve!');
                }

            } else if ($selectOption == 'reject') {
                phpAlert('Pending implementation.');
            }
        }
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
         INNER JOIN `".$wpdb->prefix."postmeta` as post_meta on post.`ID`= post_meta.`post_id`
         WHERE post.`post_content`='".$product->sku_description."' and post_meta.`meta_value` in('".$product->sku_id."');");

        if($totalProducts[0]->total == 0){
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
            update_post_meta($post_id, '_sku', $product->sku_id);
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

        $result = $wpdb->get_results("SELECT post.`ID` as ID
         FROM `".$wpdb->prefix."posts` as post
         INNER JOIN `".$wpdb->prefix."postmeta` as post_meta on post.`ID`= post_meta.`post_id`
         WHERE post.`post_content`='".$product->sku_description."' and post_meta.`meta_value` in('".$product->sku_id."');");

        return $result[0]->ID;
    }

    function processFile($fullPatch, $filename, $current_user, $formatDate, $distributorID){

        $allDataInSheet = readContentFile($fullPatch);
        $arrayCount = count($allDataInSheet)-1;

        if(validateHeaders($allDataInSheet)){

            $products = getProductsList($allDataInSheet, $arrayCount);
            saveProducts($products, $filename, $arrayCount, $current_user, $formatDate, $distributorID);

            $_GET['message-success']='Upload success, please approve the file to update de inventory.';
            $_GET['message-file-name'] = $filename;
            $_GET['message-total-products'] = $arrayCount;

        }else{
            $_GET['message-error']= 'The Format File not contain the headers required.';
        }
        $_GET['products-list'] = $products;
    }

    function readContentFile($fullPatch){

        set_include_path(plugin_dir_path(__FILE__) . 'Classes/');
        include plugin_dir_path(__FILE__) . '/Classes/PHPExcel/IOFactory.php';

        try {
            $objPHPExcel = PHPExcel_IOFactory::load($fullPatch);
        } catch (Exception $e) {
            $_GET['message-error']='Error loading file "' . pathinfo($fullPatch, PATHINFO_BASENAME) . '": ' . $e->getMessage();
        }

        $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        return $allDataInSheet;

    }

    function validateHeaders($allDataInSheet){

        $headers = $allDataInSheet[1];
        $result = true;

        if (!in_array('SKU ID', $headers)) {
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
        }

        return $result;
    }

    function getProductsList($allDataInSheet, $arrayCount){

        $products = array();
        for ($i = 2; $i <= $arrayCount+1; $i++) {
            $product = array();
            $skuID = trim($allDataInSheet[$i]["A"]);
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
            $product[15]=$category;

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

                $price = str_replace("$", "", $product[14]);
                $wpdb->query("INSERT INTO ot_custom_inventory_file_items 
                                                  (`inventory_file_id`, `sku_id`, `sku_description`, `product_line`, `lot_number`, `issue_type`, `li_specialist`, `warehouse`, `city`, `zipcode`, `lmd`, `id_month`, `days_under_current_path`, `quantity_libs`, `sum_quantity`, `total_cost`, `added_by`, `added_date`, `deleted`, `status`, `category`,`distributor_id`) 
                                       VALUES 
                                                  ('$idProductFile','$product[1]','$product[2]','$product[3]','$product[4]','$product[5]','$product[6]','$product[7]','$product[8]','$product[9]','$product[10]','$product[11]','$product[12]',0 ,$product[13],$price,'$current_user', '$formatDate',0 , 'pending_approval', '$product[15]',$distributorID)");
            }
        }
    }
?>