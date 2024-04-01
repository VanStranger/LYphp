<?php
use \ly\lib\Result as Result;
function curlhtml($url, $post = false, $type = "",$header=[], $cookie = '', $returnCookie = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        if (is_array($post)) {
            if ($type == 'json') { //json $_POST=json_decode(file_get_contents('php://input'), TRUE);
                $headers = ["Content-Type:application/json"];
                $data    = json_encode($post);
                // echo $data;
                $header=array_merge($headers,$header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } if($type=="urlencoded"){
                $headers = ["Content-Type:application/x-www-form-urlencoded"];
                $data    = http_build_query($post);
                // echo $data;
                $header=array_merge($headers,$header);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
        }
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    if ($cookie) {
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    }
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
    if (curl_errno($ch)) {
        return curl_error($ch);
    }
    curl_close($ch);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie']  = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}
//数组转xml
function arrayToXml($arr)
{
    $xml = "<xml>";
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}
//将XML转为array
function xmlToArray($xml)
{
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}
function listToTree($list, $root = 0, $strick = false, $pk = 'id', $pid = 'parent_id', $child = 'children')
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $list[$child]      = [];
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = 0;
            if (isset($data[$pid])) {
                $parentId = $data[$pid];
            }

            if (isset($refer[$parentId])) {
                $parent           = &$refer[$parentId];
                $parent[$child][] = &$list[$key];
            } elseif (!$strick || $root == $parentId) {
                $tree[] = &$list[$key];
            }

        }
    }
    return $tree;
}
function treeToList($tree = [], $children = 'children')
{
    if (empty($tree) || !is_array($tree)) {
        return $tree;
    }
    $arrRes = [];
    foreach ($tree as $k => $v) {
        $arrTmp = $v;
        unset($arrTmp[$children]);
        $arrRes[] = $arrTmp;
        if (!empty($v[$children])) {
            $arrTmp = treeToList($v[$children]);
            $arrRes = array_merge($arrRes, $arrTmp);
        }
    }
    return $arrRes;
}
function uploadImgs($filename = "imgs", $path = "/images/uploads")
{
    if (!$_FILES[$filename]) {
        return Result::fail('无图片上传信息，或文件key设置错误', 1, null, $_FILES);
        die();
    }
    $files = array();
    if (is_array($_FILES[$filename]['name'])) {
        for ($i = 0, $len = count($_FILES[$filename]['name']); $i < $len; $i++) {
            if ($_FILES[$filename]['error'][$i] > 0) {
                switch ($_FILES[$filename]['error'][$i]) {
                    case 1:
                        $error_log = '文件大小超出php限制';
                        break;
                    case 2:
                        $error_log = '文件大小超出form限制';
                        break;
                    case 3:
                        $error_log = '文件仅成功上传部分内容';
                        break;
                    case 4:
                        $error_log = '文件上传失败';
                        break;
                    default:
                        break;
                }
                return Result::fail($error_log);
                die();
            } else {
                $img_data[$i]  = $_FILES[$filename]['tmp_name'][$i];
                $size[$i]      = getimagesize($img_data[$i]);
                $file_type[$i] = $size[$i]['mime'];
                if (!in_array($file_type[$i], array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'image/webp'))) {
                    $error_log = 'only allow jpg,png,gif,webp';
                    return Result::fail($error_log, 1, null, ["file_type" => $file_type[$i], "img_data" => $img_data[$i]]);
                    die();
                }
                switch ($file_type[$i]) {
                    case 'image/jpg':
                    case 'image/jpeg':
                    case 'image/pjpeg':
                        $extension = 'jpg';
                        break;
                    case 'image/png':
                        $extension = 'png';
                        break;
                    case 'image/gif':
                        $extension = 'gif';
                        break;
                    case 'image/webp':
                        $extension = 'webp';
                        break;
                }
            }
            if (!is_file($img_data[$i])) {
                return Result::fail("部分文件上传失败");
                die();
            }
            $save_path = $path;
            if (substr($save_path, 0, 1) == "/") {
                $save_path = $_SERVER['DOCUMENT_ROOT'] . $save_path;
            }
            if (!is_dir($save_path)) {
                mkdir($save_path, 0755, true);
            }
            $uinqid   = uniqid();
            $file     = $uinqid . '.' . $extension;
            $files[]  = $path . "/" . $file;
            $savename = $save_path . '/' . $file;
            $result   = move_uploaded_file($img_data[$i], $savename);
            if (!$result || !is_file($savename)) {
                return Result::fail("upload error");
                die();
            }
        }

    } else {
        if ($_FILES[$filename]['error'] > 0) {
            switch ($_FILES[$filename]['error']) {
                case 1:
                    $error_log = 'The file is bigger than this PHP installation allows';
                    break;
                case 2:
                    $error_log = 'The file is bigger than this form allows';
                    break;
                case 3:
                    $error_log = 'Only part of the file was uploaded';
                    break;
                case 4:
                    $error_log = 'No file was uploaded';
                    break;
                default:
                    break;
            }
            return Result::fail($error_log);
            die();
        } else {
            $img_data  = $_FILES[$filename]['tmp_name'];
            $size      = getimagesize($img_data);
            $file_type = $size['mime'];
            if (!in_array($file_type, array('image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'image/webp'))) {
                $error_log = 'only allow jpg,png,gif,webp';
                return Result::fail($error_log);
                die();
            }
            switch ($file_type) {
                case 'image/jpg':
                case 'image/jpeg':
                case 'image/pjpeg':
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $extension = 'png';
                    break;
                case 'image/gif':
                    $extension = 'gif';
                    break;
                case 'image/webp':
                    $extension = 'webp';
                    break;
            }
        }
        if (!is_file($img_data)) {
            return Result::fail("upload error");
            die();
        }
        $save_path = $path;
        if (substr($save_path, 0, 1) == "/") {
            $save_path = $_SERVER['DOCUMENT_ROOT'] . $save_path;
        }
        if (!is_dir($save_path)) {
            mkdir($save_path, 0755, true);
        }
        $uinqid        = uniqid();
        $file          = $uinqid . '.' . $extension;
        $files[]       = $path . "/" . $file;
        $save_filename = $save_path . '/' . $file;
        $result        = move_uploaded_file($img_data, $save_filename);
        if (!$result || !is_file($save_filename)) {
            return Result::fail("upload error");
            die();
        }
    }
    return Result::success($files, "", ["f" => $_FILES, "path" => $path, "filename" => $filename]);
}

function uploadFiles($filename = "upload_file", $path = "/uploads")
{
    if (!isset($_FILES[$filename])) {
        return Result::fail('无图片上传信息，或文件key设置错误', 1, null, $_FILES);
        die();
    }
    $files = array();
    if (is_array($_FILES[$filename]['name'])) {
        for ($i = 0, $len = count($_FILES[$filename]['name']); $i < $len; $i++) {
            if ($_FILES[$filename]['error'][$i] > 0) {
                switch ($_FILES[$filename]['error'][$i]) {
                    case 1:
                        $error_log = '文件大小超出php限制';
                        break;
                    case 2:
                        $error_log = '文件大小超出form限制';
                        break;
                    case 3:
                        $error_log = '文件仅成功上传部分内容';
                        break;
                    case 4:
                        $error_log = '文件上传失败';
                        break;
                    default:
                        break;
                }
                return Result::fail($error_log);
                die();
            } else {
                $img_data[$i]  = $_FILES[$filename]['tmp_name'][$i];
                $extension_arr = explode(".", $_FILES[$filename]['name'][$i]);
                $extension     = $extension_arr[count($extension_arr) - 1];
            }
            if (!is_file($img_data[$i])) {
                return Result::fail("部分文件上传失败");
                die();
            }
            $save_path = $path;
            if (substr($save_path, 0, 1) == "/") {
                $save_path = $_SERVER['DOCUMENT_ROOT'] . $save_path;
            }
            if (!is_dir($save_path)) {
                mkdir($save_path, 0755, true);
            }
            $uinqid    = uniqid();
            $file      = $uinqid . '.' . $extension;
            $files[]   = $path . "/" . $file;
            $save_path = rtrim($save_path, "\/");
            $savename  = $save_path . '/' . $file;
            $result    = move_uploaded_file($img_data[$i], $savename);
            if (!$result || !is_file($savename)) {
                return Result::fail("upload error");
                die();
            }
        }
    } else {
        if ($_FILES[$filename]['error'] > 0) {
            switch ($_FILES[$filename]['error']) {
                case 1:
                    $error_log = 'The file is bigger than this PHP installation allows';
                    break;
                case 2:
                    $error_log = 'The file is bigger than this form allows';
                    break;
                case 3:
                    $error_log = 'Only part of the file was uploaded';
                    break;
                case 4:
                    $error_log = 'No file was uploaded';
                    break;
                default:
                    break;
            }
            return Result::fail($error_log);
            die();
        } else {
            $img_data      = $_FILES[$filename]['tmp_name'];
            $extension_arr = explode(".", $_FILES[$filename]['name']);
            $extension     = $extension_arr[count($extension_arr) - 1];
        }
        if (!is_file($img_data)) {
            return Result::fail("upload error");
            die();
        }
        $save_path = $path;
        if (substr($save_path, 0, 1) == "/") {
            $save_path = $_SERVER['DOCUMENT_ROOT'] . $save_path;
        }
        $save_path = rtrim($save_path, "\/");
        if (!is_dir($save_path)) {
            mkdir($save_path, 0755, true);
        }
        $uinqid        = uniqid();
        $file          = $uinqid . '.' . $extension;
        $files[]       = $path . "/" . $file;
        $save_filename = $save_path . '/' . $file;
        $result        = move_uploaded_file($img_data, $save_filename);
        if (!$result || !is_file($save_filename)) {
            return Result::fail("upload error");
            die();
        }
    }
    return Result::success($files, "", ["f" => $_FILES, "path" => $path, "filename" => $filename]);
}
function getpage($now, $max, $href = "", $arr = array())
{
    $now = $now ?? 1;
    $max = intval($max) < 1 ? 1 : intval($max);
    $now = $now > $max ? $max : $now;
    switch ($now) {
        case 1:
            $pre = "";
            if ($max == 1) {
                $next = "<li class='pageli'>共1页</li>";
            } else {
                $next = "<li class='pageli sib'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $now + 1))) . "'>下一页</a></li>";
            }
            break;
        case $max:
            $next = "";
            $pre  = "<li class='pageli sib'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $now - 1))) . "'>上一页</a></li>";
            break;
        default:
            $pre  = "<li class='pageli sib'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $now - 1))) . "'>上一页</a></li>";
            $next = "<li class='pageli sib'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $now + 1))) . "'>下一页</a></li>";
            break;
    }
    switch (true) {
        case $now < 5:
            if ($max > 7) {
                $body = "";
                for ($i = 1; $i < 6; $i++) {
                    if ($i == $now) {
                        $body .= "<li class='pageli active'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                    } else {
                        $body .= "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                    }
                }
                $body .= "<li class='pageli'>···</li><li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $max))) . "'>" . $max . "</a></li>";
            } else {
                $body = "";
                for ($i = 1; $i <= $max; $i++) {
                    if ($i == $now) {
                        $body .= "<li class='pageli active'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                    } else {
                        $body .= "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                    }
                }
            }
            break;
        case ($now > $max - 3 && $now > 4):
            $body = "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => 1))) . "'> 1</a></li><li class='pageli'>···</li>";
            for ($i = $now - 3; $i <= $max; $i++) {
                if ($i == $now) {
                    $body .= "<li class='pageli active'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                } else {
                    $body .= "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                }
            }
            break;
        default:
            $body = "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => 1))) . "'>1</a></li><li class='pageli'>···</li>";
            for ($i = $now - 3; $i <= $now + 3; $i++) {
                if ($i == $now) {
                    $body .= "<li class='pageli active'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                } else {
                    $body .= "<li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $i))) . "'>" . $i . "</a></li>";
                }
            }
            $body .= "<li class='pageli'>···</li><li class='pageli'><a class='pagea' href='" . gethref($href, array_merge($arr, array("page" => $max))) . "'>" . $max . "</a></li>";
            break;
    }
    return $pre . $body . $next;
}
