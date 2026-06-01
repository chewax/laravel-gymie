<?php

return [
    /*
    | Apple Wallet (.pkpass). You need an Apple Developer account ($99/yr),
    | a Pass Type ID + its certificate exported as .p12, and Apple's WWDR
    | intermediate certificate (PEM). Drop the files under storage/app/wallet/.
    */
    'apple' => [
        'enabled' => env('WALLET_APPLE_ENABLED', false),
        'pass_type_id' => env('WALLET_APPLE_PASS_TYPE_ID'),     // e.g. pass.cloud.mdevel.gymtastic
        'team_id' => env('WALLET_APPLE_TEAM_ID'),               // Apple Team ID
        'organization' => env('WALLET_APPLE_ORG_NAME', config('app.name')),
        'description' => env('WALLET_APPLE_DESCRIPTION', 'Gym membership card'),
        // Paths (relative to storage/app) to the signing material:
        'certificate' => env('WALLET_APPLE_CERT_PATH', 'wallet/apple/certificate.p12'),
        'certificate_password' => env('WALLET_APPLE_CERT_PASSWORD', ''),
        'wwdr' => env('WALLET_APPLE_WWDR_PATH', 'wallet/apple/wwdr.pem'),
        'background_color' => env('WALLET_APPLE_BG', 'rgb(21,117,115)'),
        'foreground_color' => env('WALLET_APPLE_FG', 'rgb(255,255,255)'),
        'label_color' => env('WALLET_APPLE_LABEL', 'rgb(209,250,229)'),
    ],

    /*
    | Google Wallet. You need a Google Cloud project with the Wallet API
    | enabled, a Wallet issuer account, and a service-account JSON key.
    | Put the key at storage/app/wallet/google/service-account.json.
    */
    'google' => [
        'enabled' => env('WALLET_GOOGLE_ENABLED', false),
        'issuer_id' => env('WALLET_GOOGLE_ISSUER_ID'),          // numeric issuer id
        'class_suffix' => env('WALLET_GOOGLE_CLASS_SUFFIX', 'gym_membership'),
        'program_name' => env('WALLET_GOOGLE_PROGRAM_NAME', config('app.name')),
        'service_account' => env('WALLET_GOOGLE_SERVICE_ACCOUNT', 'wallet/google/service-account.json'),
        'background_color' => env('WALLET_GOOGLE_BG', '#157573'),
        'origin' => env('WALLET_GOOGLE_ORIGIN', env('APP_URL')),
    ],
];
