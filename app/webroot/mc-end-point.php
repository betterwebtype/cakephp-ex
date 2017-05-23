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

  function emailExistsMc($subscriberMail, $list_id){
    global $MailChimp;
    $subscriber_hash = $MailChimp->subscriberHash($subscriberMail);
    $result = $MailChimp->get("lists/$list_id/members/$subscriber_hash");
    print_r($result['status']);
    if($result['status'] == '404'){
      return false;
      echo "False";
    } else {
      return true;
      echo "True";
    }
  }

  emailExistsMc($email, $list);

  // // setup th merge fields
  // if (emailExistsMc) {
  //   // echo $_GET['callback'] . '("Not storing source")';
  //   // echo "Not storing source";
  //   $mergeFields = array(
  //     // *** YOUR FIELDS GO HERE ***
  //     'ATTEMPT' => $_GET['ATTEMPT'],
  //     'TOPSCORE' => $_GET['TOPSCORE'],
  //   );
  // } else {
  //   // echo $_GET['callback'] . '("Storing source")';
  //   // echo "Storing source.";
  //   $mergeFields = array(
  //     // *** YOUR FIELDS GO HERE ***
  //     'ATTEMPT' => $_GET['ATTEMPT'],
  //     'TOPSCORE' => $_GET['TOPSCORE'],
  //     'JOINED' => $_GET['JOINED'],
  //   );
  // }

  // // $mergeFields = array(
  // //   // *** YOUR FIELDS GO HERE ***
  // //   'ATTEMPT' => $_GET['ATTEMPT'],
  // //   'TOPSCORE' => $_GET['TOPSCORE'],
  // //   );

  // // remove empty merge fields
  // $mergeFields = array_filter($mergeFields);

  // $result = $MailChimp->put("lists/$list/members/$id", array(
  //                 'email_address'     => $email,
  //                 'status'            => 'subscribed',
  //                 'merge_fields'      => $mergeFields,
  //                 'update_existing'   => true, // YES, update old subscribers!
  //             ));
  // // echo json_encode($result);
  // echo $_GET['callback'] . '('.json_encode($result).')';