<?php

return [
    'csv_settings' =>
        [
            'date_idx' => env('OPERATION_DATE_IDX'),
            'uid_idx' => env('UID_IDX'),
            'type_idx' => env('USER_TYPE_IDX'),
            'opt_idx' => env('OPT_TYPE_IDX'),
            'amount_idx' => env('OPT_AMOUNT_IDX'),
            'currency_idx' => env('OPT_CURRENCY_IDX'),
        ],
    'deposit_charge' => env('DEPOSIT_CHARGE'),
    'withdraw_private_charge' => env('WITHDRAW_PRIVATE'),
    'withdraw_business_charge' => env('WITHDRAW_BUSINESS'),
    'withdraw_per_week_limit' => env('MAX_WITHDRAW_PER_WEEK'),
];
