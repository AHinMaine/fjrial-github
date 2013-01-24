<?php
/*taken from http://www.emanueleferonato.com/2008/07/22/sending-email-with-multiple-attachments-with-php/ */
 
// array with filenames to be sent as attachment
$files = array("file_1.ext","file_2.ext","file_3.ext",......);
 
// email fields: to, from, subject, and so on
$to = "mail@mail.com";
$from = "mail@mail.com"; 
$subject ="My subject"; 
$message = "My message";
$headers = "From: $from";
 
// boundary 
$semi_rand = md5(time()); 
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 
 
// headers for attachment 
$headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 
 
// multipart boundary 
$message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n"; 
$message .= "--{$mime_boundary}\n";
 
// preparing attachments
for($x=0;$x<count($files);$x++){
  $file = fopen($files[$x],"rb");
	$data = fread($file,filesize($files[$x]));
	fclose($file);
	$data = chunk_split(base64_encode($data));
	$message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$files[$x]\"\n" . 
	"Content-Disposition: attachment;\n" . " filename=\"$files[$x]\"\n" . 
	"Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
	$message .= "--{$mime_boundary}\n";
}
 
// send
 
$ok = @mail($to, $subject, $message, $headers); 
if ($ok) { 
	echo "<p>mail sent to $to!</p>"; 
} else { 
	echo "<p>mail could not be sent!</p>"; 
} 
 
?>
