<?php
$destination = "http://yourdomain.com/yoururl";
 
$eol = "\r\n";
$data = '';
 
$mime_boundary=md5(time());
 
$data .= '--' . $mime_boundary . $eol;
$data .= 'Content-Disposition: form-data; name="somedata"' . $eol . $eol;
$data .= "Some Data" . $eol;
$data .= '--' . $mime_boundary . $eol;
$data .= 'Content-Disposition: form-data; name="somefile"; filename="filename.ext"' . $eol;
$data .= 'Content-Type: text/plain' . $eol;
$data .= 'Content-Transfer-Encoding: base64' . $eol . $eol;
$data .= chunk_split(base64_encode("Some file content")) . $eol;
$data .= "--" . $mime_boundary . "--" . $eol . $eol; // finish with two eol's!!
 
$params = array('http' => array(
                  'method' => 'POST',
                  'header' => 'Content-Type: multipart/form-data; boundary=' . $mime_boundary . $eol,
                  'content' => $data
               ));
 
$ctx = stream_context_create($params);
$response = @file_get_contents($destination, FILE_TEXT, $ctx);
?>