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
?>