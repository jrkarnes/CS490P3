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
	}
}
	echo $result;

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

/* This is where the rental history and rented cars functions go.
 * If you'll notice, I have a short helper function here called
 * get_current_date which will return the current date as a string
 * in YYYY-MM-DD format for helping out with the SQL queries for returning
 * a rented car. Don't forget to update the rental status on the CAR table
 * since the rental status is duplicated in this table.
 */

/* I have included the RENTED CAR block here for your convienence in writing
 * the array structure to JSON encode for the element builder
 {{#block rented_car}}
            <tr>
                <td><img src="{{picture}}"></td> 
                <td class="car_details"> 
                    <div class="car_title">
                        <div class="car_make">
                            {{make}} | {{model}}
                        </div>
                        <div class="car_year">
                            {{year}}
                        </div>
                    </div>
                    <div class="car_size">
                        Size: {{size}}
                    </div>
                    <div class="rental_ID">
                        Rental #: {{rental_ID}}
                    </div>   
                    <div class="car_date">
                        Rent date: {{rent_date}}
                    </div>          
                </td>
                <td>
                    <div class="return_car" data-rental-id="{{rental_ID}}">Return Car</div>
                </td>

            </tr>
{{#end block rented_car}}
 */

/* I have also included the RETURNED CAR block for much the same reason:
{{#block returned_car}}
            <tr>
                <td><img src="{{picture}}"></td> 
                <td class="car_details"> 
                    <div class="car_title">
                        <div class="car_make">
                            {{make}} | {{model}}
                        </div>
                        <div class="car_year">
                            {{year}}
                        </div>
                    </div>
                    <div class="car_size">
                        Size: {{size}}
                    </div>
                    <div class="rental_ID">
                        Rental #: {{rental_ID}}
                    </div>   
                    <div class="car_date">
                        Return date: {{return_date}}
                    </div>          
                </td>
            </tr>
{{#end block returned_car}}
 */

function get_current_date()
{
    // From W3C Schools
    $t=time();
    return (date("Y-m-d",$t));
}

?>