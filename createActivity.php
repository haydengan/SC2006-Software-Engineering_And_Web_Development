<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head >
    <meta charset="UTF-8">
    <title>Advanced OneMap Integration with Activity Selection</title>
    <!-- Include OneMap Leaflet CSS -->
    <link rel="stylesheet" href="https://www.onemap.gov.sg/web-assets/libs/leaflet/leaflet.css" />

    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"  />

    <!-- Include FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <!-- Include SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    <link  rel="stylesheet" href="stylesheets/style.css" type="text/css"/>
    
    <!-- Custom styles -->
    <style>
        #map {
            height: 600px;
            width: 100%;
            margin-top : 70px;
        }
        .om-tab-content {
            margin-top: 20px;
        }
        #selected-locations {
            margin-top: 20px;
        }
        #selected-locations h5 {
            margin-bottom: 10px;
        }
        .activity-selection {
            margin-top: 20px;
        }
        .activity-selection h5 {
            margin-bottom: 10px;
        }

    </style>
</head>
<body class="viewactivitybackground">
    <!-- Navbar -->
    <nav>
        <link href="stylesheets/style.css" rel="stylesheet" type="text/css"/>
        <?php include "navbar.php" ?>
    </nav>
    <div class="container-fluid" >
    <!-- Content -->
    <div  class="row mt-3">
        <!-- Left Column -->
        <div class="col-lg-3" style="font-family: 'Dancing Script', cursive";>
            <!-- Tabs -->
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-link active" id="markers-tab" data-bs-toggle="tab" data-bs-target="#markers" role="tab">
                        <i class="fa fa-map-marker"></i>&nbsp;Markers
                    </a>
                    <a class="nav-link" id="options-tab" data-bs-toggle="tab" data-bs-target="#options" role="tab">
                        <i class="fa fa-cog"></i>&nbsp;Settings
                    </a>
                </div>
            </nav>

            <!-- Tab Contents -->
            <div id="nav-tabContent" class="tab-content om-tab-content">
                <!-- Marker Tab -->
                <div class="tab-pane fade show active" id="markers" role="tabpanel">
                    <div class="form-group mt-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fa fa-search"></i>
                            </span>
                            <input id="om-searchbar" class="form-control" type="text" placeholder="Enter a location..." />
                            <button class="btn btn-primary" onclick="searchLocation()">Search</button>
                        </div>
                        <div id="vw-search-results" class="list-group mt-1 mb-3"></div>
                    </div>
                    <hr />
                    <button type="button" id="clearSelectedLocations" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Clear Selected Locations
                    </button>

                    <!-- Selected Locations List -->
                    <div id="selected-locations">
                        <h5><i class="fa fa-list"></i> Selected Locations</h5>
                        <ul class="list-group" id="location-list"></ul>
                    </div>
                </div>

                <!-- Map Settings Tab -->
                <div class="tab-pane fade" id="options" role="tabpanel">
                    <div class="form-group mt-3">
                        <label for="mapStyle">Map Style</label>
                        <select id="mapStyle" class="form-select">
                            <option value="Default" selected>Default</option>
                            <option value="Grey">Grey</option>
                            <option value="Night">Night</option>
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="zoom">Zoom Level</label>
                        <select id="zoom" class="form-select">
                            <option>11</option>
                            <option>12</option>
                            <option selected>15</option>
                            <option>17</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Column -->
        <div class="col-lg-6">
            <div id="map"></div>
        </div>

        <!-- Right Column: Activity Selection -->
        <div class="col-lg-3" style="font-family: 'Dancing Script', cursive;">
            <div class="card activity-selection" style="margin-top: 200px;">
                <div class="card-body">


                    <h5 class="card-title">Select Activity Types</h5>
                    <form id="activity-form" method="POST" action="viewActivity.php">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="restaurants" id="activity-restaurants">
                            <label class="form-check-label" for="activity-restaurants">Restaurants</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="shopping" id="activity-shopping">
                            <label class="form-check-label" for="activity-shopping">Shopping</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="bars" id="activity-bars">
                            <label class="form-check-label" for="activity-bars">Bars</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="cafes" id="activity-cafes">
                            <label class="form-check-label" for="activity-cafes">Cafes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="parks" id="activity-parks">
                            <label class="form-check-label" for="activity-parks">Parks</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="activities" id="activity-activities">
                            <label class="form-check-label" for="activity-activities">Activities</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="others" id="activity-others">
                            <label class="form-check-label" for="activity-others">Others</label>
                        </div>
                        <!-- Additional activities can be added here -->
                        <input type="hidden" name="selectedLocations" id="selectedLocationsInput" />
                        <button type="submit" id="begin-activity" class="btn btn-primary mt-3">Begin Activity</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include JavaScript libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.onemap.gov.sg/web-assets/libs/leaflet/onemap-leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom scripts -->
