$(document).ready(init);

function init() {
    // Attach events
    $("#logout-link").on("click",logout); // Logout from system
    $("#find-car").on("click", find_cars); // Find Cars
    $("#find-car-input").on("keydown", function(event){maybe_search(event);});
    // Need event for RETURN car and any events which are based on the
    $(".return_car").on("click",return_car($this.attr("data-rental-id")));
    //there needs to be a event listner for the tabs, however since they dont have ID's i'm not quite sure
    //how to do that, i can add IDs and do a couple event statements, but i wanted to kow what you thought first.
    // rental history of the customer.
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

function return_car(rentId)
{
    $.ajax({
        method: "POST",
        url: "controller.php",
        dataType: "json",
        data: { type: "return",value:rentId},
        success: function (rec_data) {
            if(rec_data == "failure")
            {
                alert("Your return request failed or something");//should make this more professional
            }
            else
            {
                //update the return view
                show_rented_cars();
                alert("Car successfully returned!");
                //build the html again?
                var info_template=$("#rented-car-template").html();
                var html_maker=new htmlMaker(info_template);
                var html=html_maker.getHTML(rec_data);    
                $("#rented_cars").html(html);
                
                $(".return_car").on("click",return_car($this.attr("data-rental-id")));//pretty sure this is correct
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
    var showIt = "showData";
    $.ajax({
        method: "POST",
        url: "controller.php",
        dataType: "json",
        data: { type:"rentals",
                value:showIt},
        success: function(rec_data) {
            if(rec_data == "failure")
            {
                alert("something went wrong trying to display rentals.")
            }
            else
            {
                var info_template=$("#rented-car-template").html();
                var html_maker=new htmlMaker(info_template);
                var html=html_maker.getHTML(rec_data);    
                $("#rented_cars").html(html);
                
                $(".return_car").on("click",return_car($this.attr("data-rental-id")));//pretty sure this is correct                
            }
        }
    });
}

function show_rental_history()
{
    
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