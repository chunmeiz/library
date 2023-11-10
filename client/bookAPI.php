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
function fetch_books_API($BookID,$con){
    $stmt = $con->prepare("SELECT * FROM books WHERE BookID = ?");
    $stmt->bind_param("s", $BookID);
    $stmt->execute();
    $stmt->store_result();
    $bookArray = array();

     if ($stmt->num_rows > 0 ) {
        $stmt->bind_result($BookID,$Title, $Author, $Publisher, $Language, $Category, $PhotoName); 
        $stmt->fetch();
        $bookArray = array(
            'BookID' => $BookID,
            'Title' => $Title,
            'Author' => $Author,
            'Publisher' => $Publisher,
            'Language' => $Language,
            'Category' => $Category,
            'PhotoName' => $PhotoName
        );
        return $bookArray;
    }
}

 function fetch_bookstatus_API($MemberID,$con){
    $stmt = $con->prepare("SELECT * FROM bookstatus WHERE MemberID = ?");
    $stmt->bind_param("s", $MemberID);
    $stmt->execute();
    $stmt->store_result();
    $bookStatusArray = array();
    $booksArray = array();
 
     if ($stmt->num_rows > 0 ) {
        $stmt->bind_result($ID,$BookID,$MemberID, $Status, $AppliedDate, $ReturnDate); 
        // Loop through the rows and fetch each one
        while ($stmt->fetch()) {
            $ChangedAppliedDate = strtotime($ReturnDate);
            $nowDate = time();
            $overDue = floor(($nowDate-$ChangedAppliedDate)/(24*60*60));
            if ($overDue <= 0) {
                $overDue = 0;
            }

            $bookStatus = array(
                'BookID' => $BookID,
                'MemberID' => $MemberID,
                'Status' => $Status,
                'AppliedDate' => $AppliedDate,
                'ReturnDate' => $ReturnDate,
                'overDue' => $overDue
            );
            $bookStatusArray[] = $bookStatus;
            $booksArray[] = fetch_books_API($BookID,$con);
        }
            
            $response = array(
                "message" => "success", 
                "text" => "You have borrowed " . count($bookStatusArray) . " books.",
                "bookStatusArray" => $bookStatusArray,
                "booksArray" => $booksArray
            );
            return $response;
    } else {
        $response = array("message" => "no success", "text" => "You have not borrowed any book.");
        return $response;
    }
}

