<?php
//We define some functions in the files. 
//We can access the attributes sent from JavaScript in the $_POST superglobal array
// because we used the POST method in the fetch request.

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {


$data = json_decode(file_get_contents("php://input"));
if (!empty($data->email) && !empty($data->password)) {
                $Email = $data->email;
                $pass = $data->password;

	            $msg='';
	            $flag=0;
        
				session_start();
				include_once('conn.php');

                // Check if the email  already exists in the database
                $stmt = $con->prepare("SELECT * FROM members WHERE Email = ?");
                $stmt->bind_param("s", $Email);
                $stmt->execute();
                $stmt->store_result();

                 // Process the results
                 if ($stmt->num_rows > 0 ) {
					$stmt->bind_result($MemberID, $MemberType, $FirstName, $LastName,$Email,$PasswordHash); // Add column names here
					$stmt->fetch();

					if (password_verify($pass, $PasswordHash)){
                        $response = array("message" => "success","FirstName" => $FirstName, "MemberType" => $MemberType);
                 } else {
                    
                        $response = array("message" => "no success", "text" =>'The password is not correct. Try again.');
                 }

                 } else {
                // Handle the case when no result set is available
                        $response = array("message" => "no success", "text" => "The username is not correct. Try again.");
                  }
				} else {
			   $response = array("message" => "no success","text"=>"Invalid input.Please try again.");
		         }
		   header("Content-Type: application/json");
		   echo json_encode($response);
		   mysqli_close($con);
		   }
					

?>
