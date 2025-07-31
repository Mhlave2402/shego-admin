<!DOCTYPE html>
<html>
<head>
    <title>Track Trip</title>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <script>
        let map;
        let marker;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -34.397, lng: 150.644 },
                zoom: 8
            });

            marker = new google.maps.Marker({
                map: map
            });

            fetchDriverLocation();
            setInterval(fetchDriverLocation, 5000); // Refresh every 5 seconds
        }

        function fetchDriverLocation() {
            // This would be an API call to your backend to get the driver's current location
            // For demonstration, we'll use a static location
            const location = { lat: {{ $trip->driver->lat ?? -34.397 }}, lng: {{ $trip->driver->lng ?? 150.644 }} };

            marker.setPosition(location);
            map.setCenter(location);
        }

        google.maps.event.addDomListener(window, 'load', initMap);
    </script>
</body>
</html>
