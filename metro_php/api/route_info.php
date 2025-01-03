<?php

require_once 'data.php';
require_once 'utils.php';

// Функции
function isEveningTime($time) {
    global $eveningStartTime;
    return $time > $eveningStartTime;
}

function isArrivalFinalStation($stationIndex, $currentDirection, $maxStationIndex) {
    return ($stationIndex === 0 && $currentDirection < 0) ||
           ($stationIndex === $maxStationIndex && $currentDirection > 0);
}

function isDepartureFromStartStation($stationIndex, $currentDirection, $maxStationIndex) {
    return ($stationIndex === 0 && $currentDirection > 0) ||
           ($stationIndex === $maxStationIndex && $currentDirection < 0);
}

function addStationPoint($lineID, &$infoArray, $routeID, $routeInfo, $stationIndex, $departureTime, $currentDirection, $maxStationIndex)
{
    global $stations, $defaultStationWait, $defaultStartStationWait, $additionalTransferStationWait, 
        $routeTimesFixing, $maxTimeLimit, $transportLines;

    $stationInfo = $stations[$transportLines[$lineID]["stations"][$stationIndex]];
    $info = ["lineStationIndex" => $stationIndex, "direction" => $currentDirection];

    $arrivalTime = 0;

    // Calculate arrival time
    if (isArrivalFinalStation($stationIndex, $currentDirection, $maxStationIndex) ||
            isDepartureFromStartStation($stationIndex, $currentDirection, $maxStationIndex))
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

    // Check is schedule start or finish
    if ($routeInfo["startTime"] < $departureTime)
    {
        $info["arrival"] = $arrivalTime;

        if (array_key_exists("finalTime", $routeInfo))
        {
            if ($arrivalTime >= $routeInfo["finalTime"])
            {
                $info["landingProhibited"] = true;
            }
        }
    }

    if ($arrivalTime < $routeInfo["endTime"])
    {
        $info["departure"] = $departureTime;
    }

    if (isArrivalFinalStation($stationIndex, $currentDirection, $maxStationIndex))
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

function getRouteSchedule($lineTravelTime, $routeID) 
{
    global $routes, $stations, $defaultRoundtripDayTime, $defaultRoundtripEveningTime, 
        $defaultStartStationWait, $maxTimeLimit, $linesTravelTime, $transportLines;

    $infoArray = [];
    $routeInfo = $routes[$routeID] ?? null;

    if ($routeInfo === null) {
        return $infoArray;
    }

    $currentStationIndex = $routeInfo['startStationIndex'];
    $currentDepartureTime = $routeInfo['startTime'];
    $currentDirection = $routeInfo['direction'];

    $lineID = $routeInfo["lineID"];

    if (!array_key_exists($lineID, $transportLines))
    {
        logError("getRouteSchedule", "Unknown line", "routeID: $routeID, lineID: $lineID");
        return $infoArray;
    }

    $maxStationIndex = count($transportLines[$lineID]["stations"]) - 1;

    if (!array_key_exists($lineID, $linesTravelTime))
    {
        logError("getRouteSchedule", "Unable to find line travel times for specified route", "routeID: $routeID, lineID: $lineID");

        return $infoArray;
    }

    if (count($linesTravelTime[$lineID]['normal']) != $maxStationIndex)
    {
        logError("getRouteSchedule", "Travel time is inconsistent", "Travel times count: {($linesTravelTime[$lineID]['normal'] + 1)}, max station index: $maxStationIndex");

        return $infoArray;
    }

    if (array_key_exists('reverse', $linesTravelTime[$lineID]))
    {
        if (count($linesTravelTime[$lineID]['reverse']) != $maxStationIndex)
        {
            logError("getRouteSchedule", "Travel reverse time is inconsistent", "Travel times count: {($linesTravelTime[$lineID]['reverse'] + 1)}, max station index: $maxStationIndex");
    
            return $infoArray;
        }        
    }


    $prevStationArrival = null;
    while (($prevStationArrival == null || $prevStationArrival < $routeInfo['endTime'])) 
    {
        $currentDepartureTime = addStationPoint($lineID, $infoArray, $routeID, $routeInfo, $currentStationIndex, $currentDepartureTime, $currentDirection, $maxStationIndex);

        if ($currentDepartureTime == $maxTimeLimit)
        {
            break;
        }

        $prevStationArrival = count($infoArray) > 1 ? $infoArray[count($infoArray) - 1]["arrival"] : $infoArray[0]["departure"];

        if (isArrivalFinalStation($currentStationIndex, $currentDirection, $maxStationIndex)) {
            $isEvening = isEveningTime($currentDepartureTime);
            $travelTime = $isEvening ? ($stations[$currentStationIndex]['roundtripEveningTime'] ?? $defaultRoundtripEveningTime)
                                     : ($stations[$currentStationIndex]['roundtripDayTime'] ?? $defaultRoundtripDayTime);
            $travelTime -= $defaultStartStationWait;
            $currentDirection *= -1;
        } else 
        {
            $lineTravelTimeDirection = $currentDirection > 0 || !array_key_exists("reverse", $lineTravelTime) ? $lineTravelTime["normal"] : $lineTravelTime["reverse"];

            $travelTime = $lineTravelTimeDirection[$currentStationIndex + ($currentDirection > 0 ? $currentDirection : 0) - 1];
            $currentStationIndex += $currentDirection;
        }

        $currentDepartureTime += $travelTime;
    }

    return $infoArray;
}