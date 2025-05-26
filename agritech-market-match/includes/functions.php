<?php
function getDistance($location1, $location2) {
   
    $locations = ['Nairobi', 'Kisumu', 'Mombasa'];
    if ($location1 == $location2) return 0;
    return rand(10, 200); 
}
?>