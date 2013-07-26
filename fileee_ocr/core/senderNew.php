<?php

$data_hash = array('username' => '', 'data-id' => '');

$host = '' //Fileee Server

$path = '' //Server Pfadadresse


//$file = file_get_contents($_FILES['userfile']['tmp_name']); 
//$byteArr = str_split($file); $byteArr = array_map('ord', $byteArr);



function http_post($host, $path, $file = '', $username, $data-id, $url) {
    $boundary = md5(uniqid());
    //Wenn Datei vorhanden
    if ($file) {
        $binary = file_get_contents($file);
        //$byteArr = str_split($binary); 
        //$byteArr = array_map('ord', $byteArr);
        
        $name = basename($file);

        //Typen passend setzen
        $content_type = "multipart/form-data; boundary=$boundary";
 
        $items = array();

        array_push($items, "Content-Type: application/octet-stream\r\nContent-Transfer-Encoding: binary\r\n\r\n$binary\r\n--$boundary--\r\n");

        /*foreach (array_keys($data_hash) as $key) {
            array_push($items, "--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n{$data_hash[$key]}\r\n");
        }*/
        array_push($items, "--$boundary\r\nContent-Type: application/json; charset=UTF-8\r\n");
        array_push($items, "\{\"username\":\"{$username}\", \r\n \"data-id\":\"{$data-id}\",\r\n \"url\":\"{$url}\"\}")
        
        $data = implode('', $items);
    }   /* else {
        $content_type = 'application/json; charset=UTF-8';
 
        $items = array();
        foreach (array_keys($data_hash) as $key) {
            array_push($items, urlencode($key) . '=' . urlencode($data_hash[$key]));
        }
        $data = implode('&', $items);
    }*/
 
    $content_length = strlen($data);
    $fp = fsockopen($host, 8008);
    fputs($fp, "POST $path HTTP/1.1\r\n");
    fputs($fp, "Host: $host\r\n");
    fputs($fp, "Content-Type: $content_type\r\n");
    fputs($fp, "Content-Length: $content_length\r\n");
    fputs($fp, "Connection: close\r\n\r\n");
    fputs($fp, $data, $content_length);
 
    $http_response = stream_get_contents($fp);
    fclose($fp);
 
    list($headers, $body) = explode("\r\n\r\n", $http_response, 2);
    return $body;
}

?>