function fetch_allbookstatus_API($con){
    $stmt = $con->prepare("SELECT * FROM bookstatus");
    $stmt->execute();
    $stmt->store_result();
    $bookStatusArray = array();
    $booksArray = array();
 
     if ($stmt->num_rows > 0 ) {
        $stmt->bind_result($ID,$BookID,$MemberID, $Status, $AppliedDate, $ReturnDate); 
        // Loop through the rows and fetch each one
        while ($stmt->fetch()) {
            $bookStatus = array(
                'BookID' => $BookID,
                'MemberID' => $MemberID,
                'Status' => $Status,
            );
            $bookStatusArray[] = $bookStatus;
            $booksArray[] = fetch_books_API($BookID,$con);
        }
            
            $response = array(
                "message" => "success", 
                "text" => "Admin can help our members to borrow and return books.",
                "bookStatusArray" => $bookStatusArray,
                "booksArray" => $booksArray
            );
            return $response;
    } else {
        $response = array("message" => "no success", "text" => "There are no book records.");
        return $response;
    }
}


 function update_bookstatus_API($BookID,$Status,$MemberID,$con){
        // Check if MemberID exists in the members table
        $checkMemberQuery = $con->prepare("SELECT MemberID FROM members WHERE MemberID = ?");
        $checkMemberQuery->bind_param("s", $MemberID);
        $checkMemberQuery->execute();
        $checkMemberQuery->store_result();

  if ($checkMemberQuery->num_rows > 0) {
     // MemberID exists in the members table 
    if ($Status === 'Available'){
        $Status ='Onloan';
        $AppliedDate = date("Y-m-d",time());
        $ReturnDate = date("Y-m-d",time()+24*60*60*21);
        $stmt = $con->prepare("UPDATE bookstatus SET MemberID = ?, Status = ?, AppliedDate = ?,ReturnDate = ?  WHERE BookID = ?");
        $stmt->bind_param("ssssi", $MemberID, $Status, $AppliedDate,$ReturnDate, $BookID);

        if ($stmt->execute()) {
            $response = array("message" => "success","text" => "Borrow successfully." ,"bookStatus" => $Status, "MemberID" => $MemberID);
        } else {
            $response = array("message" => "error","text"=>"Fail to borrow.Please try again.");
        }
    }else if($Status === 'Onloan'){
        $Status ='Available';
        $MemberID = Null;
        $AppliedDate = '';
        $ReturnDate = '';
       
        $stmt = $con->prepare("UPDATE bookstatus SET MemberID = ?, Status = ?, AppliedDate = ?,ReturnDate = ?  WHERE BookID = ?");
        $stmt->bind_param("ssssi", $MemberID, $Status, $AppliedDate,$ReturnDate, $BookID);

        if ($stmt->execute()) {
            $response = array("message" => "success","text"=>"Return successfully.","bookStatus" => $Status);
        } else {
            $response = array("message" => "error","text"=>"Fail to return.Please try again.");
        }
      }else{
            $response = array("message" => "error","text"=>"The book status is wrong.Please try again.");
        }
   } else {
            $response = array("message" => "error", "text" => "MemberID does not exist.Please try again.");
    } 
    return  $response;
 }

