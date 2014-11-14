<?php

$log_date = date("Y-m-d H:i:s");


if(!empty($_GET['text'])){

  $log_mode = "GET";
  $log_parameter = "text: " . $_GET['text'];

  spotlight($_GET['text']);

}
else if(!empty($_POST['text'])){

  $log_mode = "POST";
  $log_parameter = "text: " . $_POST['text'];

  spotlight($_POST['text']);

}
else{

  deliver_response(400,"Invalid Request: missing 'text' parameter",NULL);
  $log_parameter = "missing 'text' parameter";

}


# log
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ip = $_SERVER['REMOTE_ADDR'];
}

$logString = "[" . $log_date . "] - " . $ip . " - " . $log_mode . " - Parameter: " . $log_parameter . "\n";
$logFile = "/home/lcuser/logs/bglink_access.log";
$fh = fopen($logFile, 'a') or die ('Cannot open file');
fwrite($fh, $logString);
fclose($fh);



function spotlight($text){

  $api_args = array('text' => stripslashes($text),
  'confidence' => !empty($GLOBALS['api_config']['DBpediaSpotlight']['confidence']) ? $GLOBALS['api_config']['DBpediaSpotlight']['confidence'] : 0.4,
                    'support' => !empty($GLOBALS['api_config']['DBpediaSpotlight']['support']) ? $GLOBALS['api_config']['DBpediaSpotlight']['support'] : 20);

  $ch = curl_init('http://localhost:2222/rest/annotate?'.http_build_query($api_args));

  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept: application/json"));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $raw_result = curl_exec($ch);

  if(!curl_errno($ch)){

    if(!empty($raw_result)){
      $data = json_decode($raw_result, true);

      $entities = array();
      //      if(!empty($data['Resources'])){
      //      if(array_key_exists('Resources',$data)){

      $annotation = 0;
      foreach($data['Resources'] as $e) {
        $annotation = 1;
        $entity = array(
                        'URI' => $e['@URI'],
                        'similarityScore' => $e['@similarityScore'],
                        'surfaceForm' => $e['@surfaceForm'],
                        'offset' => $e['@offset']
                        );
        array_push($entities, $entity);
      }
      $new_data = array();
      if($annotation){
        $new_data['Resources'] = $entities;
      }
      deliver_response(200,"Success",$new_data);

      /*}
        else{
        deliver_response(204,"NULL annotation",NULL);
        }*/
    }
    else{
      deliver_response(500,"Spotlight is not answering",NULL);
    }
  }
  else{
    deliver_response(500,"Spotlight is not answering",NULL);
  }

  curl_close($ch);

}



function deliver_response($status,$status_message,$data){
  header("HTTP/1.1 $status status_message");
  header('Content-Type: application/json; charset=utf-8');

  $response['Status'] = $status;
  $response['Status_message'] = $status_message;
  $response['data'] = $data;

  $json_response = json_encode($response);
  echo $json_response;

}


?>
