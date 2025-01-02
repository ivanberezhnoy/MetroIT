<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once 'data.php';
require_once 'route_info.php';
require_once 'utils.php';

// Получаем параметр "action"
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    case 'getLinesRoutesSchedule':
        getLinesRoutesSchedule();
        break;
    case 'getRoutes':
        getRoutes();
        break;
    case 'getStations':
        getStations();
        break;
    case 'getLines':
        getLines();
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        http_response_code(400); // Bad Request
        break ;
}
function getLines()
{
    global $transportLines;

    echo json_encode($transportLines);
}

// First key lineID, next key routeID, and array of schedule points
// The schedule point contains stationIndex is the index of station in $transportLines['stations']
function getLinesRoutesSchedule() 
{
    global $routes, $linesTravelTime, $transportLines;
    $result = [];

    foreach ($routes as $routeID => $routeInfo) 
    {
        $routeSchedule = getRouteSchedule($linesTravelTime[1], $routeID);

        $routeLineID = $routes[$routeID]["lineID"];

        if (!array_key_exists($routeLineID, $transportLines))
        {
            logError("getLinesRoutesSchedule", "Unable to find route line", "routeID: $routeID, lineID: $routeLineID");

            continue;
        }

        $result[$routeLineID][$routeID] = $routeSchedule;
    }

    echo json_encode($result);
}

function getRoutes() 
{
    global $routes;

    echo json_encode($routes);
        
    // $body = file_get_contents('php://input');
    // $data = json_decode($body, true);

    // if (!isset($data['name']) || !isset($data['age'])) 
    // {
    //     echo json_encode(['error' => 'Invalid data']);
    //     http_response_code(400);
    //     return;
    // }

    // // Эмуляция создания пользователя
    // echo json_encode(['status' => 'success', 'message' => 'User created', 'data' => $data]);
}

function getStations()
{
    global $stations;
    echo json_encode($stations);
}

function deleteUser() {
    // Эмуляция удаления пользователя
    $userId = $_POST['id'] ?? null;

    if (!$userId) {
        echo json_encode(['error' => 'User ID not provided']);
        http_response_code(400);
        return;
    }

    echo json_encode(['status' => 'success', 'message' => "User with ID $userId deleted"]);
}

?>