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
    global $stations, $defaultStationWait, $defaultStartStationWait, $additionalTransferStationWait, $routeTimesFixing, $maxTimeLimit;

    $stationInfo = $stations[$stationID];
    $info = ["station" => $stationID, "direction" => $currentDirection];

    $arrivalTime = 0;

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

                        $arrivalTime += $routeTimeFix;
                        $departureTime += $routeTimeFix;
                        
                        break;
                    }
                }                
            }
        }
    }

    // Check is is schedule start or finish
    if ($routeInfo["startTime"] < $departureTime)
    {
        $info["arrival"] = $arrivalTime;
    }

    if ($arrivalTime < $routeInfo["endTime"])
    {
        $info["departure"] = $departureTime;
    }

    if (isArrivalFinalStation($stationID, $currentDirection, $minStationID, $maxStationID))
    {
        $info["isFinalStation"] = true;
    }    

    // Check arrival time less than endTime
    if ($arrivalTime <= $routeInfo["endTime"])
    {
        $infoArray[] = $info;
    }
    else
    {
        return $maxTimeLimit;
    }

    return array_key_exists("departure", $info) ? $info['departure'] : $departureTime;
}

function getRouteSchedule($lineTravelTime, $routeID) {
    global $routes, $stations, $defaultRoundtripDayTime, $defaultRoundtripEveningTime, $defaultStartStationWait, $maxTimeLimit;

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

    $prevStationArrival = null;
    while (($prevStationArrival == null || $prevStationArrival < $routeInfo['endTime'])) 
    {
        $currentDepartureTime = addStationPoint($infoArray, $routeID, $routeInfo, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);

        if ($currentDepartureTime == $maxTimeLimit)
        {
            break;
        }

        $prevStationArrival = count($infoArray) > 1 ? $infoArray[count($infoArray) - 1]["arrival"] : $infoArray[0]["departure"];

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
    }

    return $infoArray;
}