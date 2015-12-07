$(document).ready(init);

function init() {
    // Attach events
    $("#logout-link").on("click",logout); // Logout from system
    $("#find-car").on("click", find_cars); // Find Cars
    $("#find-car-input").on("keydown", function(event){maybe_search(event);});
    
    // Need event for RETURN car and any events which are based on the
    // rental history of the customer.
    $("#return_car").on("click", show_rental_history);
}

function maybe_search(event){
    if (event.keyCode == 13)
        find_cars();
}

function find_cars() {
    // Get the search string that the user typed in
    var search_string = $("#find-car").val();
    $.ajax({
        method: "POST",
        url: "controller.php",
        dataType: "json",
        data: { type:"search",
                value:search_string },
        success: function (rec_data) {
            if (rec_data == "failure")
            {
                alert("Your search query was improperly transmitted. Please try again.");
            }
            else
            {
                // Build HTML elements
                var info_template=$("#find-car-template").html();
                var html_maker=new htmlMaker(info_template);
                var html=html_maker.getHTML(rec_data);
                $("#search_results").html(html);

                // Now that DOM has been altered, need to attach events
                $(".car_rent").on("click", rent_car($this.attr("id")));
            }
        }
    });
}

function rent_car(id)
{
    $.ajax({
        method: "POST",
        url: "controller.php",
        dataType: "json",
        data: { type:"rent",
                value:id },
        success: function (rec_data) {
                if (rec_data == "failure")
                {
                    alert("Your rent request was denied. Please try again.");
                }
                else
                {
                    // Update the Rental View
                    find_cars();
                    alert("Car Successfully rented!");
                    // Build HTML elements
                    var info_template=$("#find-car-template").html();
                    var html_maker=new htmlMaker(info_template);
                    var html=html_maker.getHTML(rec_data);
                    $("#search_results").html(html);
                    // Now that DOM has been altered, need to attach events
                    $(".car_rent").on("click", rent_car($this.attr("id")));
                }
        }
    });
}

/* Here is where the other two functions go to find cars currently rented by
 * A customer and to pull a customer's rental history (based on returns).
 * Keep in mind that the LOGIN function in PHP sets some session variables
 * which are probably helpful:
 * $_SESSION["username"] = $row["name"];  //Customer name here.
 * $_SESSION["ID"] = $row["ID"]; // Also save the user's ID for updates and fast SQL queries.
 */

function show_rented_cars()
{
 
}

function show_rental_history()
{
    $.ajax({
		method: "POST",
		url: "controller.php",
		dataType: "text",
		data:{type:"history"} , // not sure
		success: function () {
			// show the data
                        find_cars();
		}
	});
}

function logout(){
	// Terminate Session through Controller
	$.ajax({
		method: "POST",
		url: "controller.php",
		dataType: "json",
		data: { type:"logout" },
		success: function (rec_data) {
			if ($.trim(rec_data) == "success")
			{
                            alert("You have been logged out!");
                            // Redirect back to homepage
                            window.location.assign("index.html"); //redirect the page to cars.html
			}
			else
                            alert("You have NOT been logged out! Try again.");
		}
	});
}