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
        'games'             => [GamesController::class, 'index'],
        'games/details'      => [GamesController::class, 'detail'],
        'games/player'      => [GamesController::class, 'player'],
        'games/all'         => [GamesController::class, 'all'],
        'games/topPlayed'   => [GamesController::class, 'topPlayed'],
        'games/featured'    => [GamesController::class, 'featured'],

        # developers
        'developers'        => [DevelopersController::class, 'index'],

        # about
        'about'             => [AboutController::class, 'index'],

        #profile
        'profile'           => [ProfileController::class, 'index'],
        
        #settings
        'settings'          => [SettingsController::class, 'index'],

    # API routes
    'api/profile'           => [AuthController::class, 'profile'],
];