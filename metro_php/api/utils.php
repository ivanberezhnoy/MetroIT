<?php

function logError($functionName, $errorText, $description) 
{

}

function implodeMap($map)
{
    $resultParts = [];
    foreach ($map as $key => $value) {
        $resultParts[] = "$key: $value";
    }
    
    return implode(", ", $resultParts);    
}

function formatTimeVal($timeVal)
{
    $res = strval($timeVal);

    if ($timeVal < 10) {
        $res = "0" . $res;
    }

    return $res;
}

function formatTime($time)
{
    $secondsInHour = 3600;
    $secondsInMinute = 60;

    $hours = intdiv($time, $secondsInHour);
    $minutes = intdiv($time % $secondsInHour, $secondsInMinute);
    $seconds = $time - $hours * $secondsInHour - $secondsInMinute * $minutes;

    return formatTimeVal($hours) . ':' . formatTimeVal($minutes) . ':' . formatTimeVal($seconds);
}
?>