<?php
function resize_imagejpg($file, $w, $h, $finaldst){

    list($width, $height) = getimagesize($file);
    $src = imagecreatefromjpeg($file);
    $ir = $width / $height;
    $fir = $w / $h;
    if ($ir >= $fir) {
        $newheight = $h;
        $newwidth = $w * ($width / $height);
    } else {
        $newheight = $w / ($width / $height);
        $newwidth = $w;
    }
    $xcor = 0 - ($newwidth - $w) / 2;
    $ycor = 0 - ($newheight - $h) / 2;


    $dst = imagecreatetruecolor($w, $h);
    imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth, $newheight,
        $width, $height);
    imagejpeg($dst, $finaldst);
    imagedestroy($dst);
    return $file;
}


function resize_imagegif($file, $w, $h, $finaldst){

    list($width, $height) = getimagesize($file);
    $src = imagecreatefromgif($file);
    $ir = $width / $height;
    $fir = $w / $h;
    if ($ir >= $fir) {
        $newheight = $h;
        $newwidth = $w * ($width / $height);
    } else {
        $newheight = $w / ($width / $height);
        $newwidth = $w;
    }
    $xcor = 0 - ($newwidth - $w) / 2;
    $ycor = 0 - ($newheight - $h) / 2;


    $dst = imagecreatetruecolor($w, $h);
    $background = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagecolortransparent($dst, $background);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth, $newheight,
        $width, $height);
    imagegif($dst, $finaldst);
    imagedestroy($dst);
    return $file;
}


function resize_imagepng($file, $w, $h, $finaldst){

    list($width, $height) = getimagesize($file);
    $src = imagecreatefrompng($file);
    $ir = $width / $height;
    $fir = $w / $h;
    if ($ir >= $fir) {
        $newheight = $h;
        $newwidth = $w * ($width / $height);
    } else {
        $newheight = $w / ($width / $height);
        $newwidth = $w;
    }
    $xcor = 0 - ($newwidth - $w) / 2;
    $ycor = 0 - ($newheight - $h) / 2;


    $dst = imagecreatetruecolor($w, $h);
    $background = imagecolorallocate($dst, 0, 0, 0);
    imagecolortransparent($dst, $background);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth,
        $newheight, $width, $height);

    imagepng($dst, $finaldst);
    imagedestroy($dst);
    return $file;
}


function ImageResize($file, $w, $h, $finaldst){
    $getsize = getimagesize($file);
    $image_type = $getsize[2];

    if ($image_type == IMAGETYPE_JPEG) {
        resize_imagejpg($file, $w, $h, $finaldst);
    } elseif ($image_type == IMAGETYPE_GIF) {
        resize_imagegif($file, $w, $h, $finaldst);
    } elseif ($image_type == IMAGETYPE_PNG) {
        resize_imagepng($file, $w, $h, $finaldst);
    }
}