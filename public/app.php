<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript" src="epoly.js"></script>

    <script type="text/javascript">

    /**
     * TODO: Add trailing polyline (blue) so we can see where all the ghosts already have been
     */

//function getNumSteps(route) {
//     var total_steps = 0;
//     for (var l=0; l<route.legs.length; l++) {
//         total_steps += route.legs[l].steps.length;
//     }
//     return total_steps;
// };

function getLeg(route, step) {
    for (var l=0; l<route.legs.length; l++) {
        for (var s=0; s<route.legs[l].steps.length; s++) {
            if (step == 0) {
                return route.legs[l];
            }
            step--;
        }
    }
    return null;
};

 function getStep(route, step) {
     for (var l=0; l<route.legs.length; l++) {
         for (var s=0; s<route.legs[l].steps.length; s++) {
             if (step <= 0) {
                 return route.legs[l].steps[s];
             }
             step--;
         }
     }
     return null;
 };

function getStepFromPolyNum(route, poly_num) {
    console.log("GSFP "+poly_num);
    for (l=0; l<route.legs.length; l++) {
        for (s=0; s<route.legs[l].steps.length; s++) {
            for (k=0; k<route.legs[l].steps[s].path.length; k++) {
                poly_num--;
                if (poly_num <= 0) {
                    return route.legs[l].steps[s];
                }
            }
        }
    }
    return null;
}

        var TICKER_TIME = 250;  // How many milliseconds between ghosts
        var MAX_GHOSTS = 5;     // Display how many ghosts on the map?

        var pacman;             // Pacman marker
        var ghosts = [];        // Ghost structure

        var bound_ne = new google.maps.LatLng(51.50388, 5.62323);
        var bound_sw = new google.maps.LatLng(51.51593, 5.65272);


        var ghostnames = ['pacman_ghost_b.gif',         // Different ghost names
                          'pacman_ghost_o.gif',
                          'pacman_ghost_p.gif',
                          'pacman_ghost_r.gif'];


        function getRandomInRange(from, to, fixed) {
            if (to < from) { tmp = to; to = from; from = tmp; }
            return (Math.random() * (to - from) + from).toFixed(fixed);
        }

        function createPolyLine(route) {
            var polyline = new google.maps.Polyline( { path: [], strokeColor: '#00FF00', strokeWeight: 1 });

            var path = route.overview_path;
            var legs = route.legs;
            for (i=0; i<legs.length; i++) {
                var steps = legs[i].steps;
                for (j=0; j<steps.length; j++) {
                    var nextSegment = steps[j].path;
                    for (k=0; k<nextSegment.length; k++) {
                        polyline.getPath().push(nextSegment[k]);
                    }
                }
            }
            return polyline;
        }

        function addGhost(map, i) {
            var position = new google.maps.LatLng(
                                    getRandomInRange(bound_sw.lat(), bound_ne.lat(), 4),
                                    getRandomInRange(bound_sw.lng(), bound_ne.lng(), 4)
                            );

            var ghostImage = new google.maps.MarkerImage(ghostnames[i % ghostnames.length],
                new google.maps.Size(15,15),
                new google.maps.Point(0,0),
                new google.maps.Point(7,7)
            );
            var ghostMarker = new google.maps.Marker({
                position: position,
                map: map,
                icon: ghostImage,
                title:"Nr "+i+": Just haunting.."
            });


            // create ghost structure
            var ghost = {
                index        : i,
                active       : false,
                calc_count   : 0,           // How many times did we calculated a route for this ghost
                marker       : ghostMarker,     // The actual marker
                total_dist   : 0,
                speed        : 10,               // Initial speed
                total_distance : 0          // The total distance for ALL routes
            }

            // Add ghost to our ghost list
            ghosts.push(ghost);

            // Recalculate
            recalc(ghost, pacman)
        }

        function recalc(ghost, pacman) {
            ghost.recalc = false;

            var request = {
                origin:      ghost.marker.position,
                destination: pacman.marker.position,
                travelMode:  google.maps.DirectionsTravelMode.DRIVING,
                provideRouteAlternatives : true,
            };

            var directionsService = new google.maps.DirectionsService();
            directionsService.route(request, function(response, status) {
                if (status == google.maps.DirectionsStatus.OK) {


                    if (ghost.poly != null) {
                        // Remove polyline first
                        ghost.poly.setMap(null);
                    }

                    // Pick a random route
                    r = getRandomInRange (0, response.routes.length-1);
                    console.log("RL: "+response.routes.length);
                    console.log("RP: "+r);

                    route = response.routes[r];

                    // Create polyline from the response and show it on the map
                    poly = createPolyLine(route);
                    poly.setMap(ghost.marker.map);


                    // Add info to ghost
                    ghost.calc_count += 1;
                    ghost.active = true;
                    ghost.dirn = route;                  // Route
                    ghost.poly = poly;                   // Gmaps Polyline
                    ghost.total_dist = poly.Distance();  // Precalled polyline distance (KMs)
                    ghost.distance = 0;                  // Distance travelled
                    ghost.poly_num = 0;                  // The current polygon path we are travelling

                    var steptext = ghost.dirn.summary;
                    document.getElementById("step"+i).innerHTML = steptext;

                    document.getElementById("distance"+i).innerHTML = "Km: 0.00";
                    document.getElementById("left"+i).innerHTML = "Km: " + (ghost.total_dist / 1000).toFixed(2);
                } else {
                    console.log("STATUS: "+status);
                }
            });
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
//            rectangle = new google.maps.Rectangle();
//            var rectOptions = {
//                  strokeColor: "#FF0000",
//                  strokeOpacity: 0.2,
//                  strokeWeight: 2,
//                  fillColor: "#FF0000",
//                  fillOpacity: 0.05,
//                  map: map,
//                  bounds: new google.maps.LatLngBounds(bound_ne, bound_sw)
//            };
//            rectangle.setOptions(rectOptions);


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
                movePacman(event.latLng);
            });

            // Add multiple ghosts
            for (var i=0; i < MAX_GHOSTS; i++) {
                addGhost(map, i);
            }

            // Move the ghosts
            setTimeout("moveGhosts()", 2000);
        }


    function movePacman(LatLng) {
        pacman.marker.setPosition(LatLng);

        // Recalculate all the ghosts
        for (i=0; i!=ghosts.length; i++) {
            ghosts[i].recalc = true;
        }
    }


        var ticker = 0;
        function moveGhosts() {
            console.log("Running tick "+ticker)
            ticker += 1;

            // Iterate all ghosts
            for (i=0; i!=ghosts.length; i++) {
                ghost = ghosts[i];

                // Recalculate ghost if needed
                if (ghost.recalc) {
                    recalc(ghost, pacman);
                }

                // Not an active ghost, so don't do anything
                if (ghost.active == false) {
                    continue;
                }

                // Check if we travelled the whole distance, deactivate ghost if so
                if (ghost.distance > ghost.total_dist) {
                    // Ghost is done
                    document.getElementById("step"+i).innerHTML = "<b>Trip completed</b>";
                    document.getElementById("distance"+i).innerHTML =  "Km: "+(ghost.distance / 1000).toFixed(2);
                    document.getElementById("left"+i).innerHTML = "Km: " + ((ghost.total_dist-ghost.distance) / 1000).toFixed(2);

                    // Deactivate ghost, since we have arrived at destination
                    ghost.active = false;
                    continue;
                }


                // Set new marker position
                var p = ghost.poly.GetPointAtDistance(ghost.distance);
                ghost.marker.setPosition(p);

                // Get current step and update info
                var step = getStepFromPolyNum(ghost.dirn, ghost.poly_num);
                document.getElementById("step"+i).innerHTML = "STEP: "+ghost.poly_num+" "+step.instructions;

                // Check if we are on the next step, if so, set info
                var new_poly_num = ghost.poly.GetIndexAtDistance(ghost.distance);
                if (ghost.poly_num < new_poly_num) {
                    ghost.poly_num = new_poly_num;
                    //ghost.poly_num++;

                    var prevstep = getStepFromPolyNum(ghost.dirn, ghost.poly_num-1);
                    if (prevstep != null) {
                        var stepdist = prevstep.distance.value;
                        var steptime = prevstep.duration.value;
                        var stepspeed = ((stepdist/steptime) * 1).toFixed(2);

                        console.log("DIST "+stepdist);
                        console.log("TIME"+steptime);
                        console.log("SPEED "+stepspeed);

                        //ghost.step = stepspeed / 2.5;
                        console.log("GS "+ghost.speed);

                        ghost.speed = (stepspeed / 1);
                    }
                }

                // Increment distance with the current speed
                ghost.distance += ghost.speed;
                ghost.total_distance += ghost.speed;

                document.getElementById("distance"+i).innerHTML =  "Km: "+((ghost.distance + ghost.speed) / 1000).toFixed(2);
                document.getElementById("left"+i).innerHTML = "Km: " + ((ghost.total_dist-ghost.distance) / 1000).toFixed(2);
                document.getElementById("speed"+i).innerHTML = ((ghost.speed) * 1).toFixed(2) + " Km/h";
            }

            setTimeout("moveGhosts()", TICKER_TIME);
        }

    </script>
