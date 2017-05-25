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

// emailExistsMc($email, $list);
// sendCourseMc($email, $list);
// checkAttemptMc($email, $list);

  $emailExists = emailExistsMc($email, $list);
  $sendCourse = sendCourseMc($email, $list);
  $checkAttempt = checkAttemptMc($email, $list);
  $checkTopscore = checkTopscoreMc($email, $list);

  // setup th merge fields
  $mergeFields = array(
    'FNAME' => $_GET['FNAME'],
    'LNAME' => $_GET['LNAME'],
    'PLAYEDTRI' => $_GET['PLAYEDTRI'],
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

  $interests = array(
    'f498944bda' => $_GET['GROUPWEBDES'],
    '841aa1ddc6' => $_GET['GROUPWEBDEV'],
  );

  // print_r($mergeFields);

  // remove empty merge fields
  $mergeFields = array_filter($mergeFields);

  $result = $MailChimp->put("lists/$list/members/$id", array(
                  'email_address'     => $email,
                  'status'            => $status,
                  'merge_fields'      => $mergeFields,
                  'interests'         => $interests,
                  'update_existing'   => true, // YES, update old subscribers!
              ));
  // echo json_encode($result);
  echo $_GET['callback'] . '('.json_encode($result).')';