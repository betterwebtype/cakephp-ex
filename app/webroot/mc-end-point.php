<?php

  header("Access-Control-Allow-Origin: *");
  // load in mailchimp library
  include('./MailChimp.php');

  // namespace defined in MailChimp.php
  use \DrewM\MailChimp\MailChimp;

  // connect to mailchimp
  $MailChimp = new MailChimp('6005fc2b618d5c48d8c5119f81b1fc31-us2'); // put your API key here
  $list = 'b76a5d78ca'; // put your list ID here
  $email = $_GET['EMAIL']; // Get email address from form
  $id = md5(strtolower($email)); // Encrypt the email address

  $status;
  $attempt;
  $topscore;
  $interests;
  $interestId;

  function emailExistsMc($subscriberMail, $list_id){
    global $MailChimp;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    // print_r($result['status']);
    // print_r($result);
    if($result['status'] == '404'){
      return false;
    } else {
      return true;
    }
  }

  function sendCourseMc($subscriberMail, $list_id){
    global $MailChimp;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    // print_r($result['status']);
    // print_r($result);
    if($result['merge_fields']['SENDCOURSE'] != 'Yes'){
      return false;
    } else {
      return true;
    }
  }

  function checkAttemptMc($subscriberMail, $list_id){
    global $MailChimp;
    global $attempt;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    // print_r($result['status']);
    // print_r($result);
    if($result['merge_fields']['ATTEMPT'] < 1){
      return false;
    } else {
      $attempt = $result['merge_fields']['ATTEMPT'];
      return true;
    }
  }

  function checkTopscoreMc($subscriberMail, $list_id){
    global $MailChimp;
    global $topscore;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    // print_r($result['status']);
    // print_r($result);
    if($result['merge_fields']['TOPSCORE'] < 1){
      return false;
    } else {
      $topscore = $result['merge_fields']['TOPSCORE'];
      return true;
    }
  }

  function checkInterestsMc($subscriberMail, $list_id){
    global $MailChimp;
    global $interestId;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    // print_r($result['status']);
    // print_r($result);
    if($result['interests']['f498944bda'] == false && $result['interests']['841aa1ddc6'] == false){
      return false;
    } else if ( $result['interests']['f498944bda'] == true ){
      $interestId = 'f498944bda';
      return true;
    } else if ( $result['interests']['841aa1ddc6'] == true ) {
      $interestId = '841aa1ddc6';
      return true;
    }
  }

// emailExistsMc($email, $list);
// sendCourseMc($email, $list);
// checkAttemptMc($email, $list);

  $emailExists = emailExistsMc($email, $list);
  $sendCourse = sendCourseMc($email, $list);
  $checkAttempt = checkAttemptMc($email, $list);
  $checkTopscore = checkTopscoreMc($email, $list);
  $checkInterests = checkInterestsMc($email, $list);
  // print_r($checkInterests);

  // setup th merge fields
  $mergeFields = array(
    'FNAME' => $_GET['FNAME'],
    'LNAME' => $_GET['LNAME'],
    'PLAYEDTRI' => $_GET['PLAYEDTRI'],
    'SAMPLE' => $_GET['SAMPLE'],
  );

  if ($emailExists == false) {
    $status = 'pending';
    $mergeFields['ATTEMPT'] = $_GET['ATTEMPT'];
    $mergeFields['TOPSCORE'] = $_GET['TOPSCORE'];
    // $mergeFields['SENDCOURSE'] = $_GET['SENDCOURSE'];
    $mergeFields['JOINED'] = $_GET['JOINED'];
  } else {
    $status = 'subscribed';
  }

  if ($sendCourse == false) {
    $mergeFields['SENDCOURSE'] = $_GET['SENDCOURSE'];
  }

  if ($checkAttempt == false) {
    $mergeFields['ATTEMPT'] = $_GET['ATTEMPT'];
  } else {
    $mergeFields['ATTEMPT'] = $attempt + $_GET['ATTEMPT'];
  }

  if ($checkTopscore == false) {
    $mergeFields['TOPSCORE'] = $_GET['TOPSCORE'];
  } else {
    if ($_GET['TOPSCORE'] > $topscore){
      $mergeFields['TOPSCORE'] = $_GET['TOPSCORE'];
    }
  }

  $interestValue = $_GET['INTEREST'];
  // print_r($interestValue);
  // if (empty($interestValue)){
  //   echo "Yes the variable is empty";
  // }

  $interests = array(
    'f498944bda' => false,
    '841aa1ddc6' => false,
  );

  if ($checkInterests == true) {
    $interests[$interestId] = true;
  } elseif ($checkInterests == false) {
    if ($interestValue == 1){
      $interests['f498944bda'] = true;
    } elseif ($interestValue == 2) {
      $interests['841aa1ddc6'] = true;
    }
  }

  // print_r($interests);

  // remove empty merge fields
  $mergeFields = array_filter($mergeFields);
  // $interests = array_filter($interests);

  $mcInfo = array(
                  'email_address'     => $email,
                  'status'            => $status,
                  'merge_fields'      => $mergeFields,
                  'update_existing'   => true, // YES, update old subscribers!
              );
  if ($interestValue == 1 || $interestValue == 2){
    $mcInfo['interests'] = $interests;
  }

  // print_r($mcInfo);

  $result = $MailChimp->put("lists/$list/members/$id", $mcInfo);
  // echo json_encode($result);
  echo $_GET['callback'] . '('.json_encode($result).')';