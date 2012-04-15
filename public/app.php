<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript" src="epoly.js"></script>

    <script type="text/javascript">
        var pacman;
        var ghosts = [];

        function getRandomInRange(from, to, fixed) {
            return (Math.random() * (to - from) + from).toFixed(fixed);
        }

        function addGhost(map, i) {
            var position = new google.maps.LatLng(getRandomInRange(51.50388, 51.51323, 4),getRandomInRange(5.60723, 5.65272, 4));
            console.log("Position created: "+position);

            var request = {
                origin:      position,
                destination: pacman.position,
                travelMode:  google.maps.DirectionsTravelMode.DRIVING
            };

            var directionsService = new google.maps.DirectionsService();
            directionsService.route(request, function(response, status) {
                console.log("STATUS: "+status);
                if (status == google.maps.DirectionsStatus.OK) {
                    var ghostImage = new google.maps.MarkerImage('ghost.gif',
                        new google.maps.Size(15,15),
                        new google.maps.Point(0,0),
                        new google.maps.Point(7,7)
                    );
                    var ghostMarker = new google.maps.Marker({
                        position: response.routes[0].legs[0].start_location,
                        map: map,
                        icon: ghostImage,
                        title:"Nr "+i+": Just haunting.."
                    });

                    var ghost = {
                        marker   : ghostMarker,
                        route    : response,
                        routepos : 0
                    }
                    ghosts.push(ghost);
                }
            });
        }

        function initialize() {
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
            pacman = meMarker;

            // Add ghosts
            for (var i=0; i<=5; i++) {
                addGhost(map, i);
            }

            setTimeout("moveGhosts()", 1000);
        }


        var ticker = 0;
        function moveGhosts() {
            console.log("Running step"+ticker)
            ticker += 1;


            for (i=0; i!=ghosts.length; i++) {
                ghosts[i].marker.setLatLng(ghosts[i].route.getStep(ghosts[i].routepos).getLatLng());
                ghosts[i].routepos++;

                var steptext = dirn.getRoute(0).getStep(stepnum).getDescriptionHtml();
                document.getElementById("step"+i).innerHTML = steptext;
            }

            setTimeout("moveGhosts()", 1000);
        }

    </script>
</head>

<body onload="initialize()">
    <table border=1>
        <tr><th colspan=2>Ghost status</th></tr>
        <tr><td><div id=distance1>Km: 0.00</div></td><td><div id="step1">&nbsp;</div></td></tr>
        <tr><td><div id=distance2>Km: 0.00</div></td><td><div id="step2">&nbsp;</div></td></tr>
        <tr><td><div id=distance3>Km: 0.00</div></td><td><div id="step3">&nbsp;</div></td></tr>
        <tr><td><div id=distance4>Km: 0.00</div></td><td><div id="step4">&nbsp;</div></td></tr>
        <tr><td><div id=distance5>Km: 0.00</div></td><td><div id="step5">&nbsp;</div></td></tr>
    </table>
    <div id="map_canvas" style="width:800px; height:500px"></div>
</body>
</html>
