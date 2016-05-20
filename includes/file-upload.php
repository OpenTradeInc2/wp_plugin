<?php

    function validateFile($file){

        $errors = array();
        $maxsize = 2097152;
        $filename = $file['name'];
        $fileType = pathinfo($filename, PATHINFO_EXTENSION);

        if ($fileType != 'xlsx' && $fileType != 'xls') {
            $errors[] = 'Invalid file type. Only xlsx and xls types are accepted.';
        }

        if (($file['size'] >= $maxsize) || ($file["size"] == 0)) {
            $errors[] = 'File too large. File must be less than 2 megabytes.';
        }

        global $current_user;
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
        } else {
            $errors[] = 'User not fount.';
        }

        return $errors;

    }

    function uploadFile($file, $fullPatch){

        if (move_uploaded_file($file['tmp_name'], $fullPatch)) {
            $result = true;
        } else {
            $result = false;
        }
    return $result;
}

    function getFullPatch($target_dir, $file, $formatDate, $current_user){
        
        $user_dir = $target_dir . $current_user->user_login;

        if (!file_exists($user_dir)) {
            mkdir($user_dir);
        }

        $fullPatch = $user_dir . '/' . $formatDate . '_' . $file['name'];

        return $fullPatch;

    }     