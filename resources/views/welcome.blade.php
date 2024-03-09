<!DOCTYPE html>
<html>
<head>
  <title>Display Specific Area on Google Maps</title>
  <style>
    *{
        margin: 0;
        padding: 0;
    }
    #map {
      height: 600px;
      width: 100%;
    }

    .distinate{
       margin: 15px;
       box-sizing: border-box;
       background-color: #ddd;
       display: flex;
       justify-content: space-between
    }
  </style>
</head>
<body>
   <div id="map"></div>

    <div class="distinate">
        <div>
          <h3>Location: </h3>
          <table border="1">
              <tr>
                <th>Stating Point</th>
                <td><span id="starting_point"></span></td>
              </tr>
              <tr>
                <th>End Point</th>
                <td><span id="end_point"></span></td>
              </tr>
              <tr>
                  <th>Distance</th>
                  <td><span id="distance"></span></td>
              </tr>
              <tr>
                <th>Duration</th>
                <td><span id="duration"></span></td>
              </tr>
          </table>

        </div>
       <div>
        <h3>Current Trac Location: </h3>
        <table border="1">
          <tr>
            <th>Current Point</th>
            <td><span id="current_starting_point"></span></td>
          </tr>
          <tr>
            <th>End Point</th>
            <td><span id="current_end_point"></span></td>
          </tr>
          <tr>
              <th>Distance</th>
              <td><span id="current_distance"></span></td>
          </tr>
          <tr>
            <th>Duration</th>
            <td><span id="current_duration"></span></td>
          </tr>
       </div>
      </table>
    </div>
   <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   <script>

      function getCurrentLocation(){
          const response = axios();
      }

      function getStartingPointLocation(){
          const response = axios();
      }

      function getEndPointLocation(){
          const response = axios();
      }

      function initMap() {
        const map = new google.maps.Map(document.getElementById("map"), {
          zoom: 7,
          center: { lat: 24.24, lng: 90.35 },
          disableDefaultUI: true,
        });

        const start = { lat: 26.335401001893853, lng: 88.5516497170625 };
        const end = { lat: 23.806578261363796, lng: 90.41281899152386 };

        const myLatLng = { lat: 25.342227, lng: 89.107127 };
        const myLatLng2 = { lat: 25.427885, lng: 88.977768 };


        const defaultColor = '#19C37D';
        const zIndex = 1111;

        calculateAndDisplayRoute(map, start, end, defaultColor, zIndex);

        const calTime = calculateTime();

        if (calTime < 0) {
          const ltColor = '#FF0000';
          const zIndex = 9999;
          calculateAndDisplayRoute(map, myLatLng2, end, ltColor, zIndex);
        }


        const control = document.getElementById("floating-panel");
        map.controls[google.maps.ControlPosition.TOP_CENTER].push(control);

        getAddress(start, function(address) {
            document.getElementById('starting_point').innerHTML = address;
        });

        getAddress(end, function(address) {
          document.getElementById('end_point').innerHTML = address;
        });

        getAddress(myLatLng2, function(address) {
            document.getElementById('current_starting_point').innerHTML = address;
        });

        getAddress(end, function(address) {
          document.getElementById('current_end_point').innerHTML = address;
        });

        calculateDistance(map, start, end, function(results){
            const distance = results[0].distance.text;
            const duration = results[0].duration.text;
            document.getElementById('distance').innerHTML = distance;
            document.getElementById('duration').innerHTML = duration;
        })

        calculateDistance(map, myLatLng2, end, function(results){
            const distance = results[0].distance.text;
            const duration = results[0].duration.text;
            document.getElementById('current_distance').innerHTML = distance;
            document.getElementById('current_duration').innerHTML = duration;
        })

        setMyTrack(map, myLatLng2);
      }

      function calculateDistance(map, origin, destination, callback)
      {
          const service = new google.maps.DistanceMatrixService();
          const request = {
              origins: [origin],
              destinations: [destination],
              travelMode: google.maps.TravelMode.DRIVING,
              unitSystem: google.maps.UnitSystem.METRIC,
              avoidHighways: false,
              avoidTolls: false,
          };

          service.getDistanceMatrix(request).then((response) => {
            const originList = response.originAddresses;
            const destinationList = response.destinationAddresses;
            for (let i = 0; i < originList.length; i++) {
              const results = response.rows[i].elements;
              callback(results);
            }
          });
      }

      function getAddress(latLng, callback) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: latLng }, (results, status) => {
          if (status === "OK") {
            if (results[0]) {
              const address = results[0].formatted_address;
              callback(address);
            } else {
              callback("No results found");
            }
          } else {
            callback("Geocoder failed due to: " + status);
          }
        });
      }

      function deleteMarkers(markersArray) {
        for (let i = 0; i < markersArray.length; i++) {
          markersArray[i].setMap(null);
        }
        markersArray = [];
      }


      function calculateAndDisplayRoute(map, start, end, color, zIndex) {
        const directionsRenderer = new google.maps.DirectionsRenderer();
        const directionsService = new google.maps.DirectionsService();

        const bluePolylineOptions = {
          strokeColor: color,
          strokeWeight: 4,
          zIndex: zIndex
        };

        directionsRenderer.setOptions({ polylineOptions: bluePolylineOptions });
        directionsRenderer.setMap(map);

        directionsService
          .route({
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING,
          })
          .then((response) => {
            directionsRenderer.setDirections(response);
          })
          .catch((e) => window.alert("Directions request failed due to " + e));
      }



      function setMyTrack(map, myLatLng){
        new google.maps.Marker({
          position: myLatLng,
          map,
          title: "Track Name",
          zIndex: 99999,
          icon: "{{ asset('assets/car.png') }}"
        });
      }

      function calculateTime(){
          // const lt_count = '1440';
          const lt_count = '100';
          const startTime = '2024-03-09 09:20:40';
          const convertedTime = convertToTime(startTime);
          const d_minute = getMinuteCount(startTime);
          const lt_minute = (lt_count - d_minute);
          return lt_minute;
      }

      function convertToTime(timeString){
          var startTimeString = new Date(timeString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
          return startTimeString;
      }

      function getMinuteCount(startTime) {
        var startTimeStamp = new Date(startTime).getTime();
        var currentTimeStamp = new Date().getTime();
        var timeDifference = currentTimeStamp - startTimeStamp;
        var totalMinutes = Math.floor(timeDifference / (1000 * 60));
        return totalMinutes;
      }

      window.initMap = initMap;

   </script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD37xTUmtnOlDT23hpE_XoFYByzshd6rK8&callback=initMap" async defer></script>
</body>
</html>
