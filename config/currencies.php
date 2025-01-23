<?php

// return [
//     'default' => 'UGX', // Default currency
//     'supported' => [
//         'UGX' => ['name' => 'UGX', 'rate' => 1],
//         'USD' => ['name' => 'USD', 'rate' => 0.00027], // Example rate
//         'KES' => ['name' => 'KSH', 'rate' => 0.040],
//         'EUR' => ['name' => 'Euro', 'rate' => 0.00023],
//     ],
// ];

return [
    'default' => 'UGX', // Default currency
    'supported' => [
        'UGX' => ['name' => 'UGX', 'rate' => 1],
        'USD' => ['name' => 'USD', 'rate' => 3683.20],  // Example exchange rate (1 USD = 3683.20 UGX)
        'KES' => ['name' => 'KES', 'rate' => 27.34],  // Example exchange rate (1 KES = 27.34 UGX)
        'EUR' => ['name' => 'EUR', 'rate' => 3974.56],  // Example exchange rate (1 EUR = 3974.56 UGX)
    ],
];
