<?php
/**
 * @author Dmitriy Lezhnev <lezhnev.work@gmail.com>
 * Date: 03/02/2018
 */
declare(strict_types=1);

include(__DIR__ . "/../vendor/autoload.php");

/**
 * Let's make sure that weather API gives us expected data payload.
 * API source: https://www.metaweather.com/api/location/2122265/
 * Docs: https://www.metaweather.com/api/
 */

$pattern = [
    "woeid" => ":int",
    "title" => ":string",
    "location_type" => ":string",
    "latt_long" => ":string :regexp(#^\d+\.\d+,\d+\.\d+$#)",
    "time" => ":string :date",
    ":string :regex(#^sun_(rise|set)$#){2}" => ":string :date",
    "timezone" => ":string",
    "timezone_name" => ":string",
    "parent" => [
        "title" => ":string",
        "location_type" => ":string",
        "woeid" => ":int",
        "latt_long" => ":string :regexp(#^\d+\.\d+,\d+\.\d+$#)",
    ],
    "consolidated_weather" => [
        "*" => [
            "id" => ":int",
            "weather_state_name" => ":string(nullable)",
            "weather_state_abbr" => ":string(nullable)",
            "wind_direction_compass" => ":string(nullable)",
            "created" => ":string(nullable) :date",
            "applicable_date" => ":string(nullable)",
            "min_temp" => ":float(nullable)",
            "max_temp" => ":float(nullable)",
            "the_temp" => ":float(nullable)",
            "wind_speed" => ":float(nullable)",
            "wind_direction" => ":float(nullable)",
            "air_pressure" => ":float(nullable)",
            "humidity" => ":int(nullable)",
            "visibility" => ":float(nullable)",
            "predictability" => ":int(nullable)",
        ],
    ],
    "sources" => [
        "*" => [
            "title" => ":string",
            "slug" => ":string",
            "url" => ":string :url",
            "crawl_rate" => ":int",
        ],
    ],
];

$json = file_get_contents("https://www.metaweather.com/api/location/2122265/");
$data = json_decode($json, true);


$traverser = new \PASVL\Traverser\VO\Traverser(new \PASVL\ValidatorLocator\ValidatorLocator());
try {
    $traverser->match($pattern, $data); // returns void, throws Report on Fail
    echo "Data was valid";
} catch (\PASVL\Traverser\FailReport $report) {
    echo "\n--- Array does not match a pattern ---\n";

    echo "Reason: ";
    echo $report->getReason()->isValueType() ? "Invalid value found" : "";
    echo $report->getReason()->isKeyType() ? "Invalid key found" : "";
    echo $report->getReason()->isKeyQuantityType() ? "Invalid key quantity found" : "";
    echo "\n";
}