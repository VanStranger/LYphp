<?php
function curlhtml($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $file_content = curl_exec($ch);
    curl_close($ch);
    return $file_content;
}
//数组转xml
function arrayToXml($arr){
    $xml = "<xml>";
    foreach ($arr as $key=>$val){
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;
}
//将XML转为array
function xmlToArray($xml){
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

function uploadImgs($filename="imgs",$path="/images/uploads"){
    if (!$_FILES[$filename]) {
        return array("state"=>0,"error"=>'无图片上传信息，或文件key设置错误');
        die ();
    }
    $files=array();
    if(is_array($_FILES[$filename]['name'])){
        for($i=0,$len=count($_FILES[$filename]['name']);$i<$len;$i++){
            if ($_FILES[$filename]['error'][$i] > 0) {
                switch ($_FILES [$filename]['error'][$i] ) {
                    case 1 :
                        $error_log = '文件大小超出php限制';
                        break;
                    case 2 :
                        $error_log = '文件大小超出form限制';
                        break;
                    case 3 :
                        $error_log = '文件仅成功上传部分内容';
                        break;
                    case 4 :
                        $error_log = '文件上传失败';
                        break;
                    default :
                        break;
                }
                return  array("state"=>0,"error"=>$error_log);
                die ();
            } else {
                $img_data[$i] = $_FILES[$filename]['tmp_name'][$i];
                $size[$i] = getimagesize($img_data[$i]);
                $file_type[$i] = $size[$i]['mime'];
                if (!in_array($file_type[$i], array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
                    $error_log = 'only allow jpg,png,gif';
                    return array("state"=>0,"error"=>$error_log,"file_type"=>$file_type[$i],"img_data"=>$img_data[$i]);
                    die ();
                }
                switch($file_type[$i]) {
                    case 'image/jpg' :
                    case 'image/jpeg' :
                    case 'image/pjpeg' :
                        $extension = 'jpg';
                        break;
                    case 'image/png' :
                        $extension = 'png';
                        break;
                    case 'image/gif' :
                        $extension = 'gif';
                        break;
                }
            }
            if (!is_file($img_data[$i])) {
                return array("state"=>0,"error"=>"部分文件上传失败");
                die ();
            }
            $save_path=$path;
            if(substr($save_path,0,1)=="/"){
                $save_path=$_SERVER['DOCUMENT_ROOT'].$save_path;
            }
            if(!is_dir($save_path)){
                mkdir($save_path,0755,true);
            }
            $uinqid = uniqid();
            $file=$uinqid . '.' . $extension;
            $files[]=$file;
            $savename = $save_path . '/' . $file;
            $result = move_uploaded_file( $img_data[$i], $savename );
            if ( ! $result || ! is_file( $savename ) ) {
                return array("state"=>0,"error"=>"upload error");
                die ();
            }
        }


    }else{
        if ($_FILES[$filename]['error'] > 0) {
            switch ($_FILES [$filename] ['error']) {
                case 1 :
                    $error_log = 'The file is bigger than this PHP installation allows';
                    break;
                case 2 :
                    $error_log = 'The file is bigger than this form allows';
                    break;
                case 3 :
                    $error_log = 'Only part of the file was uploaded';
                    break;
                case 4 :
                    $error_log = 'No file was uploaded';
                    break;
                default :
                    break;
            }
            return  array("state"=>0,"error"=>$error_log);
            die ();
        } else {
            $img_data = $_FILES[$filename]['tmp_name'];
            $size = getimagesize($img_data);
            $file_type = $size['mime'];
            if (!in_array($file_type, array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
                $error_log = 'only allow jpg,png,gif';
                return array("state"=>0,"error"=>$error_log);
                die ();
            }
            switch($file_type) {
                case 'image/jpg' :
                case 'image/jpeg' :
                case 'image/pjpeg' :
                    $extension = 'jpg';
                    break;
                case 'image/png' :
                    $extension = 'png';
                    break;
                case 'image/gif' :
                    $extension = 'gif';
                    break;
            }
        }
        if (!is_file($img_data)) {
            return array("state"=>0,"error"=>"upload error");
            die ( );
        }
        $save_path=$path;
        if(substr($save_path,0,1)=="/"){
            $save_path=$_SERVER['DOCUMENT_ROOT'].$save_path;
        }
        if(!is_dir($save_path)){
            mkdir($save_path,0755,true);
        }
        $uinqid = uniqid();
        $file=$uinqid . '.' . $extension;
        $files[]=$file;
        $save_filename = $save_path . '/' . $file;
        $result = move_uploaded_file($img_data, $save_filename );
        if ( ! $result || ! is_file( $save_filename ) ) {
            return array("state"=>0,"error"=>"upload error");
            die ();
        }
    }
    return array("state"=>1,"files"=>$files,"f"=>$_FILES,"path"=>$path,"filename"=>$filename);
}

function uploadFiles($filename="upload_file",$path="/uploads"){
    if (!isset($_FILES[$filename])) {
        return array("state"=>0,"error"=>'无图片上传信息，或文件key设置错误',"f"=>$_FILES);
        die ();
    }
    $files=array();
    if(is_array($_FILES[$filename]['name'])){
        for($i=0,$len=count($_FILES[$filename]['name']);$i<$len;$i++){
            if ($_FILES[$filename]['error'][$i] > 0) {
                switch ($_FILES [$filename]['error'][$i] ) {
                    case 1 :
                        $error_log = '文件大小超出php限制';
                        break;
                    case 2 :
                        $error_log = '文件大小超出form限制';
                        break;
                    case 3 :
                        $error_log = '文件仅成功上传部分内容';
                        break;
                    case 4 :
                        $error_log = '文件上传失败';
                        break;
                    default :
                        break;
                }
                return  array("state"=>0,"error"=>$error_log);
                die ();
            } else {
                $img_data[$i] = $_FILES[$filename]['tmp_name'][$i];
                $extension_arr=explode(".",$_FILES[$filename]['name'][$i]);
                $extension=$extension_arr[count($extension_arr)-1];
            }
            if (!is_file($img_data[$i])) {
                return array("state"=>0,"error"=>"部分文件上传失败");
                die ();
            }
            $save_path=$path;
            if(substr($save_path,0,1)=="/"){
                $save_path=$_SERVER['DOCUMENT_ROOT'].$save_path;
            }
            if(!is_dir($save_path)){
                mkdir($save_path,0755,true);
            }
            $uinqid = uniqid();
            $file=$uinqid . '.' . $extension;
            $files[]=$file;
            $save_path=rtrim($save_path,"\/");
            $savename = $save_path . '/' . $file;
            $result = move_uploaded_file( $img_data[$i], $savename );
            if ( ! $result || ! is_file( $savename ) ) {
                return array("state"=>0,"error"=>"upload error");
                die ();
            }
        }
    }else{
        if ($_FILES[$filename]['error'] > 0) {
            switch ($_FILES [$filename] ['error']) {
                case 1 :
                    $error_log = 'The file is bigger than this PHP installation allows';
                    break;
                case 2 :
                    $error_log = 'The file is bigger than this form allows';
                    break;
                case 3 :
                    $error_log = 'Only part of the file was uploaded';
                    break;
                case 4 :
                    $error_log = 'No file was uploaded';
                    break;
                default :
                    break;
            }
            return  array("state"=>0,"error"=>$error_log);
            die ();
        } else {
            $img_data = $_FILES[$filename]['tmp_name'];
            $extension_arr=explode(".",$_FILES[$filename]['name']);
            $extension=$extension_arr[count($extension_arr)-1];
        }
        if (!is_file($img_data)) {
            return array("state"=>0,"error"=>"upload error");
            die ( );
        }
        $save_path=$path;
        if(substr($save_path,0,1)=="/"){
            $save_path=$_SERVER['DOCUMENT_ROOT'].$save_path;
        }
        $save_path=rtrim($save_path,"\/");
        if(!is_dir($save_path)){
            mkdir($save_path,0755,true);
        }
        $uinqid = uniqid();
        $file=$uinqid . '.' . $extension;
        $files[]=$file;
        $save_filename = $save_path . '/' . $file;
        $result = move_uploaded_file($img_data, $save_filename );
        if ( ! $result || ! is_file( $save_filename ) ) {
            return array("state"=>0,"error"=>"upload error");
            die ();
        }
    }
    return array("state"=>1,"files"=>$files,"f"=>$_FILES,"path"=>$path,"filename"=>$filename);
}

