<?php

  // load in mailchimp library
  include('./MailChimp.php');

  // namespace defined in MailChimp.php
  use \DrewM\MailChimp\MailChimp;

  // connect to mailchimp
  $MailChimp = new MailChimp('6005fc2b618d5c48d8c5119f81b1fc31-us2'); // put your API key here
  $list = 'b76a5d78ca'; // put your list ID here
  $email = $_GET['MERGE0']; // Get email address from form
  $id = md5(strtolower($email)); // Encrypt the email address
  // setup th merge fields
  $mergeFields = array(
    // *** YOUR FIELDS GO HERE ***
    'MERGE4' => $_GET['MERGE4'],
    'MERGE5' => $_GET['MERGE5'],
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