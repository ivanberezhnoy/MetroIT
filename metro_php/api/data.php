<?php

// Константы
define('SECONDS_IN_MINUTE', 60);
define('MINUTES_IN_HOUR', 60);
define('SECONDS_IN_HOUR', SECONDS_IN_MINUTE * MINUTES_IN_HOUR);

$eveningStartTime = 20 * SECONDS_IN_HOUR; // Время перехода на вечерний график
$defaultStationWait = 20; // Время ожидания на станции
$defaultStartStationWait = $defaultStationWait; // Время ожидания на начальной станции
$additionalTransferStationWait = 10; // Дополнительное время ожидания на пересадочных станциях
$defaultRoundtripDayTime = 300; // Время оборота в дневной период
$defaultRoundtripEveningTime = 600; // Время оборота в вечерний период

$maxTimeLimit = 48 * SECONDS_IN_HOUR;

// Данные станций
$stations = [
    1 => ["name" => "Індустріальна", "roundtripDayTime" => $defaultRoundtripDayTime, "roundtripEveningTime" => $defaultRoundtripEveningTime],
    2 => ["name" => "Тракторний завод"],
    3 => ["name" => "Масельского"],
    4 => ["name" => "Армійська"],
    5 => ["name" => "Палац спорту"],
    6 => ["name" => "Турбоатом"],
    7 => ["name" => "Заводська"],
    8 => ["name" => "Спортивна", "isTransferStation" => true],
    9 => ["name" => "Левада"],
    10 => ["name" => "м-д Констітуції", "isTransferStation" => true],
    11 => ["name" => "Центр ринок"],
    12 => ["name" => "Вокзальна"],
    13 => ["name" => "Холодна гора"]
];

// Данные маршрутов "endTime" - время прибытия на конечную, "startTime" - время отправления с начальной станции
$routes = [
    1 => ["startStation" => 1, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 45 * SECONDS_IN_MINUTE), "endTime" => (20 * SECONDS_IN_HOUR + 15 * SECONDS_IN_MINUTE)],
    2 => ["startStation" => 13, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 40 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 10 * SECONDS_IN_MINUTE)],
    3 => ["startStation" => 13, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 50 * SECONDS_IN_MINUTE), "endTime" => (20 * SECONDS_IN_HOUR + 20 * SECONDS_IN_MINUTE)],
    4 => ["startStation" => 6, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 20 * SECONDS_IN_MINUTE)],
    5 => ["startStation" => 1, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 35 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 22 * SECONDS_IN_MINUTE)],
    6 => ["startStation" => 13, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 31 * SECONDS_IN_MINUTE), "endTime" => (21 * SECONDS_IN_HOUR + 50 * SECONDS_IN_MINUTE)],
    7 => ["startStation" => 6, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 2 * SECONDS_IN_MINUTE)]
];

// Исправление времени движения маршрутов
// Ключ - номер маршрута: время отправления со станции, сдвиг по времени движения до след станции
$routeTimesFixing = [
    4 => 
        [
            ["departure" => 19 * SECONDS_IN_HOUR + 15 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => 60] // Холодная гора
            , ["departure" => 19 * SECONDS_IN_HOUR + 51 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -60] // Індустріальна  
            , ["departure" => 20 * SECONDS_IN_HOUR + 25 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -300] // Холодная гора
        ]
    ,5 => 
        [
            ["departure" => 20 * SECONDS_IN_HOUR + 45 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -300] // Індустріальна
            , ["departure" => 21 * SECONDS_IN_HOUR + 20 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => 300] // Холодная гора
            , ["departure" => 22 * SECONDS_IN_HOUR + 05 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -360] // Індустріальна
        ]        
    ,6 => 
        [
            ["departure" => 6 * SECONDS_IN_HOUR + 01 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -60] // Індустріальна
            ,["departure" => 20 * SECONDS_IN_HOUR + 0 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -300] // Індустріальна
            ,["departure" => 21 * SECONDS_IN_HOUR + 15 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -300] // Індустріальна
        ]
    ,7 => 
        [ // 1-й поезд
            ["departure" => 5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE + 0, "timeShift" => 20]
            ,["departure" => 5 * SECONDS_IN_HOUR + 39 * SECONDS_IN_MINUTE + 55, "timeShift" => -10]
            ,["departure" => 5 * SECONDS_IN_HOUR + 44 * SECONDS_IN_MINUTE + 40, "timeShift" => 25]
            ,["departure" => 5 * SECONDS_IN_HOUR + 47 * SECONDS_IN_MINUTE + 50, "timeShift" => 5]
            ,["departure" => 5 * SECONDS_IN_HOUR + 52 * SECONDS_IN_MINUTE + 0, "timeShift" => 20]
            ,["departure" => 20 * SECONDS_IN_HOUR + 30 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -300]
            ,["departure" => 21 * SECONDS_IN_HOUR + 45 * SECONDS_IN_MINUTE + $defaultStartStationWait, "timeShift" => -360]
        ]        
];

// Линии странспорта, kind: 1 - метро
$transportLines = [
    1 => ["kind" => 1, "color" => 0xFF0000, "name" => "Холодногорская ветка", "minStation" => 1, "maxStation" => 13, "stations" => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]]
];

// Travel time between stations. Line, derection normal and reverse
$linesTravelTime = [
    1 => [
        "normal" => [
            2 * SECONDS_IN_MINUTE + 40, // Індустріальна -- Тракторний завод
            2 * SECONDS_IN_MINUTE + 35, // Тракторний завод -- Масельского
            2 * SECONDS_IN_MINUTE + 35, // Масельского -- Армійська
            2 * SECONDS_IN_MINUTE + 45, // Армійська -- Палац спорту
            2 * SECONDS_IN_MINUTE + 25, // Палац спорту -- Турбоатом
            2 * SECONDS_IN_MINUTE + 35, // Турбоатом -- Заводська
            2 * SECONDS_IN_MINUTE + 30, // Заводська -- Спортивна
            2 * SECONDS_IN_MINUTE + 25, // Спортивна -- Левада
            2 * SECONDS_IN_MINUTE + 45, // Левада -- м-д Констітуції
            2 * SECONDS_IN_MINUTE + 5,  // м-д Констітуції -- Центр ринок
            2 * SECONDS_IN_MINUTE,      // Масельского -- Вокзальна
            3 * SECONDS_IN_MINUTE       // Вокзальна -- Холодна гора
        ],
        "reverse" => [
            2 * SECONDS_IN_MINUTE + 35,  // Тракторний завод -- Індустріальна
            2 * SECONDS_IN_MINUTE + 35,  // Масельского -- Тракторний завод
            2 * SECONDS_IN_MINUTE + 35,  // Армійська -- Масельского
            2 * SECONDS_IN_MINUTE + 50,  // Палац спорту -- Армійська
            2 * SECONDS_IN_MINUTE + 45,  // Турбоатом -- Палац спорту
            2 * SECONDS_IN_MINUTE + 40,  // Заводська -- Турбоатом
            2 * SECONDS_IN_MINUTE + 20,  // Спортивна -- Заводська
            2 * SECONDS_IN_MINUTE + 25,  // Левада -- Спортивна
            2 * SECONDS_IN_MINUTE + 30,  // м-д Констітуції -- Левада
            2 * SECONDS_IN_MINUTE + 5,   // Центр ринок -- м-д Констітуції
            2 * SECONDS_IN_MINUTE,       // Вокзальна -- Масельского
            3 * SECONDS_IN_MINUTE,       // Холодна гора -- Вокзальна
        ]
    ]    
];

?>