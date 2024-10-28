<?php
session_start();
include "dbFunctions.php";
if(isset($_SESSION['user_id'])){
    $value=$_SESSION['user_id'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the selected locations and activity types from POST
    $selectedLocations = json_decode($_POST['selectedLocations'], true);
    $activityTypes = json_decode($_POST['activityTypes'], true);
    // Step 1: Calculate the midpoint
    $latSum = 0;
    $lngSum = 0;
    $count = count($selectedLocations);

    foreach ($selectedLocations as $location) {
        $latSum += floatval($location['LATITUDE']);
        $lngSum += floatval($location['LONGITUDE']);
    }
    // Calculate averages
    $midLat = $latSum / $count;
    $midLng = $lngSum / $count;

    // Step 2: Use Google Places API to find activities
    $apiKey = 'AIzaSyDlysC2KJoF75esgZtWeZgEmBgrIQngbI0'; // Replace with your Google API key
    //$apiKey = 'bogus';
    // Map our activity types to Google Places types
    $typeMapping = [
        'restaurants' => 'restaurant',
        'shopping' => 'shopping_mall',
        'bars' => 'bar',
        'cafes' => 'cafe',
        'parks' => 'park',
        'activities' => ['amusement_park', 'aquarium', 'art_gallery', 'bowling_alley', 'museum', 'zoo'],
        'others' => ['tourist_attraction', 'stadium', 'library', 'movie_theater', 'night_club']
    ];

    // Function to validate place types
    function isValidPlaceType($type) {
        // List of valid place types for filtering (as per Google Places API documentation)
        $validTypes = [
            'accounting', 'airport', 'amusement_park', 'aquarium', 'art_gallery', 'atm', 'bakery',
            'bank', 'bar', 'beauty_salon', 'bicycle_store', 'book_store', 'bowling_alley',
            'bus_station', 'cafe', 'campground', 'car_dealer', 'car_rental', 'car_repair',
            'car_wash', 'casino', 'cemetery', 'church', 'city_hall', 'clothing_store',
            'convenience_store', 'courthouse', 'dentist', 'department_store', 'doctor',
            'drugstore', 'electrician', 'electronics_store', 'embassy', 'fire_station', 'florist',
            'funeral_home', 'furniture_store', 'gas_station', 'gym', 'hair_care', 'hardware_store',
            'hindu_temple', 'home_goods_store', 'hospital', 'insurance_agency', 'jewelry_store',
            'laundry', 'lawyer', 'library', 'light_rail_station', 'liquor_store', 'local_government_office',
            'locksmith', 'lodging', 'meal_delivery', 'meal_takeaway', 'mosque', 'movie_rental',
            'movie_theater', 'moving_company', 'museum', 'night_club', 'painter', 'park',
            'parking', 'pet_store', 'pharmacy', 'physiotherapist', 'plumber', 'police',
            'post_office', 'primary_school', 'real_estate_agency', 'restaurant', 'roofing_contractor',
            'rv_park', 'school', 'secondary_school', 'shoe_store', 'shopping_mall', 'spa',
            'stadium', 'storage', 'store', 'subway_station', 'supermarket', 'synagogue',
            'taxi_stand', 'tourist_attraction', 'train_station', 'transit_station', 'travel_agency',
            'university', 'veterinary_care', 'zoo'
        ];
        return in_array($type, $validTypes);
    }
    // Convert our activity types to Google Places types
    $googlePlaceTypes = [];
    foreach ($activityTypes as $type) {
        if (isset($typeMapping[$type])) {
            $mappedTypes = $typeMapping[$type];
            if (is_array($mappedTypes)) {
                foreach ($mappedTypes as $mappedType) {
                    if (isValidPlaceType($mappedType)) {
                        $googlePlaceTypes[] = $mappedType;
                    }
                }
            } else {
                if (isValidPlaceType($mappedTypes)) {
                    $googlePlaceTypes[] = $mappedTypes;
                }
            }
        }
    }

    // Ensure we have valid place types
    if (empty($googlePlaceTypes)) {
        die('No valid activity types selected. Please select at least one valid activity type.');
    }

    // Prepare the request body
    $requestBody = [
        'maxResultCount' => 20,
        'locationRestriction' => [
            'circle' => [
                'center' => [
                    'latitude' => $midLat,
                    'longitude' => $midLng
                ],
                'radius' => 1000.0 // Adjust the radius as needed
            ]
        ],
        'includedTypes' => $googlePlaceTypes
    ];

    // Convert the request body to JSON
    $jsonRequestBody = json_encode($requestBody);

    // Set the headers
    $headers = [
        'Content-Type: application/json',
        'X-Goog-Api-Key: ' . $apiKey,
        'X-Goog-FieldMask: places.displayName,places.location'
    ];

    // Initialize cURL
    $ch = curl_init('https://places.googleapis.com/v1/places:searchNearby');

    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonRequestBody);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Report HTTP errors

    // Execute the request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        die('cURL error: ' . $error);
    }

    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Decode the response
    $placesData = json_decode($response, true);
    // Check for errors in the response
    if (isset($placesData['error'])) {
        die('Error fetching places: ' . $placesData['error']['message']);
    }

    // Check if any places were found
    if (!isset($placesData['places']) || empty($placesData['places'])) {
        die('No places found for the selected activity types.');
    }

    // Randomly select one of the places
    $places = $placesData['places'];
    $selectedPlace = $places[array_rand($places)];

    // Prepare data for JavaScript
    $selectedLocationsJson = json_encode($selectedLocations);
    $activityLat = $selectedPlace['location']['latitude'];
    $activityLng = $selectedPlace['location']['longitude'];
    $activityName = $selectedPlace['displayName']['text'];

    //save activity to database
    //prepare posted data as STRING to send to db
    $DBselectedLocations = json_encode($selectedLocations);
    $DBactivityTypes = json_encode($activityTypes);
    $mapLink = "";
    $queryInsert = "INSERT INTO activity (creator_id,activityLat,activityLng,suggestedActivityName,selectedLocations,activityTypes,date_created) VALUES ($value,'$activityLat','$activityLng','$activityName','$DBselectedLocations','$DBactivityTypes',now())";
    $resultCheck = mysqli_query($link, $queryInsert) or die (mysqli_error($link));
    //grab highest auto increment, which will be the activity ID
    $id = mysqli_insert_id($link);
}
else{
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    //create select statement that pulls all from above.
    $querySelect = "SELECT activityLat,activityLng,suggestedActivityName,selectedLocations,activityTypes FROM activity where creator_id =".$value." AND activity_id =".$id."";
    $resultSelect = mysqli_query($link, $querySelect) or 
            die (mysqli_error($link));
    while ($row = mysqli_fetch_array($resultSelect)) {
        $activity[] = $row;
    }
    $activityLat = $activity[0]['activityLat'];
    $activityLng = $activity[0]['activityLng'];
    $activityName = $activity[0]['suggestedActivityName'];
    $selectedLocations = json_decode($activity[0]['selectedLocations'], true);
    $selectedLocationsJson = json_encode($selectedLocations);

    $latSum = 0;
    $lngSum = 0;
    $count = count($selectedLocations);

    foreach ($selectedLocations as $location) {
        $latSum += floatval($location['LATITUDE']);
        $lngSum += floatval($location['LONGITUDE']);
    }
    // Calculate averages
    $midLat = $latSum / $count;
    $midLng = $lngSum / $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Route</title>
    <link rel="stylesheet" href="https://www.onemap.gov.sg/web-assets/libs/leaflet/leaflet.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 20px;
        }

        h2 {
            text-align: center;
            color: #555;
            margin-top: 10px;
        }

        /* Main Container */
        #main-container {
            display: flex;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 20px auto;
        }

        /* Left Column */
        #left-column {
            flex: 1 1 400px;
            max-width: 400px;
            margin-right: 20px;
        }

        /* Right Column */
        #right-column {
            flex: 2 1 600px;
            min-width: 300px;
        }

        /* Form Styling */
        #options-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #options-form div {
            margin-bottom: 15px;
        }

        #options-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        #options-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        #options-form button {
            width: 100%;
            padding: 10px;
            background-color: #0066cc;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        #options-form button:hover {
            background-color: #005bb5;
        }

        /* Route Instructions Styling */
        #route-instructions {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #route-instructions h2 {
            margin-top: 0;
        }

        #route-instructions ul {
            list-style-type: none;
            padding-left: 0;
        }

        #route-instructions li {
            margin-bottom: 15px;
        }

        .instruction-step {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #0066cc;
        }

        .instruction-step:not(:last-child) {
            margin-bottom: 15px;
        }

        .instruction-step strong {
            display: block;
            margin-bottom: 5px;
            color: #0066cc;
        }

        /* Map Styling */
        #map {
            height: 800px;
            width: 100%;
        }

        /* Responsive Adjustments */
        @media (max-width: 800px) {
            #main-container {
                flex-direction: column;
            }

            #left-column, #right-column {
                max-width: 100%;
                margin-right: 0;
            }

            #map {
                height: 400px;
            }
        }
    </style>


