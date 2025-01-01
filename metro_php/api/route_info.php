<?php

require_once 'data.php';
require_once 'utils.php';

// Функции
function isEveningTime($time) {
    global $eveningStartTime;
    return $time > $eveningStartTime;
}

function isArrivalFinalStation($stationID, $currentDirection, $minStationID, $maxStationID) {
    return ($stationID === $minStationID && $currentDirection < 0) ||
           ($stationID === $maxStationID && $currentDirection > 0);
}

function isDepartureFromStartStation($stationID, $currentDirection, $minStationID, $maxStationID) {
    return ($stationID === $minStationID && $currentDirection > 0) ||
           ($stationID === $maxStationID && $currentDirection < 0);
}

function addStationPoint(&$infoArray, $routeID, $routeInfo, $stationID, $departureTime, $currentDirection
            , $minStationID, $maxStationID)
{
    global $stations, $defaultStationWait, $defaultStartStationWait, $additionalTransferStationWait, $routeTimesFixing;

    $stationInfo = $stations[$stationID];
    $info = ["station" => $stationID, "direction" => $currentDirection];

    // Calculate arrival time
    if (isArrivalFinalStation($stationID, $currentDirection, $minStationID, $maxStationID) ||
            isDepartureFromStartStation($stationID, $currentDirection, $minStationID, $maxStationID))
    {
        $arrivalTime = $departureTime - $defaultStartStationWait;
    }
    else
    {
        $arrivalTime = $departureTime - $defaultStationWait;

        if (!empty($stationInfo['isTransferStation'])) 
        {
            $arrivalTime -= $additionalTransferStationWait;
        }        
    }

    // Check is is schedule start or finish
    if ($routeInfo["startTime"] < $arrivalTime)
    {
        $info["arrival"] = $arrivalTime;
    }

    if ($departureTime < $routeInfo["endTime"])
    {
        $info["departure"] = $departureTime;
    }

    if (isArrivalFinalStation($stationID, $currentDirection, $minStationID, $maxStationID))
    {
        $info["isFinalStation"] = true;
    }

    // Apply routeTimesFixing
    if (count($infoArray) > 0)
    {
        $previousStationDeparture = $infoArray[count($infoArray) - 1]['departure'];
        if ($previousStationDeparture == null)
        {
            logError("addStationPoint", "Unable to find previous station departure time", implodeMap($infoArray[count($infoArray) - 1]));
        }
        else
        {
            $routeFixes = array_key_exists($routeID, $routeTimesFixing) ? $routeTimesFixing[$routeID] : null;

            if ($routeFixes != null)
            {
                foreach ($routeFixes as $routeFix) 
                {
                    if ($routeFix["departure"] == $previousStationDeparture)
                    {
                        $routeTimeFix = $routeFix["timeShift"];

                        if (array_key_exists('arrival', $info))
                        {
                            $info['arrival'] += $routeTimeFix;
                        }
                        if (array_key_exists('departure', $info))
                        {
                            $info['departure'] += $routeTimeFix;
                        }                        
                    }
                }                
            }
        }
    }

    $infoArray[] = $info;
}

function getRouteSchedule($lineTravelTime, $routeID) {
    global $routes, $stations, $defaultRoundtripDayTime, $defaultRoundtripEveningTime, $defaultStartStationWait;

    $infoArray = [];
    $routeInfo = $routes[$routeID] ?? null;

    if ($routeInfo === null) {
        return $infoArray;
    }

    $currentStation = $routeInfo['startStation'];
    $currentDepartureTime = $routeInfo['startTime'];
    $currentDirection = $routeInfo['direction'];
    $maxStationID = count($stations);
    $minStationID = 1;

    while ($currentDepartureTime <= $routeInfo['endTime']) 
    {
        addStationPoint($infoArray, $routeID, $routeInfo, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);

        if (isArrivalFinalStation($currentStation, $currentDirection, $minStationID, $maxStationID)) {
            $isEvening = isEveningTime($currentDepartureTime);
            $travelTime = $isEvening ? ($stations[$currentStation]['roundtripEveningTime'] ?? $defaultRoundtripEveningTime)
                                     : ($stations[$currentStation]['roundtripDayTime'] ?? $defaultRoundtripDayTime);
            $travelTime -= $defaultStartStationWait;
            $currentDirection *= -1;
        } else 
        {
            $lineTravelTimeDirection = $currentDirection > 0 ? $lineTravelTime["normal"] : $lineTravelTime["reverse"];

            $travelTime = $lineTravelTimeDirection[$currentStation + ($currentDirection > 0 ? $currentDirection : 0) - $minStationID - 1];
            $currentStation += $currentDirection;
        }

        $currentDepartureTime += $travelTime;

        if ($currentDepartureTime > $routeInfo['endTime']) {
            addStationPoint($infoArray, $routeID, $routeInfo, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);
        }
    }

    return $infoArray;
}