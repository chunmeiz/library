<?php
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

 function fetch_member_API($MemberID,$con){
    $stmt = $con->prepare("SELECT * FROM members WHERE MemberID = ?");
    $stmt->bind_param("s", $MemberID);
    $stmt->execute();
    $stmt->store_result();

     if ($stmt->num_rows > 0 ) {
        $stmt->bind_result($MemberID, $MemberType, $FirstName, $LastName,$Email,$PasswordHash); 
        $stmt->fetch();
        $response = array("message" => "success","text" =>"You can modify your personal information.","FirstName" => $FirstName,"LastName" => $LastName,"Email" => $Email,"PasswordHash" => $PasswordHash);  
       
    }else {         
        $response = array("message" => "no success", "text" => "The user profile can't be fetched. Try again.");
          }
        return  $response;
}


 function update_member_API($FirstName,$LastName,$Email,$password,$PasswordHash,$MemberID,$con,$orgEmail){
    // Hash the password securely
    if($password){
        $PasswordHash = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Check if the email already exists in the database
    $stmt = $con->prepare("SELECT Email FROM members WHERE Email = ?");
    $stmt->bind_param("s", $Email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0 && $Email !== $orgEmail) {
         $response = array("message" => "no success","text"=>"Email has already been registered");
    } else {
        
        // Insert the new user data into the database using a prepared statement
        $stmt = $con->prepare("UPDATE members SET FirstName = ?, LastName = ?, Email = ?, PasswordHash = ? WHERE MemberID = ?");
        $stmt->bind_param("ssssi", $FirstName, $LastName, $Email, $PasswordHash,$MemberID);

        if ($stmt->execute()) {
            
            $response = array("message" => "success","text" => "Update your profile successfully.","FirstName" => $FirstName);
        } else {
            $response = array("message" => "error","text" => "Fail to update your profile.Please try again.");
          
        }
    }
    return $response;
 }


if ($_SERVER['REQUEST_METHOD'] === 'GET' ) {
    // Connect to the database
    include_once('conn.php');

    $MemberID = $_GET['param1'];
    $response=fetch_member_API($MemberID,$con);
    header('Content-Type: application/json');
    echo json_encode($response);
    mysqli_close($con);  
} 

if ($_SERVER['REQUEST_METHOD'] === 'PUT' ) {
    // Connect to the database
    include_once('conn.php');
    
    $data = json_decode(file_get_contents("php://input"));
  
    if (isset($data->action)) {
        $action = $data->action;
        
        if ($action === 'update') {
           $response=update_member_API($data->first_name,$data->last_name,$data->Email,$data->password,$data->PasswordHash,$data->MemberID,$con,$data->orgEmail);
        }
    }
    
header('Content-Type: application/json');
echo json_encode($response);
mysqli_close($con);  
} 

?>