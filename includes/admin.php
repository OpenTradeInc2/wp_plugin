<?php

    function errorMessage($msg) {
        $_GET['message-error'] = $msg;
    }
    
    function getCurrentUser(){
        global $current_user;

        $current_user = wp_get_current_user();
        
        return $current_user;
    }

    function getFormatDate(){
        $formatDate = date("Ymdhis");
        return $formatDate;
    }

    function createUser($userName, $email, $first_name, $lastName, $distributorID){
        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_id = wp_create_user( $userName, $random_password, $email );

        $fullName = $first_name." ".$lastName;

        wp_update_user(
            array(
                'ID'          =>    $user_id,
                'nickname'    =>    $fullName,
                'display_name' => $fullName
            )
        );

        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $lastName);

        $role =get_role( 'open-trade-contributor' );

        if($role == null){
            $newRole =get_role( 'administrator' );
            add_role('open-trade-contributor', 'OT Role', $newRole->capabilities);
            $role =get_role( 'open-trade-contributor' );
        }

        $user = new WP_User( $user_id );
        $user->set_role( $role->name );

        addUserDistributor($user_id, $distributorID);

        $message = "<html>
                            <head>
                                <font FACE=\"impact\" SIZE=6 COLOR=\"red\">O</font><font FACE=\"impact\" SIZE=6 COLOR=\"black\">PENTRADE</font>
                                <br/>
                                <h1>Open Trade Credentials</h1>
                            </head>
                            <body>
                                Welcome to Open Trade, Your user is : ".$userName." and your password is: ".$random_password."
                            </body>
                        </html>";

        add_filter('wp_mail_content_type',create_function('', 'return "text/html";'));

        wp_mail( $email, 'Welcome To Open Trade!', $message );

        return $user_id;
    }

    function getCurrentDistributor(){
        $user = getCurrentUser();
        $result = -1;
        global $wpdb;

        if($wpdb->check_connection()){
            $totalIDs = $wpdb->get_results("SELECT
                                                  `distributor_user_distributor_id` as distributor_id
                                                 FROM `ot_custom_distributor_user`
                                                 WHERE `distributor_user_userid` = ".$user->ID.";");

            if($totalIDs[0]->distributor_id !== ""){
                $result = $totalIDs[0]->distributor_id;
            }
        }
        return $result;
    }

    function getDistributorByID($idDistributor){
        global $wpdb;
        $distributor = null;
        if($wpdb->check_connection()){
            $distributor = $wpdb->get_results("SELECT * FROM `ot_custom_distributor` WHERE `distributor_id` = ".$idDistributor.";",true);            
        }
        return $distributor[0];
    }

    function getWarehouseByID($idWarehouse){
        global $wpdb;
        $warehouse = null;
        if($wpdb->check_connection()){
            $warehouse = $wpdb->get_results("SELECT
                                                w.`warehouse_name`,
                                                w.`warehouse_file_id`,
                                                wl.`location_id`,
                                                wl.`warehouse_id`,
                                                wl.`zipcode`,
                                                wl.`latitude`,
                                                wl.`longitude`,
                                                wl.`location`,
                                                wl.`city`
                                            FROM `ot_custom_warehouse_location` AS wl INNER JOIN `ot_custom_warehouse` as w ON wl.`warehouse_id` = w.`warehouse_id`
                                            WHERE w.`warehouse_id` = ".$idWarehouse.";",true);
        }
        return $warehouse[0];
    }

    function approvedUser($idUser){

        approvedUserDistributor($idUser);
        $code = sha1( $idUser . time() );
        $url = get_page_by_title( 'User Activated' )->guid;
        $activation_link = add_query_arg( array( 'key' => $code, 'user' => $idUser ),$url);
        add_user_meta( $idUser, 'has_to_be_activated', $code, true );
        $user = get_user_by('ID',$idUser);
        $headers = 'Content-type: text/html; charset=utf-8' . "\r\n";
        $message ='<html>
                        <head>
                            <font FACE="impact" SIZE=6 COLOR="red">O</font><font FACE="impact" SIZE=6 COLOR="black">PENTRADE</font>
                            <br/>
                            <h1>User Activation</h1>
                        </head>
                        <body>   
                            <table>
                                <tr>
                                    <td>Congrats your user of open Trade is create .Here is your activation link:</td>
                                </tr>
                                <tr>
                                <td><label>'.$activation_link.'</label></td>
                                </tr>
                            </table>
                        </body>
                </html>';

        wp_mail( $user->user_email, 'OpenTrade User Activation', $message, $headers);
    }