<script>
    // Initialize the map
    var bounds = L.latLngBounds([1.144, 103.535], [1.494, 104.502]);
    var map = L.map('map', {
        center: [1.2868108, 103.8545349],
        zoom: 16,
        minZoom: 11,
        maxZoom: 19,
        maxBounds: bounds
    });
    var basemap = L.tileLayer('https://www.onemap.gov.sg/maps/tiles/Default/{z}/{x}/{y}.png', {
        detectRetina: true,
        maxZoom: 19,
        minZoom: 11,
        attribution: '<img src="https://www.onemap.gov.sg/web-assets/images/logo/om_logo.png" style="height:20px;width:20px;"/> ' +
            '<a href="https://www.onemap.gov.sg/" target="_blank">OneMap</a> © contributors | ' +
            '<a href="https://www.sla.gov.sg/" target="_blank">Singapore Land Authority</a>'
    });
    basemap.addTo(map);

    let markers = {};
    let selectedLocations = [];

    function searchLocation() {
        const query = document.getElementById('om-searchbar').value.trim();
        if (!query) {
            Swal.fire('Error', 'Please enter a location!', 'error');
            return;
        }
        const url = `https://www.onemap.gov.sg/api/common/elastic/search?searchVal=${encodeURIComponent(query)}&returnGeom=Y&getAddrDetails=Y&pageNum=1`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const searchResultsDiv = document.getElementById('vw-search-results');
                searchResultsDiv.innerHTML = '';
                if (data.found === 0) {
                    Swal.fire('No Results', 'No results found!', 'info');
                    return;
                }
                data.results.forEach((result) => {
                    const lat = parseFloat(result.LATITUDE);
                    const lng = parseFloat(result.LONGITUDE);
                    const address = result.ADDRESS;
                    const listItem = document.createElement('div');
                    listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                    listItem.innerHTML = `<span>${address}</span>`;
                    const addButton = document.createElement('button');
                    addButton.className = 'btn btn-sm btn-success';
                    addButton.innerHTML = '<i class="fa fa-plus"></i> Add';
                    addButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        addLocationToList(result);
                    });
                    listItem.appendChild(addButton);
                    listItem.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (e.target !== addButton && !addButton.contains(e.target)) {
                            map.setView([lat, lng], 17);
                        }
                    });
                    searchResultsDiv.appendChild(listItem);
                });
                const firstResult = data.results[0];
                map.setView([parseFloat(firstResult.LATITUDE), parseFloat(firstResult.LONGITUDE)], 17);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Search failed. Try again later.', 'error');
            });
    }

    function addLocationToList(location) {
        if (selectedLocations.some(loc => loc.ADDRESS === location.ADDRESS)) {
            Swal.fire('Info', 'Location already added to the list.', 'info');
            return;
        }
        selectedLocations.push(location);
        updateLocationList();
        addMarkerForLocation(location);
    }

    function updateLocationList() {
        const locationList = document.getElementById('location-list');
        locationList.innerHTML = '';
        selectedLocations.forEach((location, index) => {
            const listItem = document.createElement('li');
            listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
            listItem.innerHTML = `<span>${location.ADDRESS}</span>`;
            const removeButton = document.createElement('button');
            removeButton.className = 'btn btn-sm btn-danger';
            removeButton.innerHTML = '<i class="fa fa-trash"></i> Remove';
            removeButton.addEventListener('click', () => {
                removeLocationFromList(index);
            });
            listItem.appendChild(removeButton);
            locationList.appendChild(listItem);
        });
    }

    function addMarkerForLocation(location) {
        const lat = parseFloat(location.LATITUDE);
        const lng = parseFloat(location.LONGITUDE);
        const address = location.ADDRESS;
        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup(`<b>${address}</b>`);
        markers[address] = marker;
    }

    function removeLocationFromList(index) {
        const location = selectedLocations[index];
        const address = location.ADDRESS;
        if (markers[address]) {
            map.removeLayer(markers[address]);
            delete markers[address];
        }
        selectedLocations.splice(index, 1);
        updateLocationList();
    }

    document.getElementById('clearSelectedLocations').addEventListener('click', () => {
        for (let address in markers) {
            map.removeLayer(markers[address]);
        }
        markers = {};
        selectedLocations = [];
        updateLocationList();
    });

    document.getElementById('mapStyle').addEventListener('change', function() {
        const selectedStyle = this.value;
        basemap.setUrl(`https://www.onemap.gov.sg/maps/tiles/${selectedStyle}/{z}/{x}/{y}.png`);
    });

    document.getElementById('zoom').addEventListener('change', function() {
        const zoomLevel = parseInt(this.value);
        map.setZoom(zoomLevel);
    });

    document.getElementById('begin-activity').addEventListener('click', function() {
        const activityTypes = [];
        const checkboxes = document.querySelectorAll('#activity-form input[type="checkbox"]:checked');
        checkboxes.forEach((checkbox) => {
            activityTypes.push(checkbox.value);
        });
        if (activityTypes.length === 0) {
            Swal.fire('Info', 'Please select at least one activity type.', 'info');
            return;
        }
        // Placeholder for actual functionality
        Swal.fire('Selected Activities', activityTypes.join(', '), 'info');
    });

    document.getElementById('activity-form').addEventListener('submit', function (e) {
        // Convert selectedLocations to JSON and set it to the hidden input
        document.getElementById('selectedLocationsInput').value = JSON.stringify(selectedLocations);

        // You can also serialize the selected activity types
        // For simplicity, let's serialize the activity types as well
        const activityTypes = [];
        const checkboxes = document.querySelectorAll('#activity-form input[type="checkbox"]:checked');
        checkboxes.forEach((checkbox) => {
            activityTypes.push(checkbox.value);
        });
        // Add another hidden input for activity types
        let activityTypesInput = document.createElement('input');
        activityTypesInput.type = 'hidden';
        activityTypesInput.name = 'activityTypes';
        activityTypesInput.value = JSON.stringify(activityTypes);
        this.appendChild(activityTypesInput);
    });


</script>
</body>
</html>