</head>

<body onload="initialize()">
    <table border=1 id="ghosttable" width=100%>
        <tr><th colspan=5>Ghost status</th></tr>
        <tr><td>Ghost #1</td><td><div id=speed0>0.00 Km/h</div></td><td><div id=distance0>Km: 0.00</div></td><td><div id=left0>Km: 0.00</div></td><td nowrap width=75%><div id="step0">&nbsp;</div></td></tr>
        <tr><td>Ghost #2</td><td><div id=speed1>0.00 Km/h</div></td><td><div id=distance1>Km: 0.00</div></td><td><div id=left1>Km: 0.00</div></td><td nowrap width=75%><div id="step1">&nbsp;</div></td></tr>
        <tr><td>Ghost #3</td><td><div id=speed2>0.00 Km/h</div></td><td><div id=distance2>Km: 0.00</div></td><td><div id=left2>Km: 0.00</div></td><td nowrap width=75%><div id="step2">&nbsp;</div></td></tr>
        <tr><td>Ghost #4</td><td><div id=speed3>0.00 Km/h</div></td><td><div id=distance3>Km: 0.00</div></td><td><div id=left3>Km: 0.00</div></td><td nowrap width=75%><div id="step3">&nbsp;</div></td></tr>
        <tr><td>Ghost #5</td><td><div id=speed4>0.00 Km/h</div></td><td><div id=distance4>Km: 0.00</div></td><td><div id=left4>Km: 0.00</div></td><td nowrap width=75%><div id="step4">&nbsp;</div></td></tr>
    </table>
    <div id="map_canvas" style="width:800px; height:500px"></div>
</body>
</html>

