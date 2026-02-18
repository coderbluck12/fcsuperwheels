<?php
function validate_input($data){
$data = stripslashes($data);
$data = strip_tags($data);
$data = trim($data);
//$data = urlencode($data);
$clean = htmlspecialchars($data, ENT_IGNORE, 'utf-8');
return $clean;
}
$db_host='localhost';
//$db_user='pentago9_ayodeji';
$db_user='tertgxyp_seyi';
//$db_password='superwheel123';
$db_password='Fcnest001@';
//$database='pentago9_superwheel';
$database='tertgxyp_fcsuperwheels';
$table = "";
try {
  // Connect ONCE inside a try/catch block
  $pdo = new PDO("mysql:host=$db_host;dbname=$database;charset=utf8", $db_user, $db_password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  
  // Maintain compatibility with your legacy code that uses $dbc
  $dbc = $pdo; 
} catch (PDOException $e) {
  // If connection fails, show the error so you don't get a blank page
  die("Database Connection Error: " . $e->getMessage());
}

// Function to generate a unique numeric identifier
function generateUniqueNumeric() {
    // Generate a unique number based on timestamp and random number
    $unique_number = time() . mt_rand(10000, 99999);
    
    // Generate a hash of the unique number
    $hashed_number = sha1($unique_number);
    
    // Extract a portion of the hash
    $numeric_identifier = substr($hashed_number, 0, 4); // Take the first 4 characters
    
    // Convert hexadecimal to decimal
    $decimal_number = hexdec($numeric_identifier);
    
    // Ensure the result is within 4 digits
    $four_digit_number = $decimal_number % 10000;
    
    return $four_digit_number;
}


// Encrypt function
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    return base64_encode($iv . openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv));
}

// Decrypt function
function decryptData($data, $key) {
    $data = base64_decode($data);
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted_data = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}


function redirect_to($link)
{
	header('Location:'.$link.'');
}

function mail_user($user,$name,$subject,$message){

//refer user to email
	$to  = $user;

// subject
$subject = $subject;

// message
$msg = '<html>
<body paddingwidth="0" paddingheight="0" bgcolor="#d1d3d4"  style="padding-top: 0; padding-bottom: 0; padding-top: 0; padding-bottom: 0; background-repeat: repeat; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; -webkit-font-smoothing: antialiased;" offset="0" toppadding="0" leftpadding="0">
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tableContent" align="center" bgcolor="#EAEAEA" style="font-family:helvetica, sans-serif;" class="bgBody">
    <!-- ================ header=============== -->
    <tr><td height="20" bgcolor="#EAEAEA"></td></tr>
    <tr>

      <td align="center">

        <table width="600" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
          <tr><td height="45"></td></tr>
          
            <!-- ================ END header =============== -->
            <tr>
              <td>
                <table width="600" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td class="movableContentContainer" align="center"> 
                      <!--  =========================== The body ===========================  -->
                      
                      <div class="movableContent">
                        <table width="540" border="0" cellspacing="0" cellpadding="0">
                          <tr>
                            <td>
                              <table width="600" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                  <td width="20"></td>
                                  <td align="left">
                                    <div class="contentEditableContainer contentImageEditable">
                                      <div class="contentEditable" >
                                        <img src="images/logo.png" alt="texas teacherslounge">
                                      </div>
                                    </div>
                                  </td>
                                </tr>
                              </table>
                            </td>
							 <tr><td height="37"></td></tr>
                          <tr><td><div style="border:3px solid #1b1e6d"></div></td></tr>
                          </tr>
                        </table>
                      </div>

                     

                      <div class="movableContent">
                        <table width="540" border="0" cellspacing="0" cellpadding="0">
                          <tr><td height="38"></td></tr>
                         
                                                 
                          <tr><td height="12"></td></tr>
                          <tr>
                            <td>
                              <div class="contentEditableContainer contentTextEditable">
                                <div class="contentEditable" >
                                  <p style="color:#555555;font-size:14px;line-height:22px;"><p>Hello '.$name.' <br/><br /><br/>
                                    '.$message.'

</p><br/><br/><br/>
  Please <a href="http://fcsuperwheels.com">contact us</a> if you have further questions
  <p>
  Firstchoice Superwheels Team<br/>
  Warm regards<br/>
  
  <br/>
                                  </p>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <tr><td height="37"></td></tr>
                          <tr><td><div style="border:3px solid #1b1e6d"></div></td></tr>
                        </table>
                      </div>




                  <div class="movableContent">
                    <table width="600" border="0" cellspacing="0" cellpadding="0" class="bgItem">
                      <tr>
                        <td height="180"  align="center">
                          <table width="540" border="0" cellspacing="0" cellpadding="0">
                            <tr><td height="55"></td></tr>
                            <tr>
                              <td align="center">
                                <div class="contentEditableContainer contentTextEditable">
                                  <div class="contentEditable" >
                                    <p style="font-size:13px;color:#d42121;font-weight:bold;">Sent by Firstchoice Superwheels Technical Team</p>
                                  </div>
                                </div>

                              </td>
                            </tr>
                            <tr><td height="10"></td></tr>
                            <tr>
                              <td align="center" style="font-size:13px;color:#d42121;">
                                <div class="contentEditableContainer contentTextEditable">
                                  <div class="contentEditable" >
                                    <p><a target="_blank" href="http://fcsuperwheels.com" class="link1" style="color:#d42121;">Home</a></p>
                                  </div>
                                </div>

                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </div>

                </td>
              </tr>

              

            </table>
          </td>
        </tr>

      </table>

    </td>
    </tr>
    <!-- end footer-->

  </table>

  </body>
  </html>
';

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'From: Texas Teachers Lounge' . "\r\n";


// Mail it

$send_am = mail($to, $subject, $msg, $headers);
if($send_am){
return 1;
}else{
return 0;
}
}

