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
    "woeid" => ":number :int",
    "title" => ":string",
    "location_type" => ":string",
    "latt_long" => ":string :regexp('^\d+\.\d+,\d+\.\d+$')",
    "time" => ":string :regexp('\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6}.{1,7}')",
    ":string :regexp('^sun_(rise|set)$'){2}" => ":string :regexp('\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6}.{1,7}')",
    "timezone" => ":string",
    "timezone_name" => ":string",
    "parent" => [
        "title" => ":string",
        "location_type" => ":string",
        "woeid" => ":number :int",
        "latt_long" => ":string :regexp('^\d+\.\d+,\d+\.\d+$')",
    ],
    "consolidated_weather" => [
        "*" => [
            "id" => ":number :int",
            "weather_state_name" => ":string?",
            "weather_state_abbr" => ":string?",
            "wind_direction_compass" => ":string?",
            "created" => ":string? :regexp('\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6}.{1,7}')",
            "applicable_date" => ":string?",
            "min_temp" => ":number? :float",
            "max_temp" => ":number? :float",
            "the_temp" => ":number? :float",
            "wind_speed" => ":number? :float",
            "wind_direction" => ":number? :float",
            "air_pressure" => ":number? :float",
            "humidity" => ":number :int",
            "visibility" => ":number? :float",
            "predictability" => ":number :int",
        ],
    ],
    "sources" => [
        "*" => [
            "title" => ":string",
            "slug" => ":string",
            "url" => ":string :url",
            "crawl_rate" => ":number :int",
        ],
    ],
];

$json = file_get_contents("https://www.metaweather.com/api/location/2122265/");
$data = json_decode($json, true);


$builder = \PASVL\Validation\ValidatorBuilder::forArray($pattern);
try {
    $builder->build()->validate($data);
    echo "Data was valid";
} catch (\PASVL\Validation\Problems\ArrayFailedValidation $report) {
    echo "\n--- Array does not match a pattern ---\n";
    echo $report->getMessage();
}