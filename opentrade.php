<?php
remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );
require_once( ABSPATH . "wp-includes/pluggable.php" );
include plugin_dir_path(__FILE__) . '/includes/admin.php';
include plugin_dir_path(__FILE__) . '/includes/file-upload.php';
include plugin_dir_path(__FILE__) . '/includes/process-file.php';
include plugin_dir_path(__FILE__) . '/includes/products-admin.php';
include plugin_dir_path(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
set_include_path(plugin_dir_path(__FILE__) . 'Classes/');


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

    add_action( 'admin_init', 'my_remove_menu_pages' );
    function my_remove_menu_pages() {

        if ( current_user_can( 'open-trade-contributor' ) ) {
            remove_menu_page( 'tools.php' ); // Tools
            remove_menu_page( 'users.php' ); // Users
            remove_menu_page( 'index.php' ); // Dashboard
            remove_menu_page( 'edit.php' ); // post
            remove_menu_page( 'upload.php' ); //media
            remove_menu_page( 'edit.php?post_type=page' ); //pages
            remove_menu_page( 'edit-comments.php' );// comments
            remove_menu_page( 'woocommerce' ); //woocommerce
            remove_menu_page( 'themes.php' ); //themes
            remove_menu_page( 'plugins.php' ); //plugins
            remove_menu_page( 'options-general.php' ); //settings

        }
    }

    add_action( 'admin_menu', 'admin_menu' );
    function admin_menu(){

        add_menu_page(__( 'Open Trade 2.0', 'textdomain' ), 'Open Trade 2.0', 'manage_options', 'open-trade-menu', 'open_trade_admin' , 'dashicons-migrate', 6 );
        add_submenu_page('open-trade-menu', 'Load Inventory', 'Load Inventory', 'manage_options', 'open-trade-menu' );
        add_submenu_page('open-trade-menu', 'Pending Approval', 'Pending Approval', 'manage_options', 'open-trade-approve', 'wpdocs_pending_approval_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Distributors', 'Distributors', 'manage_options', 'open-trade-distributors', 'wpdocs_distributors_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Request Information', 'Request Information', 'manage_options', 'open-trade-reques_information', 'wpdocs_reques_information_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Post Offer', 'Post Offer', 'manage_options', 'open-trade-post-offer', 'wpdocs_post_offer_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Purchase Order', 'Purchase Order', 'manage_options', 'open-trade-purchase-order', 'wpdocs_purchase_order_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Download Inventory', 'Download Inventory', 'manage_options', 'open-trade-download-inventory', 'wpdocs_download_inventory_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Upload Inventory', 'Upload Inventory', 'manage_options', 'open-trade-upload-inventory', 'wpdocs_upload_inventory_submenu_page_callback' );
    }

    function open_trade_admin(){
    ?>
    <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Load New Inventory List</h3>
        <br>
        <form action="" method="post" enctype="multipart/form-data">
            <table>
                <thead>
                <tr>
                    <th align="left" scope="row"><label for="selectDistributor">Please select distributor: <span class="description">(required)</span></label></th>
                    <td><select name="selectDistributor" id="selectDistributor">
                            <option value="-1">Distributors</option>
                            <?php
                            global $wpdb;

                            if($wpdb->check_connection()){
                                $distributors =  $wpdb->get_results("SELECT `distributor_id`, `distributor_name` FROM `ot_custom_distributor`");
                                foreach ($distributors as $distributor) {
                                    echo "<option value=\"$distributor->distributor_id\">$distributor->distributor_name</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th  align="left" scope="row"><label for="fileToUpload">Please select file to upload: <span class="description">(required)</span></label></th>
                    <td><input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls" /></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td><input type="submit" value="Upload File" name="actionUploadFile"></td>
                </tr>
                </thead>
            </table>
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
                    <th>Line Number</th>
                    <th>SKU ID</th>
                    <th>SKU Description</th>
                    <th>Quantity</th>
                    <th>Total Weight (lb)</th>
                    <th>Total Weight (Kg)</th>
                    <th>Price - Unit</th>
                    <th>Price - Lb</th>
                    <th>Price - Kg</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Line Number</th>
                    <th>SKU ID</th>
                    <th>SKU Description</th>
                    <th>Quantity</th>
                    <th>Total Weight (lb)</th>
                    <th>Total Weight (Kg)</th>
                    <th>Price - Unit</th>
                    <th>Price - Lb</th>
                    <th>Price - Kg</th>
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
                        echo "<td>" . $product[4] . "</td>";
                        echo "<td>" . $product[5] . "</td>";
                        echo "<td>" . $product[12] . "</td>";
                        echo "<td>" . $product[13] . "</td>";
                        echo "<td>" . $product[14] . "</td>";
                        echo "<td>" . $product[15] . "</td>";
                        echo "<td>" . $product[16] . "</td>";
                        echo "<td>" . $product[17] . "</td>";
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

    function wpdocs_pending_approval_submenu_page_callback() {
        if( isset($_GET['view-details']) and $_GET['view-details'] == true ) {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>File Detail</h3>
                <form action="" method="post" enctype="multipart/form-data">
                <input id="actionDetails" class="button action" value="Back File List" type="submit" name="actionBackFileList">
                </form>
                <?php
                global $wpdb;

                $idProductFile = $_GET['view-details-idProductFile'];

                if (isset($_GET['view-details-idProductFile'])) {
                    $fileInformation = $wpdb->get_results("SELECT files.*, user.user_login FROM `ot_custom_inventory_file` as files INNER JOIN `".$wpdb->prefix."users` as user ON `added_by` = user.ID   WHERE `inventory_id` = ".$idProductFile.";");
                    $products = $wpdb->get_results("SELECT * FROM `ot_custom_inventory_file_items` WHERE `inventory_file_id` = $idProductFile;");
                    $newProducts = 0;
                    $updateProducts = 0;
                    foreach ($products as $product){

                    if(isNewProduct($product)){
                        $newProducts++;
                    }else{
                        $updateProducts++;
                    }
                }
                ?>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Products Number</th>
                        <th>New Products</th>
                        <th>Update Products</th>
                        <th>Added by</th>
                        <th>Added Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                        <td><?php _e($fileInformation[0]->file_md5) ?></td>
                        <td><?php _e($fileInformation[0]->items_count) ?></td>
                        <td><?php _e($newProducts) ?></td>
                        <td><?php _e($updateProducts) ?></td>
                        <td><?php _e($fileInformation[0]->user_login) ?></td>
                        <td><?php _e($fileInformation[0]->added_date) ?></td>
                        <td>Pending Approval</td>
                    </tbody>
                </table>
                <br>
                <h3>Product List</h3>
                <?php
                }
                ?>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th>Line Number</th>
                        <th>Distributor ID</th>
                        <th>Distributor Name</th>
                        <th>SKU ID</th>
                        <th>SKU Description</th>                        
                        <th>PackagingType</th>
                        <th>Packaging Unit</th>                        
                        <th>Quantity</th>						
						<th>Price - Unit</th>						
						<th>Is New Product</th>
                        <th>Edit</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Line Number</th>
                        <th>Distributor ID</th>
                        <th>Distributor Name</th>
                        <th>SKU ID</th>
                        <th>SKU Description</th>                        
                        <th>PackagingType</th>
                        <th>Packaging Unit</th>                        
                        <th>Quantity</th>						
						<th>Price - Unit</th>						
						<th>Is New Product</th>
                        <th>Edit</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    foreach ($products as $product) {
                        $isNewProduct = 'No';
                        if(isNewProduct($product)){
                            $isNewProduct = 'Yes';
                        }
                        $distributor = $wpdb->get_results("SELECT * FROM `ot_custom_distributor` WHERE `distributor_id` = $product->distributor_id;");
                        ?>
                        <tr>
                            <?php
                            echo "<td>"  . $product->line_number . "</td>";
                            echo "<td>"  . $product->distributor_file_id . "</td>";
                            echo "<td>"  . $product->distributor_name . "</td>";
                            echo "<td>"  . $product->distributor_sku_id . "</td>";
                            echo "<td>"  . $product->distributor_sku_description . "</td>";                            
                            echo "<td>"  . $product->packaging_type . "</td>";
                            echo "<td>"  . $product->packaging_unit . "</td>";                            
                            echo "<td>"  . $product->quantity . "</td>";
                            echo "<td>$" . $product->price_unit . "</td>";
							echo "<td>" . $isNewProduct . "</td>";
                            echo "<td><form action=\"\" method=\"post\" enctype=\"multipart/form-data\"><input type=\"hidden\" name=\"idProductFile\" value=\"$idProductFile\"><input type=\"hidden\" name=\"idProduct\" value=\"$product->inventory_file_item_id\"><input name='actionUpdateItem' id=\"actionUpdateItem\" class=\"button action\" value=\"Edit\" type=\"submit\"></form></td>";
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
        else if( isset($_GET['view-item-details']) and $_GET['view-item-details'] == true ) {
            ?><div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Update Product</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <input id="actionu" class="button action" value="Back File Detail" type="submit" name="actionBackFileDetail">
                <input type="hidden" name="idProductFile" value="<?php _e($_GET['idProductFile']) ?>">
                <input type="hidden" name="idProduct" value="<?php _e($_GET['idProduct']) ?>">
                <p>Edit Product Information.</p>
                <?php
                if (isset($_GET['message-error'])) {
                    ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                    </div>
                    <?php
                }
                if( isset($_GET['message-success']) ) {
                    ?>
                    <div id="message" class="updated">
                        <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                    </div>
                    <?php
                }
                ?>
                <table>
                    <thead>
                    <tr>
                        <th align="left" scope="row"><label for="distributor_id">Distributor ID <span class="description">(required)</span></label></th>
                        <td><input name="distributor_id" id="distributor_id" value="<?php echo $_POST['distributor_id'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                        <th align="left" scope="row"><label for="distributor_name">Distributor Name <span class="description">(required)</span></label></th>
                        <td><input name="distributor_name" id="distributor_name" value="<?php echo $_POST['distributor_name'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="distributor_sku_id">Distributor SKU ID <span class="description">(required)</span></label></th>
                        <td><input name="distributor_sku_id" id="distributor_sku_id" value="<?php echo $_POST['distributor_sku_id'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text"></td>
                        <th align="left" scope="row"><label for="distributor_sku_description">Distributor SKU Description <span class="description">(required)</span></label></th>
                        <td><input name="distributor_sku_description" id="distributor_sku_description" value="<?php echo $_POST['distributor_sku_description'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="lot">Lot#</label></th>
                        <td><input name="lot" id="distributor_id" value="<?php echo $_POST['lot'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="packaging_type">Packaging Type <span class="description">(required)</span></label></th>
                        <td><input name="packaging_type" id="packaging_type" value="<?php echo $_POST['packaging_type'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="packaging_unit">Packaging Unit <span class="description">(required)</span></label></th>
                        <td><input name="packaging_unit" id="packaging_unit" value="<?php echo $_POST['packaging_unit'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="packaging_measure">Packaging Measure<span class="description">(required)</span></label></th>
                        <td><input name="packaging_measure" id="packaging_measure" value="<?php echo $_POST['packaging_measure'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="packaging_weight_lb">Packaging Weight (lb)</label></th>
                        <td><input name="packaging_weight_lb" id="packaging_weight_lb" value="<?php echo $_POST['packaging_weight_lb'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="packaging_weight_kg">Packaging Weight (kg)</label></th>
                        <td><input name="packaging_weight_kg" id="packaging_weight_kg" value="<?php echo $_POST['packaging_weight_kg'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="quantity">Quantity <span class="description">(required)</span></label></th>
                        <td><input name="quantity" id="quantity" value="<?php echo $_POST['quantity'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="price_unit">Price / Unit<span class="description">(required)</span></label></th>
                        <td><input name="price_unit" id="price_unit" value="<?php echo $_POST['price_unit'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="total_weight_lb">Total Weight (lb)</label></th>
                        <td><input name="total_weight_lb" id="total_weight_lb" value="<?php echo $_POST['total_weight_lb'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="total_weight_kg">Total Weight (Kg)</label></th>
                        <td><input name="total_weight_kg" id="total_weight_kg" value="<?php echo $_POST['total_weight_kg'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="price_lb">Price / lb</label></th>
                        <td><input name="price_lb" id="price_lb" value="<?php echo $_POST['price_lb'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="price_kg">Price / Kg</label></th>
                        <td><input name="price_kg" id="price_kg" value="<?php echo $_POST['price_kg'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="warehouse_location_id">Warehouse location ID</label></th>
                        <td><input name="warehouse_location_id" id="warehouse_location_id" value="<?php echo $_POST['warehouse_location_id'];?>" type="text" ></td>
                        <th align="left" scope="row"><label for="warehouse_location_address">Warehouse Location Address</label></th>
                        <td><input name="warehouse_location_address" id="warehouse_location_address" value="<?php echo $_POST['warehouse_location_address'];?>" type="text"></td>
                    </tr>
                    </thead>
                    <tr>
                        <th scope="row"></th>
                        <td><input name="actionUpdateProduct" id="createusersub" class="button button-primary" value="Update Product" type="submit"></td>
                        <th></th>
                    </tr>
                </table>
            </form>
            </div>
            <?php
        }
        else {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>Pending Approval Files</h3>
                <br>
            <?php
                if (isset($_GET['message-success'])) {
                    ?>
                    <div id="message" class="updated">
                        <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                    </div>
                    <br>
                    <?php
                }
                if (isset($_GET['message-error'])) {
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
                            <input id="doaction" class="button action" value="Apply" type="submit" name="actionProducts">
                        </select>
                    </div>
                    <script>
                        jQuery(function ($) {
                            $('#selectAllValuesHead').on('click', function () {
                                $(':checkbox').prop("checked", $(this).is(':checked'));
                            });
                        })
                    </script>
                    <script language="JavaScript">
                        function verifyChecks(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                var countTotalChecks = 0;
                                var countTotalChecksChecked = 0;
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'selectAllValues') {
                                            countTotalChecks++;
                                            if (checkboxes[i].checked){
                                                countTotalChecksChecked++;
                                            }
                                        }
                                    }
                                }
                                if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                    var checkHead = document.getElementById('selectAllValuesHead');
                                    checkHead.checked = true;
                                }
                            }
                            else {
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = false;
                            }
                        }
                    </script>
                    <br class="clear">
                    <table class="widefat" name="tablePendingFiles" id="idTablePendingFiles">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                            <th>File Name</th>
                            <th>Quantity of Products</th>
                            <th>Added By</th>
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
                            <th>Added By</th>
                            <th>Upload Date</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                        </tfoot>
                        <tbody>


                        <?php
                        global $wpdb;

                        $isConnected = $wpdb->check_connection();

                        if ($isConnected) {
                            $products_files = $wpdb->get_results("SELECT `inventory_id`, `file_md5`, `items_count`, `added_date`, users.`user_login` as added_by FROM `ot_custom_inventory_file` INNER JOIN `".$wpdb->prefix."users` as users ON `added_by` = users.`ID`  WHERE `status` = 'pending_approval' ORDER BY `added_date` DESC");
                            foreach ($products_files as $product_file) {
                                ?>
                                <tr>
                                    <?php
                                    echo "<td><input onchange='verifyChecks(this)' id='selectAllValues' style='margin-left:8px;' type=\"checkbox\" name=\"idProduct[]\" value=" . $product_file->inventory_id . "></td>";
                                    echo "<td>" . $product_file->file_md5 . "</td>";
                                    echo "<td>" . $product_file->items_count . "</td>";
                                    echo "<td>" . $product_file->added_by . "</td>";
                                    echo "<td>" . $product_file->added_date . "</td>";
                                    echo "<td>Pending Approval</td>";
                                    echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idProductFile\" value=\"$product_file->inventory_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewDetails\"></form></td>";
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
    }

    function wpdocs_distributors_submenu_page_callback(){
        if( isset($_GET['view-new-distributor']) and $_GET['view-new-distributor'] == true ){
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <?php
                if(isset($_POST['editDistributor']) and $_POST['editDistributor'] == true){
                    ?>
                    <h3>Update Distributor</h3>
                    <?php
                }else{
                    ?>
                    <h3>Add New Distributor</h3>
                    <?php
                }
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <input id="actionu" class="button action" value="Back Distributor List" type="submit" name="actionBackDistributorList">
                <br>
                <br>
                <?php
                if (isset($_GET['message-error'])) {
                    ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                    </div>
                    <?php
                }
                ?>
                    <input align="left" type="hidden" name="idDistributor" value="<?php _e($_POST['idDistributor']) ?>">
                    <table>
                        <thead>
                        <tr>
                            <th align="left"  scope="row"><label for="nameDistributor">Name: <span class="description">(required)</span></label></th>
                            <?php
                            if(isset($_POST['editDistributor']) and $_POST['editDistributor'] == true){
                                ?>
                                <td><input readonly name="nameDistributor" id="nameDistributor" value="<?php echo $_POST['nameDistributor'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                                <?php
                            }else{
                                ?>
                                <td><input name="nameDistributor" id="nameDistributor" value="<?php echo $_POST['nameDistributor'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <th align="left"  scope="row"><label for="locationDistributor">Location: </label></th>
                            <td><textarea rows="4" cols="50" name="locationDistributor"><?php echo $_POST['locationDistributor'];?></textarea></td>
                        </tr>
                        <tr>
                            <th align="left"  scope="row"><label for="locationDistributor">Tax Id: </label></th>
                            <td><input type="text" name="taxIdDistributor" value="<?php echo $_POST['taxIdDistributor'];?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"></th>
                            <?php
                            if(isset($_POST['editDistributor']) and $_POST['editDistributor'] == true){
                                ?>
                                <td><input class="button button-primary" id="doAction" class="button action" value="Update" type="submit" name="actionUpdateDistributor"></td>
                                <?php
                            }else{
                                ?>
                                <td><input class="button button-primary" id="doAction" class="button action" value="Create" type="submit" name="actionCreateDistributor"></td>
                                <?php
                            }
                            ?>
                        </tr>
                        </thead>
                    </table>
                </form>
            </div>
            <?php
        }
        else if( isset($_GET['view-user-distributor']) and $_GET['view-user-distributor'] == true ){
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>User List</h3>
                <form action="" method="post" enctype="multipart/form-data">
                <input id="actionu" class="button action" value="Back Distributor List" type="submit" name="actionBackDistributorList">
                <h4>Users assigned to distributor</h4>
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="selectActionAssignedUsers" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="delete" class="hide-if-no-js">Delete</option>
                            <option value="approve">Approve</option>
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkAssignedUsers">
                            <input class="button action" value="Add New User" type="submit" name="actionNewUser">
                        </select>
                    </div>
                    <script language="JavaScript">
                        function checkAssigned(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'chkAssignedUsers') {
                                            checkboxes[i].checked = true;
                                        }
                                    }
                                }
                            } else {
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'chkAssignedUsers') {
                                            checkboxes[i].checked = false;
                                        }
                                    }
                                }
                            }
                        }

                        function verifyChecks(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                var countTotalChecks = 0;
                                var countTotalChecksChecked = 0;
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'chkAssignedUsers') {
                                            countTotalChecks++;
                                            if (checkboxes[i].checked){
                                                countTotalChecksChecked++;
                                            }
                                        }
                                    }
                                }
                                if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                    var checkHead = document.getElementById('headChkAssignedUsers');
                                    checkHead.checked = true;
                                }
                            }
                            else {
                                var checkHead = document.getElementById('headChkAssignedUsers');
                                checkHead.checked = false;
                            }
                        }
                    </script>

                <input type="hidden" name="idDistributor" value="<?php _e($_GET['idDistributor']) ?>">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox" onchange="checkAssigned(this)" name="chk[]" id="headChkAssignedUsers" /> </th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Status</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    if($wpdb->check_connection()){
                        $distributorID = $_GET['idDistributor'];
                        $users =  $wpdb->get_results("SELECT
                                                            cdu.`distributor_user_userid`,                                                     
                                                            users.`user_login`,
                                                            users.`display_name`,
                                                            users.`user_email`,                                                           
                                                            (SELECT `user_login` FROM `".$wpdb->prefix."users` WHERE `ID` = cdu.`distributor_user_added_by`) as distributor_user_added_by,
                                                            cdu.`distributor_user_added_date`,
                                                            cdu.`status`
                                                       FROM `ot_custom_distributor_user` as cdu
                                                       INNER JOIN `".$wpdb->prefix."users` as users ON users.`ID` = cdu.`distributor_user_userid`
                                                       WHERE cdu.`distributor_user_distributor_id` = ".$distributorID.";");
                        foreach ($users as $user) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input onchange='verifyChecks(this)'  id=\"chkAssignedUsers\" style='margin-left:8px;' type=\"checkbox\" name=\"idAssignedUsers[]\" value=" . $user->distributor_user_userid . "></td>";
                                echo "<td>" . $user->distributor_user_userid . "</td>";
                                echo "<td>" . $user->user_login . "<form action=\"\" method=\"post\"><div class='row-actions'><span class='edit'><input type=\"hidden\" name=\"idUser\" value=\"$user->distributor_user_userid\"><input type='submit' class=\"button-link\" value=\"Edit\" style=\"color:#0073aa; font-size: 13px;\" name=\"actionEditUser\"></span></div></form></td>";
                                echo "<td>" . $user->display_name . "</td>";
                                echo "<td>" . $user->user_email . "</td>";
                                echo "<td>" . $user->distributor_user_added_by . "</td>";
                                echo "<td>" . $user->distributor_user_added_date . "</td>";
                                echo "<td>" .str_replace("-", " ", ucfirst($user->status)). "</td>";
                                ?>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
                </form>
                <br>
                <h4>Unassigned users</h4>
                <?php
                if (isset($_GET['message-error'])) {
                    ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                    </div>
                    <?php
                }
                ?>
                <form action="" method="post" enctype="multipart/form-data" name="Unassigned " id="Unassigned ">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="selectActionUsers" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="add" class="hide-if-no-js">Add</option>
                            <!--<option value="deactivate">Deactivate</option>-->
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkUsers">
                        </select>
                    </div>

                    <script language="JavaScript">
                        function checkAll(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                         if(currentValue.toString() == 'chkUnassignedUsers') {
                                            checkboxes[i].checked = true;
                                        }
                                    }
                                }
                            } else {
                                for (var i = 0; i < checkboxes.length; i++) {
                                    console.log(i)
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'chkUnassignedUsers') {
                                            checkboxes[i].checked = false;
                                        }
                                    }
                                }
                            }
                        }

                        function verifyChecksUnassignedUsers(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                var countTotalChecks = 0;
                                var countTotalChecksChecked = 0;
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'chkUnassignedUsers') {
                                            countTotalChecks++;
                                            if (checkboxes[i].checked){
                                                countTotalChecksChecked++;
                                            }
                                        }
                                    }
                                }
                                if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                    var checkHead = document.getElementById('headChkUnAssignedUsers');
                                    checkHead.checked = true;
                                }
                            }
                            else {
                                var checkHead = document.getElementById('headChkUnAssignedUsers');
                                checkHead.checked = false;
                            }
                        }

                    </script>

                    <input type="hidden" name="idDistributor" value="<?php _e($_GET['idDistributor']) ?>">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox" onchange="checkAll(this)" name="chk[]" id="headChkUnAssignedUsers" /> </th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added Date</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added Date</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    if($wpdb->check_connection()){
                        $users =  $wpdb->get_results("SELECT users.*
                                                      FROM `".$wpdb->prefix."users` as users  
                                                      WHERE users.`ID` not in (SELECT `distributor_user_userid` FROM `ot_custom_distributor_user`);");
                        foreach ($users as $user) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input onchange='verifyChecksUnassignedUsers(this)'  id=\"chkUnassignedUsers\" style='margin-left:8px;' type=\"checkbox\" name=\"idUsers[]\" value=" . $user->ID . "></td>";
                                echo "<td>" . $user->ID . "</td>";
                                echo "<td>" . $user->user_login . "</td>";
                                echo "<td>" . $user->display_name . "</td>";
                                echo "<td>" . $user->user_email . "</td>";
                                echo "<td>" . $user->user_registered . "</td>";
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
        else if( isset($_GET['view-add-new-user']) and $_GET['view-add-new-user'] == true ){
            ?><div class="wrap">
                <h4>Open Trade 2.0</h4>
                <?php
                if(isset($_POST['editUser']) and $_POST['editUser'] == true){
                    ?>
                    <h3>Add New User</h3>
                    <?php
                }else{
                    ?>
                    <h3>Update User</h3>
                    <?php
                }
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                <input id="actionu" class="button action" value="Back User List" type="submit" name="actionBackUserList">
                <input type="hidden" name="idDistributor" value="<?php _e($_POST['idDistributor']) ?>">
                <input type="hidden" name="idUser" value="<?php _e($_POST['idUser']) ?>">
                <?php
                if(!isset($_POST['editUser'])){
                    ?>
                    <p>Create a new user and add them to this distributor.</p>
                    <?php
                }
                ?>

                <?php
                if (isset($_GET['message-error'])) {
                    ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                    </div>
                    <?php
                }
                ?>
                <table>
                <thead>
                <tr>
                    <th align="left" scope="row" ><label for="user_login">Username <span class="description">(required)</span></label></th>
                    <?php
                    if(isset($_POST['editUser']) and $_POST['editUser'] == true){
                        ?>
                        <td><input readonly name="user_login" id="user_login" value="<?php echo $_POST['user_login'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                        <?php
                    }else{
                        ?>
                        <td><input name="user_login" id="user_login" value="<?php echo $_POST['user_login'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <th align="left" scope="row"><label for="email">Email <span class="description">(required)</span></label></th>
                    <?php
                    if(isset($_POST['editUser']) and $_POST['editUser'] == true){
                        ?>
                        <td><input readonly name="email" id="email" value="<?php echo $_POST['email'];?>" type="email"></td>
                        <?php
                    }else{
                        ?>
                        <td><input name="email" id="email" value="<?php echo $_POST['email'];?>" type="email"></td>
                        <?php
                    }
                    ?>
                </tr>
                <tr>
                    <th align="left" scope="row"><label for="first_name">First Name </label></th>
                    <td><input name="first_name" id="first_name" value="<?php echo $_POST['first_name'];?>" type="text"></td>
                </tr>
                <tr>
                    <th align="left" scope="row"><label for="last_name">Last Name </label></th>
                    <td><input name="last_name" id="last_name" value="<?php echo $_POST['last_name'];?>" type="text"></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <?php
                    if(isset($_POST['editUser']) and $_POST['editUser'] == true){
                        ?>
                        <td><input name="actionUpdateUser" id="createusersub" class="button button-primary" value="Update User" type="submit"></td>
                        <?php
                    }else{
                        ?>
                        <td><input name="actionCreateUser" id="createusersub" class="button button-primary" value="Add New User" type="submit"></td>
                        <?php
                    }
                    ?>
                </tr>
                </thead>
                </table>
                </form>
            </div>
            <?php
        }
        else if( isset($_GET['view-warehouse-list']) and $_GET['view-warehouse-list'] == true ){
            ?><div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Warehouse List</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="idDistributor" value="<?php _e($_POST['idDistributor']) ?>">
                <input id="actionu" class="button action" value="Back Distributor List" type="submit" name="actionBackDistributorList">
                <script>
                    jQuery(function ($) {
                        $('#selectAllValuesHead').on('click', function () {
                            $(':checkbox').prop("checked", $(this).is(':checked'));
                        });
                    })
                </script>
                <script language="JavaScript">
                    function verifyChecks(ele) {
                        var checkboxes = document.getElementsByTagName('input');
                        if (ele.checked) {
                            var countTotalChecks = 0;
                            var countTotalChecksChecked = 0;
                            for (var i = 0; i < checkboxes.length; i++) {
                                if (checkboxes[i].type == 'checkbox') {
                                    var currentValue =checkboxes[i].id;
                                    if(currentValue.toString() == 'selectAllValues') {
                                        countTotalChecks++;
                                        if (checkboxes[i].checked){
                                            countTotalChecksChecked++;
                                        }
                                    }
                                }
                            }
                            if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = true;
                            }
                        }
                        else {
                            var checkHead = document.getElementById('selectAllValuesHead');
                            checkHead.checked = false;
                        }
                    }
                </script>
            <br>
            <br>
            <?php
            if (isset($_GET['message-error'])) {
                ?>
                <div id="message" class="error">
                    <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                </div>
                <?php
            }
            ?>
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                    <select name="selectActionWarehouse" id="bulk-action-selector-top">
                        <option value="-1">Actions</option>
                        <option value="delete" class="hide-if-no-js">Delete</option>
                        <!--<option value="deactivate">Deactivate</option>-->
                        <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkWarehouse">
                        <input id="doAction" class="button action" value="New Warehouse" type="submit" name="actionNewWarehouse">
                    </select>
                </div>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Warehouse location ID</th>
                        <th>ZipCode</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Location</th>
                        <th>City</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Warehouse location ID</th>
                        <th>ZipCode</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Location</th>
                        <th>City</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    if($wpdb->check_connection()){
                        $idDistributor = $_POST['idDistributor'];
                        $warehouses =  $wpdb->get_results("SELECT cwl.`warehouse_id`, cw.`warehouse_name`, cw.`warehouse_file_id`, cwl.`zipcode`, cwl.`latitude`, cwl.`longitude`, cwl.`location`, cwl.`city`, user.`user_login`, cwl.`added_date`
                                                             FROM `ot_custom_warehouse_location` as cwl 
                                                             INNER JOIN `ot_custom_warehouse` AS cw ON cwl.`warehouse_id` = cw.`warehouse_id`
                                                             INNER JOIN `ot_custom_distributor_warehouse` AS cdw ON cdw.`warehouse_id` = cw.`warehouse_id`
                                                             INNER JOIN `".$wpdb->prefix."users` as user ON cw.`added_by` = user.`ID`
                                                             WHERE cdw.`distributor_id` = ".$idDistributor.";");
                        foreach ($warehouses as $warehouse) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input onchange='verifyChecks(this)' id='selectAllValues'  style='margin-left:8px;' type=\"checkbox\" name=\"idWarehouses[]\" value=" . $warehouse->warehouse_id . "></td>";
                                echo "<td>" . $warehouse->warehouse_id . "</td>";
                                echo "<td>" . $warehouse->warehouse_name . "<form action=\"\" method=\"post\"><div class='row-actions'><span class='edit'><input type=\"hidden\" name=\"idDistributor\" value=\"$idDistributor\"><input type=\"hidden\" name=\"idWarehouse\" value=\"$warehouse->warehouse_id\"><input type='submit' class=\"button-link\" value=\"Edit\" style=\"color:#0073aa; font-size: 13px;\" name=\"actionEditWareHouse\"></span></div></form></td>";
                                echo "<td>" . $warehouse->warehouse_file_id . "</td>";
                                echo "<td>" . $warehouse->zipcode . "</td>";
                                echo "<td>" . $warehouse->latitude . "</td>";
                                echo "<td>" . $warehouse->longitude . "</td>";
                                echo "<td>" . $warehouse->location . "</td>";
                                echo "<td>" . $warehouse->city . "</td>";
                                echo "<td>" . $warehouse->user_login . "</td>";
                                echo "<td>" . $warehouse->added_date . "</td>";
                                ?>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <?php
        }
        else if( isset($_GET['view-add-new-warehouse']) and $_GET['view-add-new-warehouse'] == true ){
            ?><div class="wrap">
            <h4>Open Trade 2.0</h4>
            <?php
            if(isset($_POST['editWarehouse']) and $_POST['editWarehouse'] == true){
                echo "<h3>Update Warehouse</h3>";
            }else{
                echo "<h3>Add New Warehouse</h3>";
            }
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input id="actionu" class="button action" value="Back Warehouse List" type="submit" name="actionBackWarehouseList">
                <input type="hidden" name="idDistributor" value="<?php _e($_POST['idDistributor']) ?>">
                <input type="hidden" name="idWarehouse" value="<?php _e($_POST['idWarehouse']) ?>">
                <?php
                if(!isset($_POST['editWarehouse'])){
                    ?>
                    <p>Create a new warehouse and add them to this distributor.</p>
                    <?php
                }
                ?>
                <?php
                if (isset($_GET['message-error'])) {
                    ?>
                    <div id="message" class="error">
                        <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                    </div>
                    <?php
                }
                ?>
                <table>
                    <thead>
                    <tr>
                        <th align="left" scope="row"><label for="name">Name <span class="description">(required)</span></label></th>
                        <?php
                        if(isset($_POST['editWarehouse']) and $_POST['editWarehouse'] == true){
                            ?>
                            <td align="left"><input name="name" id="name" value="<?php echo $_POST['name'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                            <?php
                        }else{
                            ?>
                            <td align="left"><input name="name" id="name" value="<?php echo $_POST['name'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                            <?php
                        }
                        ?>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="name">Warehouse location ID</label></th>
                        <?php
                        if(isset($_POST['editWarehouse']) and $_POST['editWarehouse'] == true){
                            ?>
                            <td align="left"><input name="file_id" id="file_id" value="<?php echo $_POST['file_id'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                            <?php
                        }else{
                            ?>
                            <td align="left"><input name="file_id" id="file_id" value="<?php echo $_POST['file_id'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                            <?php
                        }
                        ?>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="zipcode">Zip Code</label></th>
                        <td><input name="zipcode" id="zipcode" value="<?php echo $_POST['zipcode'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="latitude">Latitude </label></th>
                        <td><input name="latitude" id="latitude" value="<?php echo $_POST['latitude'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="longitude">Longitude </label></th>
                        <td><input name="longitude" id="longitude" value="<?php echo $_POST['longitude'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="location">Location </label></th>
                        <td><textarea rows="4" cols="50" name="location"><?php echo $_POST['location'];?></textarea></td>
                    </tr>
                    <tr>
                        <th align="left" scope="row"><label for="city">City </label></th>
                        <td><input name="city" id="city" value="<?php echo $_POST['city'];?>" type="text"></td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <?php
                        if(isset($_POST['editWarehouse']) and $_POST['editWarehouse'] == true){
                            ?>
                            <td><input name="actionUpdateWarehouse" id="createusersub" class="button button-primary" value="Update Warehouse" type="submit"></td>
                            <?php
                        }else{
                            ?>
                            <td><input name="actionCreateWarehouse" id="createusersub" class="button button-primary" value="Add New Warehouse" type="submit"></td>
                            <?php
                        }
                        ?>
                    </tr>
                    </thead>
                </table>
            </form>
            </div>
            <?php
        }
        else
        {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>Distributors List</h3>
                <br>
                <?php
                if (isset($_GET['message-error'])) {
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
                        <select name="selectActionDistributors" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="delete" class="hide-if-no-js">Delete</option>
                            <option value="approve">Approve</option>
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkDistributors">
                            <input id="doAction" class="button action" value="New Distributor" type="submit" name="actionNewDistributor">
                        </select>
                </div>
                <script>
                    jQuery(function ($) {
                        $('#selectAllValuesHead').on('click', function () {
                            $(':checkbox').prop("checked", $(this).is(':checked'));
                        });
                    })
                </script>
                    <script language="JavaScript">
                        function verifyChecks(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                var countTotalChecks = 0;
                                var countTotalChecksChecked = 0;
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'selectAllValues') {
                                            countTotalChecks++;
                                            if (checkboxes[i].checked){
                                                countTotalChecksChecked++;
                                            }
                                        }
                                    }
                                }
                                if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                    var checkHead = document.getElementById('selectAllValuesHead');
                                    checkHead.checked = true;
                                }
                            }
                            else {
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = false;
                            }
                        }
                    </script>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                        <th>ID</th>
                        <th>Distributor</th>
                        <th>Location</th>
                        <th>Tax ID</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Warehouses</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Distributor</th>
                        <th>Location</th>
                        <th>Tax ID</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Status</th>
                        <th>Users</th>
                        <th>Warehouses</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    if($wpdb->check_connection()){
                        $distributors =  $wpdb->get_results("SELECT dist.`distributor_id`, dist.`distributor_name`, dist.`location`,dist.`tax_id`, user.`user_nicename` as `added_by`, dist.`added_date`, dist.`status` FROM `ot_custom_distributor` as dist INNER JOIN `".$wpdb->prefix."users` as user ON dist.`added_by` = user.`ID` ;");
                        foreach ($distributors as $distributor) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input onchange='verifyChecks(this)' id='selectAllValues' style='margin-left:8px;' type=\"checkbox\" name=\"idDistributors[]\" value=" . $distributor->distributor_id . "></td>";
                                echo "<td>" . $distributor->distributor_id . "</td>";
                                echo "<td>" . $distributor->distributor_name . "<form action=\"\" method=\"post\"><div class='row-actions'><span class='edit'><input type=\"hidden\" name=\"idDistributor\" value=\"$distributor->distributor_id\"><input type='submit' class=\"button-link\" value=\"Edit\" style=\"color:#0073aa; font-size: 13px;\" name=\"actionEditDistributor\"></span></div></form></td>";
                                echo "<td>" . $distributor->location . "</td>";
                                echo "<td>" . $distributor->tax_id . "</td>";
                                echo "<td>" . $distributor->added_by . "</td>";
                                echo "<td>" . $distributor->added_date . "</td>";
                                echo "<td>" . str_replace("-", " ", ucfirst($distributor->status)) . "</td>";
                                echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idDistributor\" value=\"$distributor->distributor_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewUsers\"></form></td>";
                                echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idDistributor\" value=\"$distributor->distributor_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewWarehouse\"></form></td>";
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
    }

    function wpdocs_reques_information_submenu_page_callback(){

        if( isset($_GET['view-products-request']) and $_GET['view-products-request'] == true ) {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>Products Detail</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <input id="actionDetails" class="button action" value="Back Request List" type="submit" name="actionBackRequestInfoList">
                </form>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php

                    global $wpdb;
                    $idRequestInfo = $_GET['view-details-idRequestInfo'];
                    $products = $wpdb->get_results("SELECT * FROM `ot_custom_product_request_information`  WHERE `request_information_id` = ".$idRequestInfo.";");

                    foreach ($products as $product) {
                        $price = get_post_meta( $product->product_id, '_price', true );
                        $post = get_post($product->product_id);
                        $stock = get_post_meta( $product->product_id, '_stock' );
                        ?>
                        <tr>
                            <?php
                            echo "<td>"  . $product->product_id . "</td>";
                            echo "<td>"  . $post->post_title . "</td>";
                            echo "<td>"  . $post->post_content . "</td>";
                            echo "<td>$"  . $price . "</td>";
                            echo "<td>"  . $stock[0] . "</td>";
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php

        }
        else {
        ?>
        <div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Pending Request Information</h3>
            <br>
            <?php
            if (isset($_GET['message-success'])) {
                ?>
                <div id="message" class="updated">
                    <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                </div>
                <br>
                <?php
            }
            if (isset($_GET['message-error'])) {
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
                    <select name="selectActionRequestInformation" id="bulk-action-selector-top">
                        <option value="-1">Actions</option>
                        <option value="processed" class="hide-if-no-js">Processed</option>
                        <!--<option value="reject">Reject</option>-->
                        <input id="doaction" class="button action" value="Apply" type="submit" name="actionRequestInformation">
                    </select>

                </div>
                <script>
                    jQuery(function ($) {
                        $('#selectAllValuesHead').on('click', function () {
                            $(':checkbox').prop("checked", $(this).is(':checked'));
                        });
                    })
                </script>
                <script language="JavaScript">
                    function verifyChecks(ele) {
                        var checkboxes = document.getElementsByTagName('input');
                        if (ele.checked) {
                            var countTotalChecks = 0;
                            var countTotalChecksChecked = 0;
                            for (var i = 0; i < checkboxes.length; i++) {
                                if (checkboxes[i].type == 'checkbox') {
                                    var currentValue =checkboxes[i].id;
                                    if(currentValue.toString() == 'selectAllValues') {
                                        countTotalChecks++;
                                        if (checkboxes[i].checked){
                                            countTotalChecksChecked++;
                                        }
                                    }
                                }
                            }
                            if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = true;
                            }
                        }
                        else {
                            var checkHead = document.getElementById('selectAllValuesHead');
                            checkHead.checked = false;
                        }
                    }
                </script>
                <br class="clear">
                <table class="widefat" name="tableRequestsinformation" id="idTableRequestInformation">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                        <th>Id</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Products</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>Id</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Quantity</th>
                        <th>Date</th>
                        <th>Products</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    $isConnected = $wpdb->check_connection();

                    if ($isConnected) {
                        $requests_information = $wpdb->get_results("select ".
        "cri.request_information_id as requestId,
        (select user_login from ".$wpdb->prefix."users where ID = (select user_id from ot_custom_request_information where request_information_id = cri.request_information_id)) as user_name,
        (select user_email from ".$wpdb->prefix."users where ID = (select user_id from ot_custom_request_information where request_information_id = cri.request_information_id)) as email,
        cri.added_date as date,
        (select count(*) from ot_custom_product_request_information where request_information_id = cri.request_information_id) as quantity
    from ot_custom_request_information cri where status = 'created'");
                        foreach ($requests_information as $request_information) {
                            //$price= get_post_meta( $request_information->product_id, '_price', true );
                            ?>
                            <tr>
                                <?php
                                echo "<td><input onchange='verifyChecks(this)' id='selectAllValues' style='margin-left:8px;' type=\"checkbox\" name=\"idRequestInformation[]\" value=" . $request_information->requestId . "></td>";
                                echo "<td>" . $request_information->requestId . "</td>";
                                //echo "<td>" . $request_information->product_description . "</td>";
                                echo "<td>" . $request_information->user_name . "</td>";
                                echo "<td>" . $request_information->email . "</td>";
                                //echo "<td>" . $price . "</td>";
                                echo "<td>" . $request_information->quantity . "</td>";
                                echo "<td>" . $request_information->date . "</td>";
                                //echo "<td>Pending Approval</td>";
                                echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idRequestInfo\" value=\"$request_information->requestId\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewRequestInfo\"></form></td>";
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
    }

    function wpdocs_post_offer_submenu_page_callback(){
        if( isset($_GET['view-products-offer']) and $_GET['view-products-offer'] == true ) {
        ?>
        <div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Products Detail</h3>
            <form action="" method="post" enctype="multipart/form-data">
                <input id="actionDetails" class="button action" value="Back Offer List" type="submit" name="actionBackOfferInfoList">
            </form>
            <br>
            <table class="widefat">
                <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Offer</th>
                    <th>Stock</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Offer</th>
                    <th>Stock</th>
                </tr>
                </tfoot>
                <tbody>
                <?php

                global $wpdb;
                $idOfferInfo = $_GET['idOfferInfo'];
                $products = $wpdb->get_results("SELECT * FROM `ot_custom_product_offer_information`  WHERE `offer_information_id` = ".$idOfferInfo.";");

                foreach ($products as $product) {
                    $price = get_post_meta( $product->product_id, '_price', true );
                    $post = get_post($product->product_id);
                    $stock = get_post_meta( $product->product_id, '_stock' );
                    ?>
                    <tr>
                        <?php
                        echo "<td>"  . $product->product_id . "</td>";
                        echo "<td>"  . $post->post_title . "</td>";
                        echo "<td>"  . $post->post_content . "</td>";
                        echo "<td>$"  . number_format($price, 2) . "</td>";
                        echo "<td>$"  . number_format($product->offer, 2)  . "</td>";
                        echo "<td>"  . $stock[0] . "</td>";
                        ?>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
        }
        else {
        ?>
        <div class="wrap">
            <h4>Open Trade 2.0</h4>
            <h3>Post Offer</h3>
            <br>
            <?php
            if (isset($_GET['message-success'])) {
                ?>
                <div id="message" class="updated">
                    <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                </div>
                <br>
                <?php
            }
            if (isset($_GET['message-error'])) {
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
                    <select name="selectActionOfferInformation" id="bulk-action-selector-top">
                        <option value="-1">Actions</option>
                        <option value="approve" class="hide-if-no-js">Approve</option>
                        <option value="reject">Reject</option>
                        <input id="doaction" class="button action" value="Apply" type="submit" name="actionOfferInformation">
                    </select>
                </div>
                <script>
                    jQuery(function ($) {
                        $('#selectAllValuesHead').on('click', function () {
                            $(':checkbox').prop("checked", $(this).is(':checked'));
                        });
                    })
                </script>
                <script language="JavaScript">
                    function verifyChecks(ele) {
                        var checkboxes = document.getElementsByTagName('input');
                        if (ele.checked) {
                            var countTotalChecks = 0;
                            var countTotalChecksChecked = 0;
                            for (var i = 0; i < checkboxes.length; i++) {
                                if (checkboxes[i].type == 'checkbox') {
                                    var currentValue =checkboxes[i].id;
                                    if(currentValue.toString() == 'selectAllValues') {
                                        countTotalChecks++;
                                        if (checkboxes[i].checked){
                                            countTotalChecksChecked++;
                                        }
                                    }
                                }
                            }
                            if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = true;
                            }
                        }
                        else {
                            var checkHead = document.getElementById('selectAllValuesHead');
                            checkHead.checked = false;
                        }
                    }
                </script>
                <br class="clear">
                <table class="widefat" name="tablePendingFiles" id="idTablePendingFiles">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                        <th>Id</th>
                        <th>User Id</th>
                        <th>User Name</th>
                        <th>User Email</th>
                        <th>Added Date</th>
                        <th>Total Amount</th>
                        <th>Total Offer</th>
                        <th>Products</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th>Id</th>
                        <th>User Id</th>
                        <th>User Name</th>
                        <th>User Email</th>
                        <th>Added Date</th>
                        <th>Total Amount</th>
                        <th>Total Offer</th>
                        <th>Products</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    $isConnected = $wpdb->check_connection();

                    if ($isConnected) {
                        $products_items = $wpdb->get_results("SELECT *  FROM `ot_custom_offer_information` WHERE status = 'created' ORDER BY `added_date` DESC");
                        foreach ($products_items as $product_item) {
                            $user = get_user_by('ID',$product_item->user_id );
                            ?>
                            <tr>
                                <?php
                                echo "<td><input  onchange='verifyChecks(this)' id='selectAllValues' style='margin-left:8px;' type=\"checkbox\" name=\"idProductOfferList[]\" value=" . $product_item->offer_information_id . "></td>";
                                echo "<td>"  . $product_item->offer_information_id . "</td>";
                                echo "<td>"  . $user->ID . "</td>";
                                echo "<td>"  . $user->user_login . "</td>";
                                echo "<td>"  . $user->user_email . "</td>";
                                echo "<td>"  . $product_item->added_date . "</td>";
                                echo "<td> $" . number_format($product_item->total_amount, 2) . "</td>";
                                echo "<td>$" . number_format($product_item->total_offer, 2) . "</td>";
                                echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idOfferInfo\" value=\"$product_item->offer_information_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewOfferInfo\"></form></td>";
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
    }

    function wpdocs_purchase_order_submenu_page_callback(){
        if( isset($_GET['view-products-purchase-order']) and $_GET['view-products-purchase-order'] == true ) {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>Products Detail</h3>
                <form action="" method="post" enctype="multipart/form-data">
                    <input id="actionDetails" class="button action" value="Back Purchase Order List" type="submit" name="actionBackPurchaseList">
                </form>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Id</th>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php

                    global $wpdb;
                    $idPurchaseOrder = $_GET['idPurchaseOrder'];
                    $products = $wpdb->get_results("SELECT * FROM `ot_custom_product_purchase_order`  WHERE `purchase_order_id` = ".$idPurchaseOrder.";");

                    foreach ($products as $product) {
                        $price = get_post_meta( $product->product_id, '_price', true );
                        $post = get_post($product->product_id);
                        $stock = get_post_meta( $product->product_id, '_stock' );
                        ?>
                        <tr>
                            <?php
                            echo "<td>"  . $product->product_purchase_order_id . "</td>";
                            echo "<td>"  . $product->product_id . "</td>";
                            echo "<td>"  . $post->post_title . "</td>";
                            echo "<td>"  . $post->post_content . "</td>";
                            echo "<td>$"  . $price . "</td>";
                            echo "<td>"  . $stock[0] . "</td>";
                            ?>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php

        }
        else {
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>Pending Approval Purchase Orders</h3>
                <br>
                <?php
                if (isset($_GET['message-success'])) {
                    ?>
                    <div id="message" class="updated">
                        <p><strong><?php _e($_GET['message-success']) ?></strong></p>
                    </div>
                    <br>
                    <?php
                }
                if (isset($_GET['message-error'])) {
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
                        <select name="selectActionPurchaseOrder" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="approve" class="hide-if-no-js">Approve</option>
                            <option value="reject">Reject</option>
                            <input id="doaction" class="button action" value="Apply" type="submit" name="actionPurchaseOrders">
                        </select>
                    </div>
                    <script>
                        jQuery(function ($) {
                            $('#selectAllValuesHead').on('click', function () {
                                $(':checkbox').prop("checked", $(this).is(':checked'));
                            });
                        })
                    </script>
                    <script language="JavaScript">
                        function verifyChecks(ele) {
                            var checkboxes = document.getElementsByTagName('input');
                            if (ele.checked) {
                                var countTotalChecks = 0;
                                var countTotalChecksChecked = 0;
                                for (var i = 0; i < checkboxes.length; i++) {
                                    if (checkboxes[i].type == 'checkbox') {
                                        var currentValue =checkboxes[i].id;
                                        if(currentValue.toString() == 'selectAllValues') {
                                            countTotalChecks++;
                                            if (checkboxes[i].checked){
                                                countTotalChecksChecked++;
                                            }
                                        }
                                    }
                                }
                                if ((countTotalChecks>0) && (countTotalChecksChecked>0) && (countTotalChecks== countTotalChecksChecked)){
                                    var checkHead = document.getElementById('selectAllValuesHead');
                                    checkHead.checked = true;
                                }
                            }
                            else {
                                var checkHead = document.getElementById('selectAllValuesHead');
                                checkHead.checked = false;
                            }
                        }
                    </script>
                    <br class="clear">
                    <table class="widefat" name="tablePendingFiles" id="idTablePendingFiles">
                        <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllValuesHead" name ="selectAllValuesHead"></th>
                            <th>Id</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Quantity of Products</th>
                            <th>Total Amount</th>
                            <th>File Name</th>
                            <th>View Products</th>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th></th>
                            <th>Id</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Quantity of Products</th>
                            <th>Total Amount</th>
                            <th>File Name</th>
                            <th>View Products</th>
                        </tr>
                        </tfoot>
                        <tbody>
                        <?php
                        global $wpdb;

                        $isConnected = $wpdb->check_connection();

                        if ($isConnected) {
                            $purchaseOrders = $wpdb->get_results("SELECT * FROM `ot_custom_purchase_order`  WHERE `status` = 'created' ORDER BY `added_date` DESC");
                            foreach ($purchaseOrders as $purchaseOrder) {
                                $user = get_user_by('ID', $purchaseOrder->user_id);
                                $products = $wpdb->get_results("SELECT * FROM `ot_custom_product_purchase_order`  WHERE `purchase_order_id` = " . $purchaseOrder->purchase_order_id . ";");
                                $url = $purchaseOrder->file_patch;
                                $name = $purchaseOrder->file_name;
                                $arreglo = explode("/",$purchaseOrder->file_patch);
                                $ruta = $arreglo[1]."/".$arreglo[2]."/".$arreglo[3];
                                ?>
                                <tr>
                                    <?php
                                    echo "<td><input  onchange='verifyChecks(this)' id='selectAllValues'  style='margin-left:8px;' type=\"checkbox\" name=\"idPurchaseOrders[]\" value=" . $purchaseOrder->purchase_order_id . "></td>";
                                    echo "<td>" . $purchaseOrder->purchase_order_id . "</td>";
                                    echo "<td>" . $user->nickname . "</td>";
                                    echo "<td>" . $user->user_email . "</td>";
                                    echo "<td>" . sizeof($products) . "</td>";
                                    echo "<td>$" . $purchaseOrder->total_amount . "</td>";
                                    echo "<td><a href='../wp-content/plugins/woocommerce/templates/checkout/". $ruta."' download>" . $name . "</a></td>";
                                    echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idPurchaseOrder\" value=\"$purchaseOrder->purchase_order_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionPurchaseOrderViewProducts\"></form></td>";
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
    }

    function wpdocs_download_inventory_submenu_page_callback(){
    ?>
    <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Download All Inventory</h3>
            <?php
            if (isset($_GET['message-error'])) {
                ?>
                <div id="message" class="error">
                    <p><strong><?php _e($_GET['message-error']) ?></strong></p>
                </div>
                <?php
            }
        ?>
        <form action="" method="post" enctype="multipart/form-data">
            <p>Press download button to generated inventory file.</p>
            <input id="actionDownloadInventory" class="button action" value="Download" type="submit" name="actionDownloadInventory">
        </form>
    </div>
    <?php
    }

    function wpdocs_upload_inventory_submenu_page_callback(){
    ?>
    <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Upload All Inventory</h3>
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
        ?>
        <br>
        <form action="" method="post" enctype="multipart/form-data">
            <table>
                <thead>
                <!--<tr>
                    <th scope="row"><label for="selectDistributor">Please select distributor: <span class="description">(required)</span></label></th>
                    <td><select name="selectDistributor" id="selectDistributor">
                            <option value="-1">Distributors</option>
                            <?php
        /*
                            global $wpdb;

                            if($wpdb->check_connection()){
                                $distributors =  $wpdb->get_results("SELECT `distributor_id`, `distributor_name` FROM `ot_custom_distributor`");
                                foreach ($distributors as $distributor) {
                                    echo "<option value=\"$distributor->distributor_id\">$distributor->distributor_name</option>";
                                }
                            }
        */
                            ?>
                        </select>
                    </td>
                </tr>-->
                <tr>
                    <th scope="row"><label for="fileToUpload">Please select file to upload: <span class="description">(required)</span></label></th>
                    <td><input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls" /></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td><input type="submit" value="Upload File" name="actionUpdateUploadFile"></td>
                </tr>
                </thead>
            </table>
        </form>
    </div>
    <?php
}

    if(isset($_POST["actionUploadFile"])) {

        $distributorID = $_POST['selectDistributor'];

        if ($distributorID == -1) {
            errorMessage('Please select one distributor!');
        } else {

            $errors = validateFile($_FILES['fileToUpload']);

            if (count($errors) === 0) {

                $formatDate = getFormatDate();
                $current_user = getCurrentUser();
                $target_dir = plugin_dir_path(__FILE__) . "uploads/";
                $fullPatch = getFullPatch($target_dir, $_FILES["fileToUpload"], $formatDate, $current_user);
                $filename = ($_FILES["fileToUpload"]['name']);
                $userID = $current_user->ID;
                if(uploadFile($_FILES["fileToUpload"], $fullPatch)){

                    $products = readAndProcessFile($fullPatch,$filename , $userID, $formatDate, $distributorID);

                    $_GET['products-list'] = $products;

                }else{
                    $_GET['message-error'] = 'File was not uploaded';
                }
            } else {
                $_GET['message-error'] = $errors[0];
            }
        }
    }
    
    if(isset($_POST["actionViewDetails"])){
        if(isset($_POST["idProductFile"])){
            $_GET['view-details'] = true;
            $_GET['view-details-idProductFile'] = $_POST['idProductFile'];
        }
    }

    if(isset($_POST["actionProducts"])){

        if(isset($_POST["selectAction"])) {
            $selectOption = $_POST['selectAction'];

            if ($selectOption == -1) {
                errorMessage('Please select one action!');
            } else if ($selectOption == 'approve') {
                if (isset($_POST['idProduct'])) {
                    $idProductsFile = $_POST["idProduct"];
                    if(approveProductsFiles($idProductsFile)){
                        $_GET['message-success'] = 'File Approve!';
                    }
                } else {
                    errorMessage('Please select a file to approve!');
                }

            } else if ($selectOption == 'reject') {
                if (isset($_POST['idProduct'])) {
                    $idProductsFile = $_POST["idProduct"];
                    rejectProductsFiles($idProductsFile);
                } else {
                    errorMessage('Please select a file to approve!');
                }
            }
        }
    }

    if(isset($_POST["actionBackFileList"])){
        $_GET['view-details'] = false;
    }

    if(isset($_POST["actionNewDistributor"])){
        $_GET['view-new-distributor'] = true;
    }

    if(isset($_POST["actionCreateDistributor"])){
        if(isset($_POST["nameDistributor"]) and $_POST["nameDistributor"] !== ""){
            createDistributor($_POST['nameDistributor'],$_POST['locationDistributor'],$_POST['taxIdDistributor']) ;
            $_GET['view-new-distributor'] = false;
        }else{
            $_GET['message-error'] = "Please set valid name!";
            $_GET['view-new-distributor'] = true;
        }
    }

    if(isset($_POST["actionBulkDistributors"])){
        if(isset($_POST['selectActionDistributors']) and $_POST['selectActionDistributors'] !== "-1"){
            
            if($_POST['selectActionDistributors'] =='delete') {                
                if (isset($_POST['idDistributors'])) {
                    $idDistributors = $_POST["idDistributors"];
                    foreach ($idDistributors as $idDistributor) {
                        deleteDistributor($idDistributor);
                    }
                } else {
                    $_GET['message-error'] = "Please select a distributor!";
                }
            }else if($_POST['selectActionDistributors'] =='approve'){
                if (isset($_POST['idDistributors'])) {
                    $idDistributors = $_POST["idDistributors"];
                    foreach ($idDistributors as $idDistributor) {
                        approveDistributor($idDistributor);

                        global $wpdb;
                        if($wpdb->check_connection()){
                            $distributor = $wpdb->get_results(" SELECT * FROM `ot_custom_distributor` WHERE `distributor_id` = ".$idDistributor.";")[0];
                            $to = array($distributor->email_administrator);
                            $subject='Your Company Is approved';
                            $headers = 'MIME-Version: 1.0' . "\r\n";
                            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                            $headers .= 'Reply-to: '.'Michael'.' '.'Lin'.' <'.'michael.lin@opentradeinc.com'.'>' . "\r\n";
                            $userData = $current_user->data;
                            $formatDate = date("Y-m-d h:i:s");
                            $message ='<html>
                                        <head>
                                        <font FACE="impact" SIZE=6 COLOR="red">O</font><font FACE="impact" SIZE=6 COLOR="black">PENTRADE</font>
                                        <br/>
                                        <h1>Your company has been approved to add inventory at the site of openTrade.</h1>
                                        </head>
                                        <body>
                                        <table>                    
                                        <tr>
                                        <th>Company Name:</th>
                                        <td>'.$distributor->distributor_name.'</td>
                                        </tr>
                                        <tr>
                                        <th>Location:</th>
                                        <td>'.$distributor->location.'</td>
                                        </tr>
                                        <tr>
                                        <th>Tax Id:</th>
                                        <td>'.$distributor->tax_id.'</td>
                                        </tr>                                                                                  
                                        </table>                    
                                        <br/>
                                        <table>
                                        <tr>
                                        <th>Date:</th>
                                        <td><label>'.$formatDate.'</label></td>
                                        </tr>
                                        </table>
                                        </body>
                                        </html>';


                            //add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));
                            add_filter('wp_mail_from','mqw_email_from');

                            function mqw_email_from($content_type) {
                                return 'info@opentradeinc.com';
                            }

                            add_filter( 'wp_mail_from_name', function( $name ) {
                                return 'Opentradeinc';
                            });

                            wp_mail( $to, $subject, $message, $headers);
                        }
                    }
                } else {
                    $_GET['message-error'] = "Please select a distributor!";
                }                
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
        }
    }

    if(isset($_POST["actionViewUsers"])){
        if(isset($_POST['idDistributor'])){
            $_GET['idDistributor'] = $_POST['idDistributor'];
            $_GET['view-user-distributor'] = true;
        }
    }

    if(isset($_POST["actionBulkUsers"])){
        if(isset($_POST['selectActionUsers']) and $_POST['selectActionUsers'] !== "-1"){
            if (isset($_POST['idUsers'])) {
                $idUsers = $_POST["idUsers"];
                $idDistributor = $_POST['idDistributor'];
                foreach ($idUsers as $idUser){
                    addUserDistributor($idUser,$idDistributor);
                }
                $_GET['view-user-distributor'] = true;
                $_GET['idDistributor'] = $idDistributor;
            }
            else{
                $_GET['message-error']="Please select a user!";
                $_GET['view-user-distributor'] = true;
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
            $_GET['view-user-distributor'] = true;
        }
    }

    if(isset($_POST["actionBulkAssignedUsers"])){
        if(isset($_POST['selectActionAssignedUsers']) and $_POST['selectActionAssignedUsers'] !== "-1"){
            if($_POST['selectActionAssignedUsers'] =='delete'){
                if (isset($_POST['idAssignedUsers'])){
                    $idUsers = $_POST["idAssignedUsers"];
                    $idDistributor = $_POST['idDistributor'];
                    foreach ($idUsers as $idUser){
                        deleteUserDistributor($idUser,$idDistributor);
                    }
                    $_GET['view-user-distributor'] = true;
                    $_GET['idDistributor'] = $idDistributor;
                }
                else{
                    $_GET['message-error']="Please select a user!";
                    $_GET['view-user-distributor'] = true;
                }
            }else if($_POST['selectActionAssignedUsers'] =='approve'){
                if (isset($_POST['idAssignedUsers'])){
                    $idUsers = $_POST["idAssignedUsers"];
                    $idDistributor = $_POST['idDistributor'];
                    
                    foreach ($idUsers as $idUser){
                        approvedUserDistributor($idUser);
                        $code = sha1( $idUser . time() );
                        $url = get_page_by_title( 'User Activated' )->guid;
                        $activation_link = add_query_arg( array( 'key' => $code, 'user' => $idUser ),$url);
                        add_user_meta( $idUser, 'has_to_be_activated', $code, true );
                        $user = get_user_by('ID',$idUser);
                        $message ='
                                        <html>
                                            <head>
                                            <font FACE="impact" SIZE=6 COLOR="red">O</font><font FACE="impact" SIZE=6 COLOR="black">PENTRADE</font>
                                            <br/>
                                                <h1>User Activation</h1>
                                            </head>
                                            <body>
                                                
                                                <table>
                                                    <tr>
                                                        <td>Congrats your user of open Trade is create .Here is your activation link:</td>
                                                        <td><label>'.$activation_link.'</label></td>
                                                    </tr>
                                                </table>
                                            </body>
                                        </html>';


                        wp_mail( $user->user_email, 'OpenTrade User Activation', $message);//
                    }
                    $_GET['view-user-distributor'] = true;
                    $_GET['idDistributor'] = $idDistributor;
                }
                else{
                    $_GET['message-error']="Please select a user!";
                    $_GET['view-user-distributor'] = true;
                }
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
            $_GET['view-user-distributor'] = true;
        }
    }

    if(isset($_POST["actionNewUser"])){
        $_GET['view-add-new-user'] = true;
    }

    if(isset($_POST["actionCreateUser"])){

        if(isset($_POST["user_login"]) and $_POST["user_login"] !== ""){

            $userLogin = get_user_by('login', $_POST["user_login"]);

            if(!$userLogin){
                if(isset($_POST["email"]) and $_POST["email"] !== ""){
                    $email =email_exists($_POST["email"]);
                    if(!$email){
                        createUser($_POST["user_login"],$_POST["email"],$_POST["first_name"],$_POST["last_name"],$_POST['idDistributor']);
                        $_GET['idDistributor'] = $_POST['idDistributor'];
                        $_GET['view-user-distributor'] = true;
                    }else{
                        $_GET['message-error']="Email already exists!";
                        $_GET['view-add-new-user'] = true;
                    }
                }else{
                    $_GET['message-error']="Email is required!";
                    $_GET['view-add-new-user'] = true;
                }

            }else{
                $_GET['message-error']="User Name already exists!";
                $_GET['view-add-new-user'] = true;
            }

        }else{
            $_GET['message-error']="User Name is required!";
            $_GET['view-add-new-user'] = true;
        }

    }

    if(isset($_POST["actionBackUserList"])){
        if(isset($_POST['idDistributor'])){
            $_GET['idDistributor'] = $_POST['idDistributor'];
            $_GET['view-user-distributor'] = true;
        }
    }

    if(isset($_POST["actionBackDistributorList"])){

    }

    if(isset($_POST["actionViewWarehouse"])){
        if(isset($_POST['idDistributor'])){
            $_GET['idDistributor'] = $_POST['idDistributor'];
            $_GET['view-warehouse-list'] = true;
        }
    }

    if(isset($_POST["actionNewWarehouse"])){
        $_GET['view-add-new-warehouse'] = true;
        $_GET['idDistributor'] = $_POST['idDistributor'];
    }

    if(isset($_POST["actionCreateWarehouse"])){
        if(isset($_POST["name"]) and $_POST["name"] !== ""){
            createWarehouse($_POST["name"],$_POST["zipcode"],$_POST["latitude"],$_POST["longitude"],$_POST["location"],$_POST["city"],$_POST['idDistributor'],$_POST['file_id']);
            $_GET['idDistributor'] = $_POST['idDistributor'];
            $_GET['view-warehouse-list'] = true;

        }else{
            $_GET['message-error']="Name is required!";
            $_GET['view-add-new-warehouse'] = true;
        }
    }

    if(isset($_POST["actionBulkWarehouse"])){
        if(isset($_POST['selectActionWarehouse']) and $_POST['selectActionWarehouse'] !== "-1"){
            if (isset($_POST['idWarehouses'])) {
                $idWarehouses = $_POST["idWarehouses"];
                $idDistributor = $_POST['idDistributor'];
                foreach ($idWarehouses as $idWarehouse){
                    deleteWarehouse($idWarehouse, $idDistributor);
                }
                $_GET['view-warehouse-list'] = true;
                $_GET['idDistributor'] = $idDistributor;
            }
            else{
                $_GET['message-error']="Please select a warehouse!";
                $_GET['view-warehouse-list'] = true;
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
            $_GET['view-warehouse-list'] = true;
        }
    }

    if(isset($_POST["actionEditDistributor"])){
        $_GET['view-new-distributor'] = true;
        $idDistributor = $_POST['idDistributor'];
        $distributor = getDistributorByID($idDistributor);
        $_POST['idDistributor'] = $distributor->distributor_id;
        $_POST['nameDistributor'] = $distributor->distributor_name;
        $_POST['locationDistributor'] = $distributor->location;
        $_POST['taxIdDistributor'] = $distributor->tax_id;
        $_POST['editDistributor'] = true;
    }

    if(isset($_POST["actionUpdateDistributor"])){
        if(isset($_POST["nameDistributor"]) and $_POST["nameDistributor"] !== ""){
            updateDistributor($_POST['idDistributor'],$_POST['locationDistributor'],$_POST['taxIdDistributor']) ;
            $_GET['view-new-distributor'] = false;
        }else{
            $_GET['message-error'] = "Please set valid name!";
            $_GET['view-new-distributor'] = true;
        }
    }

    if(isset($_POST["actionEditWareHouse"])){
        $_GET['view-add-new-warehouse'] = true;
        $_GET['idDistributor'] = $_POST['idDistributor'];
        $idWarehouse = $_POST['idWarehouse'];
        $warehouse = getWarehouseByID($idWarehouse);
        $_POST['idWarehouse'] = $warehouse->warehouse_id;
        $_POST['name']= $warehouse->warehouse_name;
        $_POST['file_id']= $warehouse->warehouse_file_id;
        $_POST['zipcode'] = $warehouse->zipcode;
        $_POST['latitude'] = $warehouse->latitude;
        $_POST['longitude'] = $warehouse->longitude;
        $_POST['location'] = $warehouse->location;
        $_POST['city'] = $warehouse->city;
        $_POST['editWarehouse'] = true;
    }

    if(isset($_POST["actionUpdateWarehouse"])){
        if(isset($_POST["name"]) and $_POST["name"] !== ""){
            updateWarehouse($_POST["idWarehouse"], $_POST["zipcode"], $_POST["latitude"], $_POST["longitude"], $_POST["location"], $_POST["city"], $_POST["file_id"], $_POST["name"]);
            $_GET['view-warehouse-list'] = true;

        }else{
            $_GET['message-error']="Name is required!";
            $_GET['view-add-new-warehouse'] = true;
        }
    }

    if(isset($_POST["actionBackWarehouseList"])){
        if(isset($_POST['idDistributor'])){
            $_GET['idDistributor'] = $_POST['idDistributor'];
            $_GET['view-warehouse-list'] = true;
        }
    }

    if(isset($_POST["actionEditUser"])){
        $_GET['view-add-new-user'] = true;
        $idUser = $_POST['idUser'];
        $_POST['idUser'] = $idUser;
        $user = get_user_by('ID',$idUser);
        $_POST['user_login'] = $user->data->user_login;
        $_POST['email'] = $user->data->user_email;
        $_POST['first_name']= get_user_meta($idUser, 'first_name', true);
        $_POST['last_name'] = get_user_meta($idUser, 'last_name', true);
        $_POST['editUser'] = true;
    }

    if(isset($_POST["actionUpdateUser"])){

        update_user_meta($_POST['idUser'], 'first_name', $_POST['first_name']);
        update_user_meta($_POST['idUser'], 'last_name', $_POST['last_name']);
        $fullName = $_POST['first_name']." ".$_POST['last_name'];
        wp_update_user(
            array(
                'ID'          =>    $_POST['idUser'],
                'nickname'    =>    $fullName,
                'display_name' => $fullName
            )
        );

        $_GET['idDistributor'] = $_POST['idDistributor'];
        $_GET['view-user-distributor'] = true;
    }

    if(isset($_POST["actionOfferInformation"])){
        if(isset($_POST['selectActionOfferInformation']) and $_POST['selectActionOfferInformation'] !== "-1"){
            if (isset($_POST['idProductOfferList'])) {
                $idProductOfferList = $_POST["idProductOfferList"];
               if( $_POST['selectActionOfferInformation'] == "approve") {
                   $status = "approve";
                   foreach ($idProductOfferList as $idProductOffer) {
                       updateProductOfferList($idProductOffer, $status);
                   }
               }
                else if( $_POST['selectActionOfferInformation'] == "reject") {
                    $status = "reject";
                    foreach ($idProductOfferList as $idProductOffer) {
                        updateProductOfferList($idProductOffer, $status);
                    }
                }
            }
            else{
                $_GET['message-error']="Please select a post offer!";
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
        }
    }

    if(isset($_POST["actionUpdateItem"])){
        $_GET['view-item-details'] = true;
        $_GET['idProductFile'] = $_POST['idProductFile'];
        $_GET['idProduct'] = $_POST['idProduct'];

        global $wpdb;
        $idProduct = $_GET['idProduct'];
        if($wpdb->check_connection()) {
            $product = $wpdb->get_results("SELECT * FROM `ot_custom_inventory_file_items` WHERE `inventory_file_item_id` = ".$_POST['idProduct'].";")[0];

            $_POST['distributor_id'] = $product->distributor_file_id;
            $_POST['distributor_name'] = $product->distributor_name;
            $_POST['distributor_sku_id'] = $product->distributor_sku_id;
            $_POST['distributor_sku_description'] = $product->distributor_sku_description;
            $_POST['lot'] = $product->lot_number;
            $_POST['packaging_type'] = $product->packaging_type;
            $_POST['packaging_unit'] = $product->packaging_unit;
            $_POST['packaging_measure'] = $product->packaging_measure;
            $_POST['packaging_weight_lb'] = $product->packaging_weight_lb;
            $_POST['packaging_weight_kg'] = $product->packaging_weight_kg;
            $_POST['quantity'] = $product->quantity;
            $_POST['price_unit'] = $product->price_unit;
            $_POST['total_weight_lb'] = $product->total_weight_lb;
            $_POST['total_weight_kg'] = $product->total_weight_kg;
            $_POST['price_lb'] = $product->price_lb;
            $_POST['price_kg'] = $product->price_kg;
            $_POST['warehouse_location_id'] = $product->warehouse_location_id;
            $_POST['warehouse_location_address'] = $product->warehouse_location_address;
        }
    }

    if(isset($_POST["actionPurchaseOrderViewProducts"])){
        $_GET['idPurchaseOrder'] = $_POST['idPurchaseOrder'];
        $_GET['view-products-purchase-order'] = true;
    }

    if(isset($_POST["actionBackPurchaseList"])){
        $_GET['view-products-purchase-order'] = false;
    }

    if(isset($_POST["actionPurchaseOrders"])){
        if(isset($_POST['selectActionPurchaseOrder']) and $_POST['selectActionPurchaseOrder'] !== "-1"){
            if (isset($_POST['idPurchaseOrders'])) {
                $idPurchaseOrders = $_POST["idPurchaseOrders"];
                $action = $_POST['selectActionPurchaseOrder'];
                foreach ($idPurchaseOrders as $idPurchaseOrder){
                    if($action == 'approve'){
                        global $wpdb;

                        if($wpdb->check_connection()){
                            $wpdb->query("UPDATE `ot_custom_purchase_order` SET `status` = 'approve' WHERE `purchase_order_id` = ".$idPurchaseOrder.";");

                            $products = $wpdb->get_results("SELECT * FROM `ot_custom_product_purchase_order`  WHERE `purchase_order_id` = ".$idPurchaseOrder.";");

                            foreach ($products as $product){
                                $stock = get_post_meta( $product->product_id, '_stock' );
                                $newTotal = $stock[0] - $product->quantity;
                                update_post_meta( $product->product_id, '_stock', $newTotal );
                            }
                            $_GET['message-success'] = "Order Approved!";
                        }
                    }
                    if($action == 'reject'){
                        $wpdb->query("UPDATE `ot_custom_purchase_order` SET `status` = 'reject' WHERE `purchase_order_id` = ".$idPurchaseOrder.";");
                    }
                }
            }
            else{
                $_GET['message-error']="Please select a purchase order!";
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
        }
    }

    if(isset($_POST["actionRequestInformation"])){
        if(isset($_POST['selectActionRequestInformation']) and $_POST['selectActionRequestInformation'] !== "-1"){
            if (isset($_POST['idRequestInformation'])) {
                $idRequestInformation = $_POST["idRequestInformation"];
                foreach ($idRequestInformation as $idRequestInformation){
                    updateRequestInformation($idRequestInformation, "processed");
                    $_GET['message-success']="Request processed!";
                }
            }
            else{
                $_GET['message-error']="Please select a request!";
            }
        }
        else{
            $_GET['message-error']="Please select one action!";
        }
    }
    
    if(isset($_POST["actionViewRequestInfo"])){
        if(isset($_POST["idRequestInfo"])){
            $_GET['view-products-request'] = true;
            $_GET['view-details-idRequestInfo'] = $_POST['idRequestInfo'];
        }
    }
    
    if(isset($_POST["actionBackRequestInfoList"])){
        if(isset($_POST["idRequestInfo"])){
            $_GET['view-products-request'] = false;
            $_GET['view-details-idRequestInfo'] = $_POST['idRequestInfo'];
        }
    }

    if(isset($_POST["actionUpdateProduct"])){
        if(isValidRequiredFields($_POST)){
            updateProductFileItem($_POST);
            $_GET['message-success'] = 'Product updated success!';
            $_GET['view-item-details'] = true;
            $_GET['idProductFile'] = $_POST['idProductFile'];
            $_GET['idProduct'] = $_POST['idProduct'];
        }else{
            $_GET['view-item-details'] = true;
            $_GET['idProductFile'] = $_POST['idProductFile'];
            $_GET['idProduct'] = $_POST['idProduct'];
        }
    }

    function isValidRequiredFields($post){
        $result = true;
        if(!isset($post['distributor_id']) or $post['distributor_id'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid distributor id.';
            return $result;
        }
        if(!isset($post['distributor_name']) or $post['distributor_name'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid distributor name.';
            return $result;
        }
        if(!isset($post['distributor_sku_id']) or $post['distributor_sku_id'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid sku id.';
            return $result;
        }
        if(!isset($post['distributor_sku_description']) or $post['distributor_sku_description'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid sku description.';
            return $result;
        }
        if(!isset($post['packaging_measure'] ) or $post['packaging_measure'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid packaging measure.';
            return $result;
        }

        if(!isset($post['quantity']) or $post['quantity']      == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid quantity.';
            return $result;
        }
        if(!isset($post['price_unit']) or $post['price_unit']    == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid price unit.';
            return $result;
        }
        if(!isset($post['warehouse_location_id']) or $post['warehouse_location_id'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid warehouse.';
            return $result;
        }
        if(!isset($post['warehouse_location_address']) or $post['warehouse_location_address'] == ""){
            $result = false;
            $_GET['message-error'] = 'Please set a valid warehouse location.';
            return $result;
        }
        return $result;
    }

    if(isset($_POST["actionBackFileDetail"])){
        $_GET['view-details'] = true;
        $_GET['view-details-idProductFile'] = $_POST['idProductFile'];
    }

    if(isset($_POST["actionDownloadInventory"])) {
        downloadInventory();
    }

    if(isset($_POST["actionUpdateUploadFile"])){
        $errors = validateFile($_FILES['fileToUpload']);

        if (count($errors) === 0) {

            $formatDate = getFormatDate();
            $current_user = getCurrentUser();
            $target_dir = plugin_dir_path(__FILE__) . "uploads/";
            $fullPatch = getFullPatch($target_dir, $_FILES["fileToUpload"], $formatDate, $current_user);
            $filename = ($_FILES["fileToUpload"]['name']);
            $userID = $current_user->ID;
            if(uploadFile($_FILES["fileToUpload"], $fullPatch)){
                readAndProcessFileToUpdate($fullPatch,$filename , $userID, $formatDate);
                $_GET['message-success'] = "The inventory has been updated!";

            }else{
                $_GET['message-error'] = 'File was not uploaded';
            }
        } else {
            $_GET['message-error'] = $errors[0];
        }
    }

    if(isset($_POST["actionViewOfferInfo"])){
        if(isset($_POST["idOfferInfo"])){
            $_GET['view-products-offer'] = true;
            $_GET['idOfferInfo'] = $_POST['idOfferInfo'];
        }
    }

    if(isset($_POST["actionBackOfferInfoList"])){
        if(isset($_POST["idOfferInfo"])){
            $_GET['view-products-offer'] = false;
            $_GET['idOfferInfo'] = $_POST['idOfferInfo'];
        }
    }