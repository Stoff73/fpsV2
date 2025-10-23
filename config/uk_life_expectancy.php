<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | UK Life Expectancy Tables (ONS 2021-2023)
    |--------------------------------------------------------------------------
    |
    | Based on UK Office for National Statistics (ONS) National Life Tables
    | Period: 2021-2023
    | Source: https://www.ons.gov.uk/peoplepopulationandcommunity/birthsdeathsandmarriages/lifeexpectancies
    |
    | Format: [age => life_expectancy_years]
    | Life expectancy represents additional years expected to live from that age
    |
    */

    'data_period' => '2021-2023',
    'last_updated' => '2025-03-18',

    /**
     * Male life expectancy by age (single year of age)
     * Key: Current age
     * Value: Additional years expected to live
     */
    'male' => [
        30 => 49.3,
        31 => 48.3,
        32 => 47.4,
        33 => 46.4,
        34 => 45.4,
        35 => 44.5,
        36 => 43.5,
        37 => 42.5,
        38 => 41.6,
        39 => 40.6,
        40 => 39.6,
        41 => 38.7,
        42 => 37.7,
        43 => 36.8,
        44 => 35.8,
        45 => 34.9,
        46 => 33.9,
        47 => 33.0,
        48 => 32.1,
        49 => 31.2,
        50 => 30.3,
        51 => 29.4,
        52 => 28.5,
        53 => 27.6,
        54 => 26.7,
        55 => 25.9,
        56 => 25.0,
        57 => 24.2,
        58 => 23.3,
        59 => 22.5,
        60 => 21.7,
        61 => 20.9,
        62 => 20.1,
        63 => 19.3,
        64 => 18.5,
        65 => 17.8,
        66 => 17.0,
        67 => 16.3,
        68 => 15.6,
        69 => 14.9,
        70 => 14.2,
        71 => 13.5,
        72 => 12.8,
        73 => 12.2,
        74 => 11.5,
        75 => 10.9,
        76 => 10.3,
        77 => 9.7,
        78 => 9.1,
        79 => 8.6,
        80 => 8.0,
        81 => 7.5,
        82 => 7.0,
        83 => 6.6,
        84 => 6.1,
        85 => 5.7,
        86 => 5.3,
        87 => 4.9,
        88 => 4.5,
        89 => 4.2,
        90 => 3.9,
    ],

    /**
     * Female life expectancy by age (single year of age)
     * Key: Current age
     * Value: Additional years expected to live
     */
    'female' => [
        30 => 52.6,
        31 => 51.7,
        32 => 50.7,
        33 => 49.7,
        34 => 48.7,
        35 => 47.7,
        36 => 46.8,
        37 => 45.8,
        38 => 44.8,
        39 => 43.8,
        40 => 42.9,
        41 => 41.9,
        42 => 40.9,
        43 => 40.0,
        44 => 39.0,
        45 => 38.1,
        46 => 37.1,
        47 => 36.2,
        48 => 35.2,
        49 => 34.3,
        50 => 33.4,
        51 => 32.4,
        52 => 31.5,
        53 => 30.6,
        54 => 29.7,
        55 => 28.8,
        56 => 27.9,
        57 => 27.0,
        58 => 26.1,
        59 => 25.2,
        60 => 24.4,
        61 => 23.5,
        62 => 22.7,
        63 => 21.8,
        64 => 21.0,
        65 => 20.2,
        66 => 19.4,
        67 => 18.6,
        68 => 17.8,
        69 => 17.0,
        70 => 16.3,
        71 => 15.5,
        72 => 14.8,
        73 => 14.1,
        74 => 13.3,
        75 => 12.6,
        76 => 12.0,
        77 => 11.3,
        78 => 10.7,
        79 => 10.0,
        80 => 9.4,
        81 => 8.8,
        82 => 8.3,
        83 => 7.7,
        84 => 7.2,
        85 => 6.7,
        86 => 6.2,
        87 => 5.7,
        88 => 5.3,
        89 => 4.9,
        90 => 4.5,
    ],

    /**
     * Default growth rate assumptions
     */
    'default_growth_rates' => [
        'assets' => 0.045, // 4.5% per annum
        'inflation' => 0.025, // 2.5% per annum (CPI target)
        'property' => 0.04, // 4% per annum
        'investments' => 0.055, // 5.5% per annum
        'savings' => 0.04, // 4% per annum
    ],
];