</head>
<body class="viewactivitybackground">
<nav>
    <link href="stylesheets/style.css" rel="stylesheet" type="text/css"/>
    <?php include "navbar.php" ?>
</nav>
<h1 style="font-family: 'Lobster', cursive">Your Activity Route</h1>
<h2 id="activity-location" style="font-family: 'Lobster', cursive"></h2>
<h2 style="font-family: 'Lobster', cursive">Your sharable link is:<a href="<?php echo "http://localhost/viewActivity.php?id=".$id;?>"><?php echo "http://localhost/viewActivity.php?id=".$id;?></a></h2> 

<!-- Main Container -->
<div id="main-container" style="font-family: 'Lobster', cursive">
    <!-- Left Column -->
    <div id="left-column">
        <!-- Selection Form -->
        <form id="options-form">
            <div>
                <label for="startLocation">Select Starting Location:</label>
                <select id="startLocation" name="startLocation">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div>
                <label for="transportMode">Select Mode of Transport:</label>
                <select id="transportMode" name="transportMode">
                    <option value="walk">Walk</option>
                    <option value="drive">Drive</option>
                    <option value="cycle">Cycle</option>
                    <option value="pt">Public Transport</option>
                </select>
            </div>
            <button type="button" id="showRouteButton">Show Route</button>
        </form>

        <!-- Route Instructions Container -->
        <div id="route-instructions">
            <!-- Instructions will be populated here -->
        </div>
    </div>

    <!-- Right Column -->
    <div id="right-column">
        <!-- Map Container -->
        <div id="map"></div>
    </div>
