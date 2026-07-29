<?php
return [
    # Home routes
    ''           => [HomeController::class, 'index'],
    'home'       => [HomeController::class, 'index'],

    # Auth routes
    'auth'       => [AuthController::class, 'index'],
    'auth/form'  => [AuthController::class, 'getForm'],
    'login'      => [AuthController::class, 'login'],
    'signup'     => [AuthController::class, 'signup'],
    'logout'     => [AuthController::class, 'logout'],

    # Content routes
        # games
        'games'      => [GamesController::class, 'index'],

        # developers
        'developers' => [DevelopersController::class, 'index'],

        # about
        'about'      => [AboutController::class, 'index'],

        #profile
        'profile'    => [ProfileController::class, 'index'],
        'profile/addGame' => [ProfileController::class, 'addGame'],
        'profile/editGame' => [ProfileController::class, 'editGame'],
        'profile/deleteGame' => [ProfileController::class, 'deleteGame'],
        'profile/editProfile' => [ProfileController::class, 'editProfile'],
        
        #settings
        'settings'   => [SettingsController::class, 'index'],

    # API routes
    'api/profile'=> [AuthController::class, 'profile'],
    'api/settings/me' => [SettingsController::class, 'me'],
    'api/settings/verify-email-change' => [SettingsController::class, 'verifyEmailChange'],
    'api/settings/email'  => [SettingsController::class, 'updateEmail'],
    'api/settings/password'=> [SettingsController::class, 'updatePassword'],
];