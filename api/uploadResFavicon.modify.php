<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");


if(isset($_POST['token']) && isset($_FILES['favicon'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";
    include('php-image-magician/php_image_magician.php');

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($_POST['token'], "restaurants")&&
        $oursAccess->hasTokenAccess($_POST['token'], "restaurants", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $resEnglishName = $oursAccess->select('english_name','restaurants',"`token`='".$_POST['token']."'");

    $connRes = MysqlConfig::connRes($resEnglishName);
    $resAccess = new MysqldbAccess($connRes);


    $restaurantFolder = preg_replace('/ /', "_", $resEnglishName);

    $file_name = $_FILES['favicon']['name'];
    $file_tmp_name = $_FILES['favicon']['tmp_name'];
    $file_target = '../resimg/'.$restaurantFolder."/favicon/";
    createPath($file_target);
    $file_size = $_FILES['favicon']['size'];

    list($width, $height) = getimagesize($file_tmp_name);
    $ratio = $height / $width;
    // check image be bigger than 512
    if(($width < 512 || $height < 512) )
        exit(json_encode(array('statusCode'=>400, "details"=>"image should be at least 512px")));


    $tempFolder = '../resimg/temp/';

    // Rename file
    $temp = explode('.', $file_name);
    $fileExtension = ".".end($temp);
    $newfilename = 'faviconX64';


    $allowed_image_extension = array(
        "png",
        "jpg",
        "jpeg",
    );

    rename($file_tmp_name,$tempFolder.$newfilename.$fileExtension);

    // Get image file extension
    $file_extension = strtolower(pathinfo($tempFolder.$newfilename.$fileExtension, PATHINFO_EXTENSION));


    if (! file_exists($tempFolder.$newfilename.$fileExtension)) {
        exit(json_encode(array("statusCode" => 404, "details"=>"Choose image file to upload.")));
    }    // Validate file input to check if is with valid extension
    else if (! in_array($file_extension, $allowed_image_extension)) {
        exit(json_encode(array(
            "statusCode" => 400,"details"=>"Upload valid images. Only PNG and JPEG are allowed.")));
    } else {
        rename($tempFolder.$newfilename.$fileExtension, $file_target.$newfilename.$fileExtension);
        $magicianObj = new imageLib($file_target.$newfilename.$fileExtension);
        $magicianObj -> resizeImage(64, 100, 'landscape');
        $magicianObj -> saveImage($file_target.$newfilename.$fileExtension, 50);
        chmod($file_target.$newfilename.$fileExtension,0644);
        $resAccess->update("info",array('favicon_link'=>"https://dl.cuki.ir/resimg/".$restaurantFolder."/favicon/".$newfilename.$fileExtension), "1=1");
        exit(json_encode(array('statusCode'=>200)));

    }
}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}




/**
 * recursively create a long directory path
 */
function createPath($path) {
    if (is_dir($path)) return true;
    $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
    $return = createPath($prev_path);
    return ($return && is_writable($prev_path)) ? mkdir($path) : false;
}