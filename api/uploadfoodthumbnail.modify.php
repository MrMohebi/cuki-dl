<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");

if(isset($_POST['token']) && isset($_POST['foodId'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";
    include_once "imgproseess/resizecrop.php";

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


    $foods_id = mysqli_real_escape_string($connRes, $_POST['foodId']);

    $restaurantFolder = preg_replace('/ /', "_", $resEnglishName);

    $file_name = $_FILES['foodThumbnail']['name'];
    $file_tmp_name = $_FILES['foodThumbnail']['tmp_name'];
    $file_target = '../resimg/'.$restaurantFolder."/foodThumbnail/";
    createPath($file_target);
    $file_size = $_FILES['foodThumbnail']['size'];

    $tempFolder = '../resimg/temp/';

    // Rename file
    $temp = explode('.', $file_name);
    $newfilename = strtolower($foods_id.'_'.time().'_foodThumbnail.'.end($temp));

    $allowed_image_extension = array(
        "png",
        "jpg",
        "jpeg",
        "gif"
    );


    ImageResize($file_tmp_name, 125,125, $tempFolder.$newfilename);

    // Get image file extension
    $file_extension = strtolower(pathinfo($tempFolder.$newfilename, PATHINFO_EXTENSION));


    if (! file_exists($tempFolder.$newfilename)) {
        exit(json_encode(array("statusCode" => 404, "details"=>"Choose image file to upload.")));
    }    // Validate file input to check if is with valid extension
    else if (! in_array($file_extension, $allowed_image_extension)) {
        exit(json_encode(array(
            "statusCode" => 400,"details"=>"Upload valid images. Only PNG and JPEG are allowed.")));
    } else {
        if (rename($tempFolder.$newfilename, $file_target.$newfilename)) {
            $thumbnailUrl = "https://dl.cuki.ir/resimg/".$restaurantFolder."/foodThumbnail/".$newfilename;

            if($resAccess->update("foods", array("thumbnail"=>$thumbnailUrl), "`foods_id`='$foods_id'")){
                exit(json_encode(array('statusCode'=>200)));
            }else{
                exit(json_encode(array("statusCode" => 500, "details"=>"Unable to update Thumbnail URL!")));
            }

        } else {
            exit(json_encode(array("statusCode" => 500, "details"=>"Problem in uploading image files.")));
        }
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