</div>
<!-- Include Leaflet JS -->
<script src="https://www.onemap.gov.sg/web-assets/libs/leaflet/onemap-leaflet.js"></script>

<!-- JavaScript to display map and route -->
<script>
    var map = L.map('map').setView([<?php echo $midLat; ?>, <?php echo $midLng; ?>], 15);
    L.tileLayer('https://www.onemap.gov.sg/maps/tiles/Default/{z}/{x}/{y}.png', {
        detectRetina: true,
        maxZoom: 19,
        minZoom: 11,
        attribution: '<a href="https://www.onemap.gov.sg/" target="_blank">OneMap</a>'
    }).addTo(map);

    // Activity location
    var toLat = <?php echo $activityLat; ?>;
    var toLng = <?php echo $activityLng; ?>;
    var activityName = "<?php echo addslashes($activityName); ?>";

    // Display the activity location to the user
    document.getElementById('activity-location').innerText = 'Selected Activity: ' + activityName;
    // Optional: Get the activity address
    // Uncomment the following code and replace 'YOUR_GOOGLE_API_KEY' with your actual key


    function getAddress(lat, lng) {
        var geocodeUrl = 'https://maps.googleapis.com/maps/api/geocode/json?' +
            'latlng=' + lat + ',' + lng +
            '&key=AIzaSyDlysC2KJoF75esgZtWeZgEmBgrIQngbI0'; // Replace with your API key

        fetch(geocodeUrl)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'OK' && data.results.length > 0) {
                    var address = data.results[0].formatted_address;
                    document.getElementById('activity-location').innerText = 'Selected Activity: ' + activityName + '\nAddress: ' + address;
                } else {
                    document.getElementById('activity-location').innerText = 'Selected Activity: ' + activityName;
                }
            })
            .catch(error => {
                console.error('Error fetching address:', error);
                document.getElementById('activity-location').innerText = 'Selected Activity: ' + activityName;
            });
    }

    // Call the function to get the address
    getAddress(toLat, toLng);


    // Add marker for activity location
    L.marker([toLat, toLng]).addTo(map).bindPopup(activityName);

    // List of selected locations from PHP
    var selectedLocations = <?php echo $selectedLocationsJson; ?>;

    // Populate the starting location dropdown
    var startLocationSelect = document.getElementById('startLocation');
    selectedLocations.forEach(function(location, index) {
        var option = document.createElement('option');
        option.value = index;
        option.text = location.ADDRESS;
        startLocationSelect.appendChild(option);
    });

    // Add event listener to the "Show Route" button
    document.getElementById('showRouteButton').addEventListener('click', function() {
        var selectedIndex = startLocationSelect.value;
        var transportMode = document.getElementById('transportMode').value;

        var selectedLocation = selectedLocations[selectedIndex];
        var fromLat = parseFloat(selectedLocation.LATITUDE);
        var fromLng = parseFloat(selectedLocation.LONGITUDE);

        // Clear existing route if any
        if (window.routeLayer) {
            map.removeLayer(window.routeLayer);
        }

        // Add marker for starting location
        if (window.startMarker) {
            map.removeLayer(window.startMarker);
        }
        window.startMarker = L.marker([fromLat, fromLng]).addTo(map).bindPopup('Starting Location');

        // Fetch and display route
        fetchRoute(fromLat, fromLng, toLat, toLng, transportMode);
    });

    function fetchRoute(fromLat, fromLng, toLat, toLng, transportMode) {
        var start = fromLat + ',' + fromLng;
        var end = toLat + ',' + toLng;
        var routeType = transportMode.toLowerCase(); // 'walk', 'drive', 'cycle', 'pt'

        var url = 'get_route.php?' +
            'start=' + encodeURIComponent(start) +
            '&end=' + encodeURIComponent(end) +
            '&routeType=' + routeType;

        // For public transport, additional parameters are required
        if (routeType === 'pt') {
            // Get current date and time with leading zeros
            var now = new Date();
            var month = ('0' + (now.getMonth() + 1)).slice(-2);
            var day = ('0' + now.getDate()).slice(-2);
            var date = month + '-' + day + '-' + now.getFullYear();

            var hours = ('0' + now.getHours()).slice(-2);
            var minutes = ('0' + now.getMinutes()).slice(-2);
            var seconds = ('0' + now.getSeconds()).slice(-2);
            var time = hours + minutes + seconds; // Time format HHMMSS

            url += '&date=' + encodeURIComponent(date) +
                '&time=' + encodeURIComponent(time) +
                '&mode=TRANSIT'; // You may allow users to select mode (TRANSIT, BUS, RAIL)
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Clear previous instructions
                document.getElementById('route-instructions').innerHTML = '';

                if (routeType === 'pt') {
                    // Handle public transport route
                    if (data.plan) {
                        var itineraries = data.plan.itineraries;
                        if (itineraries.length > 0) {
                            var itinerary = itineraries[0]; // You can allow users to select different itineraries
                            var legs = itinerary.legs;
                            var coordinates = [];
                            var instructions = []; // To hold the instruction steps

                            legs.forEach(function(leg) {
                                // Decode the polyline for this leg
                                var legGeometry = decodePolyline(leg.legGeometry.points);
                                coordinates = coordinates.concat(legGeometry);

                                // Extract instruction details
                                var step = {
                                    mode: leg.mode,
                                    route: leg.route, // For transit modes like BUS or RAIL
                                    startTime: new Date(leg.startTime),
                                    endTime: new Date(leg.endTime),
                                    from: leg.from.name,
                                    to: leg.to.name,
                                    duration: leg.duration,
                                    instructions: ''
                                };

                                if (leg.transitLeg) {
                                    // For transit legs, include route (bus number, train line)
                                    step.instructions = 'Take ' + leg.mode + ' ' + leg.routeShortName +
                                        ' from ' + step.from + ' to ' + step.to + '.';
                                } else {
                                    // For walking legs
                                    step.instructions = 'Walk from ' + step.from + ' to ' + step.to + '.';
                                }

                                instructions.push(step);
                            });

                            // Add route to map
                            window.routeLayer = L.polyline(coordinates, { color: 'blue' }).addTo(map);
                            map.fitBounds(window.routeLayer.getBounds());

                            // Display instructions
                            displayInstructions(instructions);
                        } else {
                            alert('No route found.');
                        }
                    } else {
                        alert('Error fetching route: ' + data.error);
                    }
                } else {
                    // Handle walk, drive, cycle
                    if (data.status === 0) {
                        var routeGeometry = decodePolyline(data.route_geometry);
                        window.routeLayer = L.polyline(routeGeometry, { color: 'blue' }).addTo(map);
                        map.fitBounds(window.routeLayer.getBounds());

                        // Display instructions (simple for walk, drive, cycle)
                        var instruction = {
                            mode: transportMode.charAt(0).toUpperCase() + transportMode.slice(1),
                            instructions: 'Travel from your starting location to the destination.',
                        };
                        displayInstructions([instruction]);
                    } else {
                        alert('Error fetching route: ' + data.error_message);
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching route:', error);
                alert('Error fetching route. Please try again.');
            });
    }

    function displayInstructions(instructions) {
        var instructionsContainer = document.getElementById('route-instructions');

        if (instructions.length === 0) {
            instructionsContainer.innerHTML = '<p>No instructions available.</p>';
            return;
        }

        var html = '<h2>Route Instructions</h2>';

        // Calculate total duration
        var totalDuration = instructions.reduce(function(sum, step) {
            return sum + (step.duration || 0);
        }, 0);
        var totalMinutes = Math.round(totalDuration / 60);

        html += '<p>Total Estimated Time: ' + totalMinutes + ' minutes</p>';
        html += '<ul>';

        instructions.forEach(function(step, index) {
            html += '<li class="instruction-step">';
            html += '<strong>Step ' + (index + 1) + ':</strong><br>';

            if (step.startTime && step.endTime) {
                html += 'Start Time: ' + step.startTime.toLocaleTimeString() + '<br>';
                html += 'End Time: ' + step.endTime.toLocaleTimeString() + '<br>';
            }

            html += '<p>' + step.instructions + '</p>';

            // Additional details for transit legs
            if (step.transitDetails) {
                html += '<p>Details: ' + step.transitDetails + '</p>';
            }

            html += '</li>';
        });

        html += '</ul>';

        instructionsContainer.innerHTML = html;
    }


    // Function to decode polyline
    function decodePolyline(encoded) {
        var points = [];
        var index = 0, len = encoded.length;
        var lat = 0, lng = 0;

        while (index < len) {
            var b, shift = 0, result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1F) << shift;
                shift += 5;
            } while (b >= 0x20);
            var dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lat += dlat;

            shift = 0;
            result = 0;
            do {
                b = encoded.charCodeAt(index++) - 63;
                result |= (b & 0x1F) << shift;
                shift += 5;
            } while (b >= 0x20);
            var dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
            lng += dlng;

            points.push([lat / 1E5, lng / 1E5]);
        }

        return points;
    }
</script>
</body>
</html>

