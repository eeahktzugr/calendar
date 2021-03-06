<?php
/*
 * Example of a working config for booking to a calendar.
 */
session_start();

//require_once('../backend/libs/config.php');
//require_once('../backend/libs/cal/corecal.php');
define('SITE_ROOT','http://localhost/');
$_SESSION['fname'] = 'firstname';
$_SESSION['lname'] = 'lastname';


/*
 * Core Calendar Functions (contents of the corecal.php file above)
 */

function sendPostRequest($postargs, $token, $cal, $data) {
  global $APIKEY;
  $request = 'https://www.googleapis.com/calendar/v3/calendars/' . $cal . '/events?pp=1&key=' . $APIKEY ;

  //$auth = json_decode($_SESSION['oauth_access_token'],true);
  //var_dump($auth);

  $session = curl_init($request);

  // Tell curl to use HTTP POST
  curl_setopt($session, CURLOPT_POST, true);
  // Tell curl that this is the body of the POST
  curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
  // Tell curl not to return headers, but do return the response
  curl_setopt($session, CURLOPT_HEADER, true);
  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($session, CURLOPT_VERBOSE, true);
  curl_setopt($session, CURLINFO_HEADER_OUT, true);
  curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type:  application/json', 'Authorization:  Bearer ' . $token, 'X-JavaScript-User-Agent:  Mount Pearl Tennis Club Bookings'));

  $response = curl_exec($session);

  //echo '<pre>';
  //var_dump(curl_getinfo($session, CURLINFO_HEADER_OUT));
  //echo '</pre>';

  curl_close($session);

  return $response;
}

function sendGetRequest($token, $request) {
  global $APIKEY;
  //$request = 'https://www.googleapis.com/calendar/v3/calendars/' . $CAL . '/events?pp=1&key=' . $APIKEY;
  //$auth = json_decode($_SESSION['oauth_access_token'],true);
  //var_dump($auth);



//  print_r($request);

  $session = curl_init($request);

  // Tell curl to use HTTP POST
  curl_setopt($session, CURLOPT_HTTPGET, true);
  // Tell curl not to return headers, but do return the response
  curl_setopt($session, CURLOPT_HEADER, false);
  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($session, CURLINFO_HEADER_OUT, false);
  curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization:  Bearer ' . $token));

  $response = curl_exec($session);

  //echo '<pre>';
  //var_dump(curl_getinfo($session, CURLINFO_HEADER_OUT));
  //print_r($response);
  //echo '</pre>';

  curl_close($session);
  return $response;
}
function createTimeWithTimezone($date,$time){
  $aika = strtotime($date . ' ' . $time);
  $paiva = date('c', $aika);
  return $paiva;
}

function createPostArgsJSON($date, $starttime, $endtime, $title) {
  $starttiaika = createTimeWithTimezone($date, $starttime);
  $endiaika = createTimeWithTimezone($date, $endtime);
  $arg_list = func_get_args();
  foreach ($arg_list as $key => $arg) {
    $arg_list[$key] = urlencode($arg);
  }
  $postargs = <<<JSON
{
 "start": {
  "dateTime": "{$starttiaika}"
 },
 "end": {
  "dateTime": "{$endiaika}"
 },
 "summary": "$title",
 "description": "$title"
}
JSON;
  return $postargs;
}

function getAccessToken() {
  $tokenURL = 'https://accounts.google.com/o/oauth2/token';
  $postData = array(
    'client_secret' => 'secret', #You need to fill these in to match your site.
    'grant_type' => 'refresh_token',
    'refresh_token' => 'token',
    'client_id' => 'your_id.apps.googleusercontent.com' #You need to fill these in to match your site.
  );
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $tokenURL);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $tokenReturn = curl_exec($ch);
  $token = json_decode($tokenReturn);
  //var_dump($tokenReturn);
  $accessToken = $token->access_token;
  return $accessToken;
}

function isTimeBooked($date, $starttime, $endtime, $cal) {
  global $APIKEY;
  $start = createTimeWithTimezone($date, $starttime);
  $end = createTimeWithTimezone($date, $endtime);
  $token = getAccessToken();
  $result = sendGetRequest($token, 'https://www.googleapis.com/calendar/v3/calendars/' . $cal . '/events?timeMax=' . urlencode($end) . '&timeMin=' . urlencode($start) . '&fields=items(end%2Cstart%2Csummary)&pp=1&key=' . urlencode($APIKEY));


  if (strlen($result) > 17) {

    return true;
  } else {
    echo"vapaa";
    return false;
  }
}


/*
 * End Core Calendar Functions (contents of the corecal.php file above)
 */

$thecal = 'court1';
if (isset($_GET['cal'])) {
  $thecal = addslashes($_GET['cal']);

}


$courts = array(
  'court1' => array('cid' => 'court1', 'name' => 'Court 1', 'id' => 'cal_id'),
  'court2' => array('cid' => 'court2', 'name' => 'Court 2', 'id' => 'cal_id'),
  'court3' => array('cid' => 'court3', 'name' => 'Court 3', 'id' => 'cal_id'),
  'court4' => array('cid' => 'court4', 'name' => 'Court 4', 'id' => 'cal_id'),
  'court5' => array('cid' => 'court5', 'name' => 'Court 5', 'id' => 'cal_id'),
  'court6' => array('cid' => 'court6', 'name' => 'Court 6', 'id' => 'cal_id'),
  'court7' => array('cid' => 'court7', 'name' => 'Court 7', 'id' => 'cal_id'),
);

$APIKEY = 'api_key';


$message = "";



  /*
   * Check to see if everything was filled out properly.
   */
  //echo 'start submit' . date('Hms', strtotime($_POST['starttime'] . ':00'));
  //echo 'start default' . date('Hms', strtotime($courts[$_POST['calendar']]['starttime']));
  //echo 'end submit' . date('Hms', strtotime($_POST['endtime'] . ':00'));
  //echo 'end default' . date('Hms', strtotime($courts[$_POST['calendar']]['endtime']));

  if (isTimeBooked($_POST['date'],$_POST['starttime'],$_POST['endtime'],$courts[$_POST['cal']]['id'])){
     echo('varattu');

   }
  else {

}

?>
