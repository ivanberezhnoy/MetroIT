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

function addStationPoint(&$infoArray, $routeID, $stationID, $departureTime, $currentDirection, $minStationID, $maxStationID) {
    global $stations, $defaultStationWait, $defaultStartStationWait, $additionalTransferStationWait, $routeTimesFixing;

    $stationInfo = $stations[$stationID];
    $info = ["station" => $stationID, "direction" => $currentDirection];

    if (isArrivalFinalStation($stationID, $currentDirection, $minStationID, $maxStationID)) {
        $info['arrival'] = $departureTime - $defaultStartStationWait;
    } else {
        $info['departure'] = $departureTime;

        if (isDepartureFromStartStation($stationID, $currentDirection, $minStationID, $maxStationID)) {
            $info['arrival'] = $departureTime - $defaultStartStationWait;
        } else {
            $info['arrival'] = $departureTime - $defaultStationWait;

            if (!empty($stationInfo['isTransferStation'])) {
                $info['arrival'] -= $additionalTransferStationWait;
            }
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

function getRouteSchedule($routeID) {
    global $routes, $stations, $timesToAdd, $defaultRoundtripDayTime, $defaultRoundtripEveningTime, $defaultStartStationWait;

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
        addStationPoint($infoArray, $routeID, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);

        if (isArrivalFinalStation($currentStation, $currentDirection, $minStationID, $maxStationID)) {
            $isEvening = isEveningTime($currentDepartureTime);
            $travelTime = $isEvening ? ($stations[$currentStation]['roundtripEveningTime'] ?? $defaultRoundtripEveningTime)
                                     : ($stations[$currentStation]['roundtripDayTime'] ?? $defaultRoundtripDayTime);
            $travelTime -= $defaultStartStationWait;
            $currentDirection *= -1;
        } else {
            $travelTime = $timesToAdd[$currentStation + ($currentDirection > 0 ? $currentDirection : 0) - $minStationID - 1];
            $currentStation += $currentDirection;
        }

        $travelFixTime = 0;

        $currentDepartureTime += $travelTime;

        if ($currentDepartureTime > $routeInfo['endTime']) {
            addStationPoint($infoArray, $routeID, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);
        }
    }

    return $infoArray;
}