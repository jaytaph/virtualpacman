<html>

<head>
    <style type="text/css" media="all">
    * {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
    }
    </style>

    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript" src="epoly.js"></script>
    <script type="text/javascript" src="jquery.js"></script>

    <script type="text/javascript">

        var MAX_TREASURE = 15;
        var treasures = [];
        var pacmanMarker;

        // Bounding box for the ghosts to randomly position themselves
        var bound_ne = new google.maps.LatLng(51.50388, 5.62323);
        var bound_sw = new google.maps.LatLng(51.51593, 5.65272);

        // Get random (fixed) number
        function getRandomInRange(from, to, fixed) {
            if (to < from) { tmp = to; to = from; from = tmp; }
            return (Math.random() * (to - from) + from).toFixed(fixed);
        }

        // Initialize and add a new ghost to a map.
        function addTreasure(map, i) {
            var position = new google.maps.LatLng(
                                    getRandomInRange(bound_sw.lat(), bound_ne.lat(), 4),
                                    getRandomInRange(bound_sw.lng(), bound_ne.lng(), 4)
                            );

            var value = getRandomInRange(10, 100) * 100;
            var t_radius = getRandomInRange(50, 500) * 1;

            // Create a new treasure and position it on the map
            var treasureImage = new google.maps.MarkerImage("treasure.gif",
                new google.maps.Size(15,15),
                new google.maps.Point(0,0),
                new google.maps.Point(7,7)
            );
            var treasureMarker = new google.maps.Marker({
                position: position,
                map: map,
                icon: treasureImage,
                title:"Treasure "+i+": Worth: " + value + " points",
                visible : false,
            });

            var circle = new google.maps.Circle();
            var circleOptions = {
                  strokeColor: "#0000FF",
                  strokeOpacity: 0.1,
                  strokeWeight: 2,
                  fillColor: "#0000FF",
                  fillOpacity: 0.02,
                  radius : t_radius,
                  map: map,
                  clickable: false,
                  visible : false,
            };
            circle.setOptions(circleOptions);
            circle.setCenter(position);

            // create treasure structure
            var treasure = {
                index          : i,
                circle         : circle,
                //radius         : circle.radius,
                value          : value,
                marker         : treasureMarker,
            }

            // Add treasure to our treasure list
            treasures.push(treasure);
        }

        function initialize() {
            // Create map (of aarle-rixtel)
            var latlng = new google.maps.LatLng(51.509823, 5.639977);
            var settings = {
                zoom: 15,
                center: latlng,
                mapTypeControl: true,
                mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
                navigationControl: true,
                navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var map = new google.maps.Map(document.getElementById("map_canvas"), settings);

              // Display bounding box
            rectangle = new google.maps.Rectangle();
            var rectOptions = {
                  strokeColor: "#FF0000",
                  strokeOpacity: 0.1,
                  strokeWeight: 2,
                  fillColor: "#FF0000",
                  fillOpacity: 0.02,
                  map: map,
                  bounds: new google.maps.LatLngBounds(bound_ne, bound_sw),
                  clickable : false,
            };
            rectangle.setOptions(rectOptions);



            // Add pacman marker in front of the church
            meImage = new google.maps.MarkerImage('pacman.gif',
                new google.maps.Size(15,15),
                new google.maps.Point(0,0),
                new google.maps.Point(7,7)
            );
            pacmanMarker = new google.maps.Marker({
                position: new google.maps.LatLng(51.50992, 5.63845),
                map: map,
                icon: meImage,
                title:"Yes, this is dog"
            });

            google.maps.event.addListener(map, 'click', function(event) {
                console.log("Moving PC to " + event.latLng);
                pacmanMarker.setPosition(event.latLng);

                for (i=0; i!=treasures.length; i++) {
                    var distance = treasures[i].circle.center.distanceFrom(pacmanMarker.position);
                    console.log("Distance from last position: " + distance);

                    if (distance > treasures[i].circle.radius) {
                        treasures[i].marker.setVisible(false);
                        treasures[i].circle.setVisible(false);
                        continue;
                    }

                    console.log("In range of treasure "+ i);
                    treasures[i].marker.setVisible(true);
                    treasures[i].circle.setVisible(true);
                }
            });



            // Add multiple ghosts
            for (var i=0; i < MAX_TREASURE; i++) {
                addTreasure(map, i);
            }
        }



    </script>
</head>

<body>

    <br>
    <center>
        <h1>Click and move around the field to find the treasures...</h1>
    <div id="map_canvas" style="border: 1px solid black; width:800px; height:500px"></div>
    </center>

    <script type="text/javascript">
        $(document).ready(function() { initialize(); });
    </script>
</body>
</html>

