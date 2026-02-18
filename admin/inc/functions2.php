<?php
//set current session
$current_session = "2018/19";


//set raw time stamp
function get_long_time()
{
$time_notif1 = date("l");
$time_notif2 = date(" F j, Y;  g:i a ");
return $time_notif1.' ,'.$time_notif2;
}

//manage student basic information  including department and degree info
function get_extended_details($id_given)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `zmain_app` WHERE `user_id`='$id_given'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details) or die(mysqli_error($dbc));
	if($num_details>0)
	{
		while($row_person = mysqli_fetch_array($process_details))
		{
			$person_id = $row_person['id'];
			$sex = $row_person['sex'];
			$marital_status = $row_person['marital_status'];
			$date_of_birth = $row_person['date_of_birth'];
			$state_of_origin = $row_person['state_of_origin'];
			$local_govt_area = $row_person['local_govt_area'];
			$religion = $row_person['religion'];
			$faculty = $row_person['faculty'];
			$department = $row_person['department'];
			$degree = $row_person['degree'];
			$mode_of_study = $row_person['mode_of_study'];
			$field_of_interest = $row_person['field_of_interest'];
		}
		return array($person_id,$sex,$marital_status,$date_of_birth,$state_of_origin,$local_govt_area,$religion,$faculty,$department,$degree,$mode_of_study,$field_of_interest);
	}
	else
	{
		return array('false');
	}
}

function log_activity($person,$activity)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "INSERT INTO `fee_manager_trl` SET `admin` = '$person', `act` = '$activity'";
	$process_details = mysqli_query($dbc, $query_details);
}

function get_department_name($dept_id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_department = "SELECT * FROM `dept_new` WHERE `id` = '$dept_id'";
	$process_department_check = mysqli_query($dbc,$query_department);
	$num_process_department = mysqli_num_rows($process_department_check);
	if($num_process_department>0)
	{
		while($department_found = mysqli_fetch_array($process_department_check))
		{
			$department_name = $department_found['department'];
			return $department_name;
		}
	}
	else
	{
		if(!isnumeric($dept_id))
		{
			return $dept_id;
		}
		else
		{
			return 'Unclassified Department';
		}
	}
}

function convert_number($number)
{
    if (($number < 0) || ($number > 999999999))
    {
    throw new Exception("Number is out of range");
    }

    $Gn = floor($number / 1000000);  /* Millions (giga) */
    $number -= $Gn * 1000000;
    $kn = floor($number / 1000);     /* Thousands (kilo) */
    $number -= $kn * 1000;
    $Hn = floor($number / 100);      /* Hundreds (hecto) */
    $number -= $Hn * 100;
    $Dn = floor($number / 10);       /* Tens (deca) */
    $n = $number % 10;               /* Ones */

    $res = "";

    if ($Gn)
    {
        $res .= convert_number($Gn) . " Million";
    }

    if ($kn)
    {
        $res .= (empty($res) ? "" : " ") .
            convert_number($kn) . " Thousand";
    }

    if ($Hn)
    {
        $res .= (empty($res) ? "" : " ") .
            convert_number($Hn) . " Hundred";
    }

    $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
        "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
        "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
        "Nineteen");
    $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",
        "Seventy", "Eigthy", "Ninety");

    if ($Dn || $n)
    {
        if (!empty($res))
        {
            $res .= " and ";
        }

        if ($Dn < 2)
        {
            $res .= $ones[$Dn * 10 + $n];
        }
        else
        {
            $res .= $tens[$Dn];

            if ($n)
            {
                $res .= "-" . $ones[$n];
            }
        }
    }

    if (empty($res))
    {
        $res = "zero";
    }

    return $res;
}

//server information
$db_host='localhost';
$db_user='root';
$db_password='';
$database='ui';
$table = "";
$dbc = mysqli_connect("$db_host","$db_user","$db_password","$database")
or die ('Error connecting to Database');


function get_id_description($id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `new_fee` WHERE `fee_id`='$id'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details);
	if($num_details>0)
	{
		while($row_person = mysqli_fetch_array($process_details))
		{
			$description = $row_person['description'];
		}
		return $description;
	}
	else
	{
		if($id=="0")
		{
			return "Tuition";
		}
		else
		{
			return 0;
		}
	}
}

function get_mode_name($mode_id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_mode = "SELECT * FROM `mode_of_study` WHERE `id` = '$mode_id'";
	$process_mode_check = mysqli_query($dbc,$query_mode) or die(mysqli_error($dbc));
	$num_process_mode = mysqli_num_rows($process_mode_check);
	if($num_process_mode>0)
	{
		while($mode_found = mysqli_fetch_array($process_mode_check))
		{
			$mode_name = $mode_found['mode'];
			return $mode_name;
		}
	}
	else
	{
		return 0;
	}
}

function get_dept_name($id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `dept_new` WHERE `id`='$id'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details);
	if($num_details>0)
	{
		while($row_person = mysqli_fetch_array($process_details))
		{
			$description = $row_person['department'];
		}
		return $description;
	}
	else
	{
		if(!is_numeric($id))
		{
			return $id;
		}
		else
		{
			return 'Unclassified Department';
		}
	}
}

function get_degree_name($id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `degree_new` WHERE `id`='$id'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details);
	if($num_details>0)
	{
		while($row_person = mysqli_fetch_array($process_details))
		{
			$description = $row_person['degree'];
		}
		return $description;
	}
	else
	{
		if(!is_numeric($id))
		{
			return $id;
		}
		else
		{
			return 'Unclassified Degree';
		}
	}
}

function get_fac_name($id)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `fac_new` WHERE `id`='$id'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details);
	if($num_details>0)
	{
		while($row_person = mysqli_fetch_array($process_details))
		{
			$description = $row_person['faculty'];
		}
		return $description;
	}
	else
	{
		if(!is_numeric($id))
		{
			return $id;
		}
		else
		{
			return 'Unclassified Faculty';
		}
	}
}

function get_fee_deatil_id($invoice_number)
{
	global $db_host;
	global $db_user;
	global $db_password;
	global $database;
	global $dbc;
	$query_details = "SELECT * FROM `fee_detail2` WHERE `invoice_number`='$invoice_number'";
	$process_details = mysqli_query($dbc, $query_details);
	$num_details = mysqli_num_rows($process_details);
	if($num_details>0)
	{
		while($row_something = mysqli_fetch_array($process_details))
		{
			$fee_detail_id = $row_something['id'];
		}
		return $fee_detail_id;
	}
	else
	{
		return 0;
	}
}







?>
