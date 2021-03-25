<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");

$logoSizes = array(512,256,128,96,72,48);

if(isset($_POST['token']) && isset($_FILES['logo'])){
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

    $file_name = $_FILES['logo']['name'];
    $file_tmp_name = $_FILES['logo']['tmp_name'];
    $file_target = '../resimg/'.$restaurantFolder."/logo/";
    createPath($file_target);
    $file_size = $_FILES['logo']['size'];

    list($width, $height) = getimagesize($file_tmp_name);
    $ratio = $height / $width;
    // check image be bigger than 512
    if(($width < 512 || $height < 512) )
        exit(json_encode(array('statusCode'=>400, "details"=>"image should be at least 512px")));


    $tempFolder = '../resimg/temp/';

    // Rename file
    $temp = explode('.', $file_name);
    $fileExtension = ".".end($temp);
    $newfilename = 'logo';


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
        foreach ($logoSizes as $eLogoSize){
            copy($tempFolder.$newfilename.$fileExtension, $file_target.$newfilename."X$eLogoSize".$fileExtension);
            $magicianObj = new imageLib($file_target.$newfilename."X$eLogoSize".$fileExtension);
            $magicianObj->resizeImage($eLogoSize, 100, 'landscape');
            $magicianObj -> saveImage($file_target.$newfilename."X$eLogoSize".$fileExtension, 60);
        }
        unlink($tempFolder.$newfilename.$fileExtension);
        $resAccess->update("info",array('logo_link'=>"https://dl.cuki.ir/resimg/".$restaurantFolder."/logo/".$newfilename."X512".$fileExtension), "1=1");
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