function update_book_API($BookID,$Title,$Author,$Publisher,$Language,$Category,$con){
    if($BookID){
        //update a book record
       $stmt = $con->prepare("UPDATE books SET Title = ?, Author = ?, Publisher = ?, Language = ?, Category = ?  WHERE BookID = ?");
       $stmt->bind_param("sssssi", $Title, $Author,$Publisher,$Language, $Category, $BookID);
    
        if ($stmt->execute()) {
            $response = array("message" => "success","text"=>"Update successfully.", "BookID" => $BookID);
        } else {
            $response = array("message" => "error","text"=>"Fail to update.Please try again.");
         }
        }else{
            // Insert a new book record
            $stmt = $con->prepare("INSERT INTO books (Title, Author, Publisher, Language, Category ) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $Title, $Author, $Publisher, $Language, $Category);

            if ($stmt->execute()) {
                $BookID = mysqli_insert_id($con); // Get the auto-generated BookID

                // Insert a corresponding bookstatus record with the same BookID
                $stmt = $con->prepare("INSERT INTO bookstatus (BookID, MemberID, Status,AppliedDate, ReturnDate) VALUES (?, NULL, 'Available','','')");
                $stmt->bind_param("i", $BookID);

                if ($stmt->execute()) {
                    $response = array("message" => "success", "text" => "New book and bookstatus inserted successfully.", "BookID" => $BookID);
                } else {
                    $response = array("message" => "error", "text" => "Fail to insert bookstatus. Please try again.");
                }
            }else {
                $response = array("message" => "error", "text" => "Fail to insert a new book. Please try again.");
            }

        }
    return $response;
}



function update_book_Url($BookID,$bookPhotoUrl,$con){
    if($BookID){
        //update a book photo Url
       $stmt = $con->prepare("UPDATE books SET PhotoName = ? WHERE BookID = ?");
       $stmt->bind_param("si", $bookPhotoUrl, $BookID);
    
        if ($stmt->execute()) {
            $response = array("message" => "success","text"=>"Add the book successfully.");
        } else {
            $response = array("message" => "error","text"=>"Fail to add the book.Please try again.");
         }
        }else{
            $response = array("message" => "error","text"=>"Fail to add the book.Please try again.");
        }
    
    return $response;
    }



function delete_book_API($BookID,$bookStatus,$con){
    if($bookStatus === "Available"){
       $stmt1 = $con->prepare("DELETE FROM books where BookID = ?");
       $stmt1->bind_param("s", $BookID);
       $stmt2 = $con->prepare("DELETE FROM bookstatus where BookID = ?");
       $stmt2->bind_param("s", $BookID);
    
       if ($stmt1->execute() && $stmt2->execute()) {
           $response = array("message" => "success","text"=>"Delete successfully.");
       } else {
           $response = array("message" => "error","text"=>"Fail to delete.Please try again.");
       }
    }else{
        $response = array("message" => "error","text"=>"The book is on loan. You cannot delete the book.");
       
    }

    return $response;
}


function searchBooks($searchTerm, $con) {
    // Define your SQL query with a WHERE clause to search for books
    $query = "SELECT * FROM books WHERE Title LIKE ? OR Author LIKE ? OR Publisher LIKE ? OR Language LIKE ? OR Language LIKE ?";
    $searchTerm = "%" . $searchTerm . "%"; // Add wildcard (%) to search for partial matches

    // Prepare and execute the SQL statement
    $stmt = $con->prepare($query);
    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();

    $result = $stmt->get_result();

    // Initialize arrays to store books and book statuses
    $booksArray = array();
    $bookStatusArray = array();
    
    if ($result){
         while ($row = $result->fetch_assoc()) {
             $booksArray[] = $row;
             $BookID_fromBooks = $row["BookID"];
             
             $stmtStatus = $con->prepare("SELECT * FROM bookstatus WHERE BookID = ?");
             $stmtStatus->bind_param("s", $BookID_fromBooks);
             $stmtStatus->execute();
             $stmtStatus->store_result();
             
             if ($stmtStatus->num_rows > 0 ) {
                $stmtStatus->bind_result($ID,$BookID,$MemberID, $Status, $AppliedDate, $ReturnDate); 
                while ($stmtStatus->fetch()) {
                    $bookStatus = array(
                        'BookID' => $BookID,
                        'MemberID' => $MemberID,
                        'Status' => $Status,
                        'AppliedDate' => $AppliedDate,
                        'ReturnDate' => $ReturnDate
                    );
                    $bookStatusArray[] = $bookStatus;
                   }
                }
            }

        //base on the search result to give response.Take these code from above ,in order to avoid to overwrite the $response variable 
            if (!empty($booksArray)) {
              $response = array("message" => "success","text"=>"Find " . count($booksArray) . " books.","books" => $booksArray, "bookStatus" => $bookStatusArray);
            }else{
                $response = array("message" => "error","text"=>"No book status found.");
               }
         }else{
           $response = array("message" => "error","text"=>"No book found.");
           }  
    return $response;
    }




//HTTP requests entry

if ($_SERVER['REQUEST_METHOD'] === 'GET'){
     
    if(empty($_GET)){
        include_once('conn.php');
        $response=fetch_allbookstatus_API($con);
        header('Content-Type: application/json');
        echo json_encode($response);
        mysqli_close($con);

    }else{
         // Connect to the database
          include_once('conn.php');
          $action = $_GET['param1'];

          if ($action === "listBorrowedBooks"){
              $MemberID = $_GET['param2'];
              $response=fetch_bookstatus_API($MemberID,$con);
          }else if ($action === "searchBooks"){
              $searchTerm = $_GET['param2'];
              $response = searchBooks($searchTerm, $con);
          }
          header('Content-Type: application/json');
          echo json_encode($response);
          mysqli_close($con); 

    }
}  


if ($_SERVER['REQUEST_METHOD'] === 'PUT' ) {
    // Connect to the database
    include_once('conn.php');
    
    $data = json_decode(file_get_contents("php://input"));
  
    if (isset($data->action)) {
        $action = $data->action;
        
        if ($action === 'updateBookStatus') {
           $response=update_bookstatus_API($data->BookID,$data->Status,$data->MemberID,$con);
    }else if ($action === 'updateBook') {
            $response=update_book_API($data->BookID,$data->Title,$data->Author,$data->Publisher,$data->Language,$data->Category,$con);
          }else if ($action === 'updateBookUrl'){
             $response=update_book_Url($data->BookID,$data->bookPhotoUrl,$con);
          }
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    mysqli_close($con); 
}



 //Upload a photo file to server
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Connect to the database
     include_once('conn.php');
     
    // Check if a file was uploaded
    if (isset($_FILES["choosefile"])) {
        $file = $_FILES["choosefile"];

        // Check for errors during file upload
        if ($file["error"] === UPLOAD_ERR_OK) {
            // Define the directory where you want to save the uploaded files
            $uploadDirectory = "images/"; // Create this directory if it doesn't exist

            // Generate a unique name for the uploaded file to avoid overwriting
            $uniqueFilename = uniqid() . "_" . $file["name"];
            

            // Define the full path to save the file
            $filePath = $uploadDirectory . $uniqueFilename;

            // Move the uploaded file to the specified directory
            if (move_uploaded_file($file["tmp_name"], $filePath)) {
                // File upload successful
                $BookID=$_POST["BookID"];
                if($BookID){
                  // Update the existing record
                   $stmt = $con->prepare("UPDATE books SET PhotoName = ?  WHERE BookID = ?");
                   $stmt->bind_param("si", $filePath, $BookID);
                
                   if ($stmt->execute()) {
                       $response = array("message" => "success","text"=>"Photo update successfully.","PhotoName" => $filePath, "BookID" => $BookID);
                   } else {
                       $response = array("message" => "error","text"=>"Fail to update photo.Please try again.");
                   }
                }else{
                   // Insert a new book record
                   $stmt = $con->prepare("INSERT INTO books (PhotoName) VALUES (?)");
                   $stmt->bind_param("s", $filePath);
    
                   if ($stmt->execute()) {
                       $BookID = mysqli_insert_id($con); // Get the auto-generated BookID
                       // Insert a corresponding bookstatus record with the same BookID
                       $stmt = $con->prepare("INSERT INTO bookstatus (BookID, MemberID, Status,AppliedDate, ReturnDate) VALUES (?, NULL, 'Available','','')");
                       $stmt->bind_param("i", $BookID);

                       if ($stmt->execute()) {
                           $response = array("message" => "success", "text" => "New book and bookstatus inserted successfully.","PhotoName" => $filePath, "BookID" => $BookID);
                       } else {
                           $response = array("message" => "error", "text" => "Fail to insert bookstatus. Please try again.");
                       }
                   }else {
                       $response = array("message" => "error", "text" => "Fail to insert a new book. Please try again.");
                   }
                }
             } else {
                // File upload failed
                $response = array("message" => "error","text" => "Failed to move the file to the server.");
             }
           
          } else {
            // Handle file upload errors
            $response = array( "message" => "error","text" => "File upload error: " . $file["error"]);
             }
    } else {
        // No file uploaded
        $response = array( "message" => "error","text" => "No file uploaded.");
        }

header('Content-Type: application/json');
echo json_encode($response);
mysqli_close($con);  
} 

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' ) {
    // Connect to the database
    include_once('conn.php');
    
    $action = $_GET['action'];
  if (isset($action)) {
        if ($action === "deleteBook") {
            $BookID = $_GET['BookID'];
            $bookStatus = $_GET['bookStatus'];
            $response = delete_book_API($BookID,$bookStatus,$con);
    }else if ($action === 'deleteMember') {
            $MemberID = $_GET['MemberID'];
            $response = delete_member_API($MemberID,$con);
          }
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    mysqli_close($con); 
}

?>