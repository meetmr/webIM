<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//数据入库处理 > 实体 + 转义
function databaseFilt($data, $force = false){
    if(empty($data)){
        return $data;
    }

    if(is_array($data)){
        foreach ($data as $key => $value){
            $data[$key] = databaseFilt($value, $force);
        }
    }else{
        $data = addslashes($data);
    }

    return $data;
}

/**
 * 数据实体化
 * $outKey 排除key“,”号分隔
 */
function dataEntity($data, $outKey = ''){
    if(empty($data)){
        return $data;
    }

    if(is_array($data)){
        foreach ($data as $key => $value){
            if(!empty($outKey)){
                $outKeys = explode(',', $outKey);
                if(in_array($key, $outKeys, true)){
                    continue;
                }
            }

            $data[$key] = dataEntity($value, $outKey);
        }
    }else{
        $data = htmlspecialchars($data);
    }

    return $data;
}

/**
 * xss去掉关键词
 * $val 内容
 * $out 排除词
 */
function xssRemoveKeyword($val, $out = '') {
    if(strpos($out, 'fh') === false){
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    }

    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    }

    if(strpos($out, 'fh') === false){
        $val = str_replace("`","‘",$val);
        $val = str_replace("'","‘",$val);
        $val = str_replace("\"","“",$val);
        $val = str_replace(",","，",$val);
        $val = str_replace("(","（",$val);
        $val = str_replace(")","）",$val);
    }

    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra3 = array('cookie', 'document', 'innerHtml');
    $keywords = array_merge($ra1, $ra2, $ra3);
    $ra = array();

    //排除标签
    foreach($keywords as $key => $value){
        if(strpos($out, $value) !== false){
            continue;
        }

        $ra[] = $value;
    }

    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'　'.substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }


    //合法开放
    $val = preg_replace('/ex　pression\_(\d+)\.png/', 'Expression_$1.png', $val);
    $val = preg_replace('/videos\/pla　yer\/file/', 'videos/player/file', $val);

    return $val;
}

//初始化表单字段
function initBdzd($value){
    if(empty($value)){
        return $value;
    }

    $value = htmlspecialchars(htmlspecialchars_decode($value));

    return $value;
}

//去掉转义字符
function stripslashesAll($data){
    if(is_array($data)){
        foreach ($data as $key => $val){
            $data[$key] = stripslashesAll($val);
        }
    }else{
        if($data){
            $data = stripslashes($data);
        }
    }
    return $data;
}

function dd($v){
    echo "<pre>";
    print_r($v);
    die;
}