function numberToWords($num) {
    // first, normalize to an integer (drops any fractional part)
    $num = (int) round($num);

    $ones = [
        0 => '',    1 => 'One',       2 => 'Two',      3 => 'Three',
        4 => 'Four',5 => 'Five',      6 => 'Six',      7 => 'Seven',
        8 => 'Eight',9 => 'Nine',     10 => 'Ten',    11 => 'Eleven',
        12 => 'Twelve',13 => 'Thirteen',14 => 'Fourteen',15 => 'Fifteen',
        16 => 'Sixteen',17 => 'Seventeen',18 => 'Eighteen',19 => 'Nineteen',
        20 => 'Twenty',30 => 'Thirty',40 => 'Forty',   50 => 'Fifty',
        60 => 'Sixty',70 => 'Seventy',80 => 'Eighty',  90 => 'Ninety'
    ];

    $thousands = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];

    if ($num === 0) {
        return 'Zero';
    }

    $words = '';
    $group  = 0;

    while ($num > 0) {
        $chunk = $num % 1000;
        if ($chunk) {
            $words = convertHundreds($chunk, $ones)
                   . ($thousands[$group] ? ' '.$thousands[$group] : '')
                   . ($words ? ' '.$words : '');
        }
        $num   = intdiv($num, 1000);
        $group++;
    }

    return trim($words);
}

function convertHundreds($num, array &$ones) {
    // ensure integer
    $num = (int) $num;

    if ($num < 20) {
        return $ones[$num];
    }

    if ($num < 100) {
        $tens = intdiv($num, 10) * 10;
        $unit = $num % 10;
        return $ones[$tens] . ($unit ? ' '.$ones[$unit] : '');
    }

    // hundreds
    $h     = intdiv($num, 100);
    $rest  = $num % 100;
    $str   = $ones[$h] . ' Hundred';
    if ($rest) {
        $str .= ' ' . convertHundreds($rest, $ones);
    }
    return $str;
}

?>