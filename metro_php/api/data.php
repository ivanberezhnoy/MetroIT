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

// Данные станций
$stations = [
    1 => ["name" => "Індустріальна", "roundtripDayTime" => 300, "roundtripEveningTime" => 500],
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

// Данные маршрутов
$routes = [
    1 => ["startStation" => 1, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 45 * SECONDS_IN_MINUTE), "endTime" => (20 * SECONDS_IN_HOUR + 15 * SECONDS_IN_MINUTE)],
    3 => ["startStation" => 13, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 50 * SECONDS_IN_MINUTE), "endTime" => (20 * SECONDS_IN_HOUR + 20 * SECONDS_IN_MINUTE)],
    4 => ["startStation" => 6, "direction" => -1, "startTime" => (5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 20 * SECONDS_IN_MINUTE)],
    5 => ["startStation" => 1, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 35 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 22 * SECONDS_IN_MINUTE)],
    7 => ["startStation" => 6, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 2 * SECONDS_IN_MINUTE)]
];

// Исправление времени движения маршрутов
// Ключ - номер маршрута: время отправления со станции, сдвиг по времени движения до след станции
$routeTimesFixing = [
    4 => [["departure" => 5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE + 0, "timeShift" => 20], // Турбоатом
          ["departure" => 5 * SECONDS_IN_HOUR + 39 * SECONDS_IN_MINUTE + 45, "timeShift"=> 5], // Палац спорту
          ["departure" => 5 * SECONDS_IN_HOUR + 47 * SECONDS_IN_MINUTE + 45, "timeShift"=> -5]] // Тракторний завод
];

// Времена между станциями
$timesToAdd = [
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
];

?>