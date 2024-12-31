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
    5 => ["startStation" => 1, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 35 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 22 * SECONDS_IN_MINUTE)],
    7 => ["startStation" => 6, "direction" => 1, "startTime" => (5 * SECONDS_IN_HOUR + 37 * SECONDS_IN_MINUTE), "endTime" => (22 * SECONDS_IN_HOUR + 2 * SECONDS_IN_MINUTE)]
];

// Времена между станциями
$timesToAdd = [
    2 * SECONDS_IN_MINUTE + 40, 2 * SECONDS_IN_MINUTE + 35, 2 * SECONDS_IN_MINUTE + 35,
    2 * SECONDS_IN_MINUTE + 45, 2 * SECONDS_IN_MINUTE + 25, 2 * SECONDS_IN_MINUTE + 35,
    2 * SECONDS_IN_MINUTE + 30, 2 * SECONDS_IN_MINUTE + 25, 2 * SECONDS_IN_MINUTE + 45,
    2 * SECONDS_IN_MINUTE + 5, 2 * SECONDS_IN_MINUTE, 3 * SECONDS_IN_MINUTE
];

?>