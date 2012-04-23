<?php
//----------------------------------------------------------------------
// Copyright (c) 2011 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------
?>
<?php
/*
Generate an RSA keypair with a 1024 bit private key
Execute command: “openssl genrsa -out private_key.pem 1024”
e.g.
$ openssl genrsa -out private_key.pem 1024
Generating RSA private key, 1024 bit long modulus
.............................++++++
................................................................++++++
e is 65537 (0x10001)
[edit]Extracting the public key from an RSA keypair
Execute command: "openssl rsa -pubout -in private_key.pem -out public_key.pem"
e.g.
$ openssl rsa -pubout -in private_key.pem -out public_key.pem
writing RSA key
A new file is created, public_key.pem, with the public key.

 */

//------------------------------------------------------------
// Load the user
//------------------------------------------------------------
require_once("settings.php");
require_once("user.php");
require_once("header.php");
$user = geni_loadUser();
if (!isset($user) || is_null($user) || ! $user->isActive()) {
  relative_redirect('home.php');
}

//------------------------------------------------------------
// If user has a key, redirect to home page
//------------------------------------------------------------
$key = db_fetch_public_key($user->account_id);
if ($key) {
  relative_redirect("home.php");
}

$error = NULL;
if (array_key_exists('file', $_FILES)) {
  $errorcode = $_FILES['file']['error'];
  if ($errorcode != 0) {
    // An error occurred with the upload.
    if ($errorcode == UPLOAD_ERR_NO_FILE) {
      $error = "No file was uploaded.";
    } else {
      $error = "Unknown upload error (code = $errorcode).";
    }
  } else {
    // A file was uploaded. Make sure it is an SSL key.
    $cmd_array = array('/usr/bin/openssl',
                       'rsa',
                       '-pubin',
                       '-in',
                       $_FILES["file"]["tmp_name"],
                       );
    $command = implode(" ", $cmd_array);
    $result = exec($command, $output, $status);
    //error_log("Ran openssl on uploaded cert with command: $command");
    if ($status != 0) {
      $fname = $_FILES['file']['name'];
      $error = "File $fname is not a valid SSL public key.";
    } else {
      //error_log("no error");
    }
  }
}

//----------------------------------------------------------------------
// Check for valid ssl public key:
//
//  /usr/bin/openssl rsa -pubin -in public_key.pem
//
//  /usr/bin/openssl rsa -pubin -in $_FILES['file']['tmp_name']
//----------------------------------------------------------------------

if ($error != NULL || count($_POST) == 0) {
  // Display the form and exit
  $GENI_TITLE = "Upload public key";
  show_header('GENI Portal: Profile', $TAB_PROFILE, 0); // 0=Don't load user to show header
  include("tool-breadcrumbs.php");
  if ($error != NULL) {
    echo "<div id=\"error-message\""
      . " style=\"background: #dddddd;font-weight: bold\">\n";
    echo "$error";
    echo "</div>\n";
  }
  include('uploadkey.html');
  include("footer.php");
  exit;
}

// The public key is in $_FILES["file"]["tmp_name"]
$contents = file_get_contents($_FILES["file"]["tmp_name"]);
$user = geni_loadUser();
$description = NULL;
/* echo "<h1>GET</h1>"; */
/* var_dump($_GET); */
/* echo "<h1>POST</h1>"; */
/* var_dump($_POST); */
if (array_key_exists("description", $_POST)) {
  /* echo "Description exists in POST<br/>"; */
  $description = $_POST["description"];
}
/* echo "Passing description: $description<br/>"; */
$res = db_add_public_key($user->account_id, $contents,
                  $_FILES["file"]["name"], $description);
//error_log("db_add_pub had result $res");

//------------------------------------------------------------
// Generate the certificate
//------------------------------------------------------------

// Run gen-certs.py and return it as the content.
$cmd_array = array($portal_gcf_dir . '/src/gen-certs.py',
                   '-c',
                   $portal_gcf_cfg_dir . '/gcf.ini',
                   '--notAll',
                   '-d',
                   '/tmp',
                   '-u',
                   $user->username,
                   '--pubkey',
                   $_FILES["file"]["tmp_name"],
                   '--exp',
                   '--email',
                   $user->email()
                   );
$command = implode(" ", $cmd_array);
$result = exec($command, $output, $status);
/* print_r($output); */
// The cert is on disk, read the file and store it in the db.
$cert_file = '/tmp/' . $user->username . "-cert.pem";
$contents = file_get_contents($cert_file);
$res = db_add_key_cert($user->account_id, $contents);
//error_log("db_add_key had res $res");

// Delete the cert file
unlink($cert_file);
//error_log("doing redirect home at the bottom of the page");

// FIXME: We redirect here - so that text at the bottom isn't shown?
// Do we want a page that shows the result?!

relative_redirect('profile');
?>
Your key was uploaded.<br/>
<a href="profile.php">My Profile page</a>
