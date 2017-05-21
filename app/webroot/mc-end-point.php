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
  // setup th merge fields
  $mergeFields = array(
    // *** YOUR FIELDS GO HERE ***
    'ATTEMPT' => $_GET['ATTEMPT'],
    'TOPSCORE' => $_GET['TOPSCORE'],
    );

  // remove empty merge fields
  $mergeFields = array_filter($mergeFields);

  $result = $MailChimp->put("lists/$list/members/$id", array(
                  'email_address'     => $email,
                  'status'            => 'subscribed',
                  'merge_fields'      => $mergeFields,
                  'update_existing'   => true, // YES, update old subscribers!
              ));
  echo json_encode($result);