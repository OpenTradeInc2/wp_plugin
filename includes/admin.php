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

    function createUser($userName, $email, $fullName, $distributorID){
        $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_id = wp_create_user( $userName, $random_password, $email );

        wp_update_user(
            array(
                'ID'          =>    $user_id,
                'nickname'    =>    $fullName,
                'display_name' => $fullName
            )
        );

        update_user_meta($user_id, 'first_name', $fullName);

        $role =get_role( 'open-trade-contributor' );

        if($role == null){
            $newRole =get_role( 'administrator' );
            add_role('open-trade-contributor', 'OT Role', $newRole->capabilities);
            $role =get_role( 'open-trade-contributor' );
        }

        $user = new WP_User( $user_id );
        $user->set_role( $role->name );

        addUserDistributor($user_id, $distributorID);

        wp_mail( $email, 'Welcome To Open Trade!', 'Your User Name: ' . $userName.', '.'Your Password: ' . $random_password );

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
