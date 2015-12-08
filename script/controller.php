<!--Site Controller-->
<?php

include "sanitization.php";
$result = "";

// We don't want to do anything unless there is an active session
if (isset($_POST['type']) && is_session_active())
{
	// What kind of request is this?
	// Make sure it's not something nasty
	$request_type = sanitizeMYSQL($connection, $_POST['type']);

	// What do we want to do with it?
	switch ($request_type) {
	case "logout":
		logout();
		$result = "success";
		break;
	case "search":
		// If the search query didn't make it through we don't want to do anything.
		if (isset($_POST['value']))
		{
			$search_string = $_POST['value'];
			$result = find_cars($connection, $search_string);
		}
		else {$result = "failure";}
		break;
	case "rent":
		if (isset($_POST['value']))
		{
			// This should work
			$result = rent_car($connection, $_POST['value']);
		}
		else {$result = "failure";}
		break;
        case "history":
		$result = get_rental_history($connection);
		break;
	case "rentals":
		if(isset($_POST['value']))
		{
			$result = show_rented($connection); // Checking for post's VALUE but not using it?
		}
		else {$result = "failure";}
		break;
	case "return":
		if(isset($_POST['value']))
		{
			$result = return_car($connection, $_POST['value']);
		}
		else {$result = "failure";}
		break;
	}
	echo $result;
}


function is_session_active() {
    return isset($_SESSION) && count($_SESSION) > 0 && time() < $_SESSION['start'] + 60 * 5; //check if it has been 5 minutes
}
	
function logout() {
    // Unset all of the session variables.
    $_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

// Finally, destroy the session.
    session_destroy();
}

function find_cars($connection, $search_string)
{
    $final = Array();
    $final["cars"] = Array();
    $query = "SELECT Car.ID, Car.Color, Car.Picture, CarSpecs.Make, CarSpecs.Model, CarSpecs.YearMade, CarSpecs.Size "
            . "FROM Car INNER JOIN CarSpecs ON Car.CarSpecsID = CarSpecs.ID "
            . "INNER JOIN Rental ON Car.ID = Rental.carID "
            . "WHERE Rental.Status = 2 AND ( "
            . "WHERE Car.Color LIKE %" . $search_string . "% OR "
            . "WHERE CarSpecs.Make LIKE %" . $search_string . "% OR "
            . "WHERE CarSpecs.Model LIKE %" . $search_string . "% OR "
            . "WHERE CarSpecs.YearMade LIKE %" . $search_string . "%)";
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($final);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            $array["ID"] = $row["ID"];
            $array["Color"] = $row["Color"];
            $array["Make"] = $row["Make"];
            $array["Model"]=$row["Model"];
            $array["Year"]=$row["Year"];
            $array["Picture"]=$row["Picture"];
            $final["cars"][] = $array;
        }
    }
    return json_encode($final);
}

function rent_car($connection, $id)
{
    // Create the Rental in SQL and UPDATE the Car object
    $query = "INSERT INTO Rental(rentDate, returnDate, status, CustomerID, carID) "
            . "VALUES ('" . get_current_date() . "', NULL, '1', '"
            . $_SESSION['ID'] . "', '" . $id . "'); "
            . "UPDATE Car SET status = '1' WHERE ID = '" . $id . "';";
    $result = mysqli_query($connection, $query);
    if (!$result)
            return "failure";
    else
            return "success";
}

function get_rental_history($connection)
{
	// I think that the rental history is dependent on the client, isn't it?
	// That's not reflected here.
    $returned = Array();
    $returned["cars"] = Array();
    $query = "SELECT Car.ID, Car.Color, Car.Picture, CarSpecs.Make, CarSpecs.Model, CarSpecs.YearMade, CarSpecs.Size "
            . "FROM Car INNER JOIN CarSpecs ON Car.CarSpecsID = CarSpecs.ID "
            . "INNER JOIN Rental ON Car.ID = Rental.carID "
            . "WHERE Rental.Status = 2 ;"; //  “2” means it has been returned. 
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($returned);
	else{
		$row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
			$array["ID"] = $row["ID"];//i believe this should be the rental id
            $array["Make"] = $row["Make"];
            $array["Model"]=$row["Model"];
            $array["Year"]=$row["Year"];//should this be YearMade?
            $array["Picture"]=$row["Picture"];
            $array["Size"]=$row["Size"];
            $array["rentDate"]=$row["rentDate"];//i am not really understanding the naming pattern here for the keys
            $returned["rentals"][] = $array;
		}
        }
        return json_encode($returned); 
}

function show_rented($connection)
{
    $final = Array();
    $final["rentals"] = Array();
    $query = "SELECT Car.Picture, CarSpecs.Make, CarSpecs.Model, CarSpecs.YearMade, CarSpecs.Size, "
            . "Rental.ID, Rental.rentDate"
            . "FROM Car INNER JOIN CarSpecs ON Car.CarSpecsID = CarSpecs.ID "
            . "INNER JOIN Rental ON Car.ID = Rental.carID "
            . "WHERE Rental.Status = 1 AND "
            . "WHERE Rental.customerID = '" . $_SESSION['ID'] . "';"; //if I am understanding this correctly this would
    //use the stored ID in the session (the users) to grab the rentals that are not returned who also have
    //a customer ID that matches the ID stored in the session, and then grab the car related info associated with the rental
	// -- Jkarnes: Yes. That's exactly what the $*_SESSION['ID'] does.
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($final);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            $array["ID"] = $row["ID"];
            $array["Color"] = $row["Color"];
            $array["Make"] = $row["Make"];
            $array["Model"]=$row["Model"];
            $array["Year"]=$row["Year"];
            $array["Picture"]=$row["Picture"];
            $final["rentals"][] = $array;
        }
    }
    
    return json_encode($final);
}

function return_car($connection, $id)
{
    $query = "UPDATE Rental SET status = '2', returnDate = '" . get_current_date()
            . "' WHERE ID = '" . $id . "';"
            . "UPDATE Car SET status = '1' FROM Car INNER JOIN Rental ON Car.ID = Rental.carID"
            . "WHERE Rental.ID = '" . $id . "';";
    $result = mysqli_query($connection, $query);
    if (!$result)
            return "failure";
    else
            return "success";
}

function get_current_date()
{
    // From W3C Schools
    $t=time();
    return (date("Y-m-d",$t));
}

?>
