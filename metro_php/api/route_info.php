<?php

require_once 'data.php';

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

function addStationPoint(&$infoArray, $stationID, $departureTime, $currentDirection, $minStationID, $maxStationID) {
    global $stations, $defaultStationWait, $defaultStartStationWait, $additionalTransferStationWait;

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

    while ($currentDepartureTime <= $routeInfo['endTime']) {
        addStationPoint($infoArray, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);

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

        $currentDepartureTime += $travelTime;

        if ($currentDepartureTime > $routeInfo['endTime']) {
            addStationPoint($infoArray, $currentStation, $currentDepartureTime, $currentDirection, $minStationID, $maxStationID);
        }
    }

    return $infoArray;
}

/// Пример вызова
//$routeInfo = getRouteSchedule(1);
//print_r($routeInfo);