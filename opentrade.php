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

    add_action( 'admin_menu', 'admin_menu' );

    function admin_menu(){
        add_menu_page(__( 'Open Trade 2.0', 'textdomain' ), 'Open Trade 2.0', 'manage_options', 'open-trade-menu', 'open_trade_admin' , 'dashicons-migrate', 6 );
        add_submenu_page('open-trade-menu', 'Load Inventory', 'Load Inventory', 'manage_options', 'open-trade-menu' );
        add_submenu_page('open-trade-menu', 'Pending Approval', 'Pending Approval', 'manage_options', 'open-trade-approve', 'wpdocs_pending_approval_submenu_page_callback' );
        add_submenu_page('open-trade-menu', 'Distributors', 'Distributors', 'manage_options', 'open-trade-distributors', 'wpdocs_distributors_submenu_page_callback' );

    }

    function open_trade_admin()
    {
    ?>
    <div class="wrap">
        <h4>Open Trade 2.0</h4>
        <h3>Load New Inventory List</h3>
        <br>
        <form action="" method="post" enctype="multipart/form-data">
            Please select file to upload:
            <input type="file" name="fileToUpload" id="fileToUpload" accept=".xlsx, .xls">
            <input type="submit" value="Upload File" name="actionUploadFile">
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
                        <th>SKU ID</th>
                        <th>SKU Description</th>
                        <th>Product Line</th>
                        <th>Issue Type</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>City</th>
                        <th>Distributor</th>
                        <th>New Product</th>
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
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>City</th>
                        <th>Distributor</th>
                        <th>New Product</th>
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
                            echo "<td>" . $product->sku_id . "</td>";
                            echo "<td>" . $product->sku_description . "</td>";
                            echo "<td>" . $product->product_line . "</td>";
                            echo "<td>" . $product->issue_type . "</td>";
                            echo "<td>" . $product->sum_quantity . "</td>";
                            echo "<td>$" . $product->total_cost . "</td>";
                            echo "<td>" . $product->category . "</td>";
                            echo "<td>" . $product->warehouse . "</td>";
                            echo "<td>" . $product->city . "</td>";
                            echo "<td>" . $distributor[0]->distributor_name . "</td>";
                            echo "<td>" . $isNewProduct . "</td>";
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

                        if ($isConnected) {
                            $products_files = $wpdb->get_results("SELECT `inventory_id`, `file_md5`, `items_count`, `added_date` FROM `ot_custom_inventory_file` WHERE `status` = 'pending_approval' ORDER BY `added_date` DESC");
                            foreach ($products_files as $product_file) {
                                ?>
                                <tr>
                                    <?php
                                    echo "<td><input style='margin-left:8px;' type=\"checkbox\" name=\"idProduct[]\" value=" . $product_file->inventory_id . "></td>";
                                    echo "<td>" . $product_file->file_md5 . "</td>";
                                    echo "<td>" . $product_file->items_count . "</td>";
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
                <h3>Add New Distributor</h3>
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
                    Name:
                    <input type="text" name="nameDistributor">
                    <input id="doAction" class="button action" value="Create" type="submit" name="actionCreateDistributor">
                </form>
            </div>
            <?php
        }
        else if( isset($_GET['view-user-distributor']) and $_GET['view-user-distributor'] == true ){
            ?>
            <div class="wrap">
                <h4>Open Trade 2.0</h4>
                <h3>User List</h3>
                <h4>Users assigned to distributor</h4>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="selectActionAssignedUsers" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="delete" class="hide-if-no-js">Delete</option>
                            <!--<option value="deactivate">Deactivate</option>-->
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkAssignedUsers">
                            <input class="button action" value="Add New User" type="submit" name="actionNewUser">
                        </select>
                    </div>
                <input type="hidden" name="idDistributor" value="<?php _e($_GET['idDistributor']) ?>">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added By</th>
                        <th>Added Date</th>
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
                                                            cdu.`distributor_user_added_date`
                                                       FROM `ot_custom_distributor_user` as cdu
                                                       INNER JOIN `".$wpdb->prefix."users` as users ON users.`ID` = cdu.`distributor_user_userid`
                                                       WHERE cdu.`distributor_user_distributor_id` = ".$distributorID.";");
                        foreach ($users as $user) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input style='margin-left:8px;' type=\"checkbox\" name=\"idAssignedUsers[]\" value=" . $user->distributor_user_userid . "></td>";
                                echo "<td>" . $user->distributor_user_userid . "</td>";
                                echo "<td>" . $user->user_login . "</td>";
                                echo "<td>" . $user->display_name . "</td>";
                                echo "<td>" . $user->user_email . "</td>";
                                echo "<td>" . $user->distributor_user_added_by . "</td>";
                                echo "<td>" . $user->distributor_user_added_date . "</td>";
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
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                        <select name="selectActionUsers" id="bulk-action-selector-top">
                            <option value="-1">Actions</option>
                            <option value="add" class="hide-if-no-js">Add</option>
                            <!--<option value="deactivate">Deactivate</option>-->
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkUsers">
                        </select>
                    </div>
                    <input type="hidden" name="idDistributor" value="<?php _e($_GET['idDistributor']) ?>">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Added Date</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th><input type="checkbox"></th>
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
                                echo "<td><input style='margin-left:8px;' type=\"checkbox\" name=\"idUsers[]\" value=" . $user->ID . "></td>";
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
                <h3>Add New User</h3>
                <form action="" method="post" enctype="multipart/form-data">
                <input id="actionu" class="button action" value="Back User List" type="submit" name="actionBackUserList">
                <input type="hidden" name="idDistributor" value="<?php _e($_POST['idDistributor']) ?>">
                <p>Create a new user and add them to this distributor.</p>
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
                    <th scope="row"><label for="user_login">Username <span class="description">(required)</span></label></th>
                    <td><input name="user_login" id="user_login" value="<?php echo $_POST['user_login'];?>" aria-required="true" autocapitalize="none" autocorrect="off" maxlength="60" type="text" ></td>
                </tr>
                <tr>
                    <th scope="row"><label for="email">Email <span class="description">(required)</span></label></th>
                    <td><input name="email" id="email" value="<?php echo $_POST['email'];?>" type="email"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="first_name">First Name </label></th>
                    <td><input name="first_name" id="first_name" value="<?php echo $_POST['first_name'];?>" type="text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="last_name">Last Name </label></th>
                    <td><input name="last_name" id="last_name" value="<?php echo $_POST['last_name'];?>" type="text"></td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td><input name="actionCreateUser" id="createusersub" class="button button-primary" value="Add New User" type="submit"></td>
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
                            <!--<option value="deactivate">Deactivate</option>-->
                            <input id="doAction" class="button action" value="Apply" type="submit" name="actionBulkDistributors">
                            <input id="doAction" class="button action" value="New Distributor" type="submit" name="actionNewDistributor">
                        </select>
                </div>
                <table class="widefat">
                    <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Distributor</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Users</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>ID</th>
                        <th>Distributor</th>
                        <th>Added By</th>
                        <th>Added Date</th>
                        <th>Users</th>
                    </tr>
                    </tfoot>
                    <tbody>
                    <?php
                    global $wpdb;

                    if($wpdb->check_connection()){
                        $distributors =  $wpdb->get_results("SELECT dist.`distributor_id`, dist.`distributor_name`, user.`user_nicename` as `added_by`, dist.`added_date` FROM `ot_custom_distributor` as dist INNER JOIN `".$wpdb->prefix."users` as user ON dist.`added_by` = user.`ID` ;");
                        foreach ($distributors as $distributor) {
                            ?>
                            <tr>
                                <?php
                                echo "<td><input style='margin-left:8px;' type=\"checkbox\" name=\"idDistributors[]\" value=" . $distributor->distributor_id . "></td>";
                                echo "<td>" . $distributor->distributor_id . "</td>";
                                echo "<td>" . $distributor->distributor_name . "</td>";
                                echo "<td>" . $distributor->added_by . "</td>";
                                echo "<td>" . $distributor->added_date . "</td>";
                                echo "<td><form action=\"\" method=\"post\"><input type=\"hidden\" name=\"idDistributor\" value=\"$distributor->distributor_id\"><input class=\"button action\" value=\"View\" type=\"submit\" name=\"actionViewUsers\"></form></td>";
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

    if(isset($_POST["actionUploadFile"])) {

        $distributorID = getCurrentDistributor();

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
                    $result = approveProductsFiles($idProductsFile);
                } else {
                    errorMessage('Please select a file to approve!');
                }

            } else if ($selectOption == 'reject') {
                errorMessage('Pending implementation.');
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
            createDistributor($_POST['nameDistributor']) ;
            $_GET['view-new-distributor'] = false;
        }else{
            $_GET['message-error'] = "Please set valid name!";
            $_GET['view-new-distributor'] = true;
        }
    }

    if(isset($_POST["actionBulkDistributors"])){
        if(isset($_POST['selectActionDistributors']) and $_POST['selectActionDistributors'] !== "-1"){
            if (isset($_POST['idDistributors'])) {
                $idDistributors = $_POST["idDistributors"];
                foreach ($idDistributors as $idDistributor){
                    deleteDistributor($idDistributor);
                }
            }
            else{
                $_GET['message-error']="Please select a distributor!";
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
            if (isset($_POST['idAssignedUsers'])) {
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
                        $fullName = $_POST["first_name"]." ".$_POST["last_name"];
                        createUser($_POST["user_login"],$_POST["email"],$fullName,$_POST['idDistributor']);
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

?>