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
    <script type="text/javascript" src="jquery.js"></script>
</head>

<body>
    <center>
    <div id="map_canvas" style="border: 1px solid black; width:800px; height:500px"></div>
    </center>

    <script type="text/javascript">
        $(document).ready(function(){

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

            // Add pacman marker in front of the church
            var meImage = new google.maps.MarkerImage('pacman.gif',
                new google.maps.Size(15,15),
                new google.maps.Point(0,0),
                new google.maps.Point(7,7)
            );
            var meMarker = new google.maps.Marker({
                position: new google.maps.LatLng(51.50992, 5.63845),
                map: map,
                icon: meImage,
                title:"Yes, this is dog"
            });

            pacman = {
                marker : meMarker,
            }

            google.maps.event.addListener(map, 'click', function(event) {

                event.latLng
                pacman.marker.setPosition(event.latLng);
//                pacman.marker.map.setCenter(event.latLng);
                var gps_url = "http://" + window.location.hostname + "/gpspos.php?long="+event.latLng.lng()+"&lat="+event.latLng.lat();
                console.log(gps_url);
                $.get(gps_url);

            });

        });
    </script>
</body>
</html>

