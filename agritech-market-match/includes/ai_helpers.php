<?php
function getPriceTrendSummary($crop) {
 
    $trends = [
        'Maize' => "Prices rising due to high demand in Nairobi.",
        'Tomatoes' => "Prices dropping in Kisumu (new harvest).",
        'Beans' => "Stable prices expected this week."
    ];
    return $trends[$crop] ?? "No trend data available.";
}


echo getPriceTrendSummary('Maize');
?>