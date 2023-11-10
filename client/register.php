<?php
// Set the allowed origin (replace '*' with the actual origin if necessary)
header('Access-Control-Allow-Origin: *');

// Allow specific request methods (e.g., POST, GET, OPTIONS)
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

// Allow specific request headers (e.g., Authorization, Content-Type)
header('Access-Control-Allow-Headers: Authorization, Content-Type');


// Handle CORS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *'); // Adjust as needed
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Adjust as needed
    header('Access-Control-Allow-Headers: Content-Type'); // Include any additional headers here
    http_response_code(204); // No content in the response for preflight requests
    exit();
}

//We define two API functions for register and login. 
function register_member_API($FirstName,$LastName,$Email,$password,$con){
  
    // Hash the password securely
    $PasswordHash = password_hash($password, PASSWORD_DEFAULT);


    // Check if the email already exists in the database
    $stmt = $con->prepare("SELECT Email FROM members WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
         $response = array("message" => "no success","text"=>"Email already registered");
    } else {
        // Insert the new user data into the database using a prepared statement
        $stmt = $con->prepare("INSERT INTO members (FirstName, LastName, Email, PasswordHash) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $FirstName, $LastName, $Email, $PasswordHash);

        if ($stmt->execute()) {
            $MemberID = $stmt->insert_id; // Get the last inserted ID
            $response = array("message" => "success","MemberID" => $MemberID,"FirstName" => $FirstName,"text"=>"Register successfully.");
        } else {
            $response = array("message" => "error","text"=>"Fail to register.Please try again.");
          
        }
    }
    return $response;
}


function login_member_API($Email,$password,$con){

    // Check if the email  already exists in the database
    $stmt = $con->prepare("SELECT * FROM members WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $stmt->store_result();

     // Process the results
     if ($stmt->num_rows > 0 ) {
        $stmt->bind_result($MemberID, $MemberType, $FirstName, $LastName,$Email,$PasswordHash); // Add column names here
        $stmt->fetch();

        if (password_verify($password, $PasswordHash)){
            $response = array("message" => "success","MemberID" => $MemberID,"FirstName" => $FirstName, "MemberType" => $MemberType);
     } else {
        
            $response = array("message" => "no success", "text" =>'The password is not correct. Try again.');
     }

     } else {
    // Handle the case when no result set is available
            $response = array("message" => "no success", "text" => "The username is not correct. Try again.");
      }
   
      return  $response;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {

$data = json_decode(file_get_contents("php://input"));
// Connect to the database
include_once('conn.php');

if (isset($data->action)) {
    $action = $data->action;

    if ($action === 'register') {
        // Handle registration
        if (!empty($data->first_name) && !empty($data->last_name) && !empty($data->email) && !empty($data->password)) {
            $response=register_member_API($data->first_name,$data->last_name,$data->email,$data->password,$con);
        } else {
            $response = array("message" => "invalid input","text"=>"Invalid input.Please try again.");
        }
    } elseif($action === 'login') {
        // Handle login
        if (!empty($data->email) && !empty($data->password)) {
            $response=login_member_API($data->email,$data->password,$con); 
        }else {
           $response = array("message" => "invalid input","text"=>"Invalid input.Please try again.");
        }
    } else{
           $response = array("message" => "invalid input","text"=>"Invalid action.Please try again.");
    }
}
header("Content-Type: application/json");
echo json_encode($response);
mysqli_close($con);
}



?>



