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
    'auth/forgot-password'   => [AuthController::class, 'forgotPassword'],
    'auth/verify-reset-code' => [AuthController::class, 'verifyResetCode'],
    'auth/reset-password'    => [AuthController::class, 'resetPassword'],

    # Content routes
        # games
        'games'             => [GamesController::class, 'index'],
        'games/details'      => [GamesController::class, 'details'],
        'games/player'      => [GamesController::class, 'player'],
        'downloader'        => [GamesController::class, 'downloader'],
        'games/all'         => [GamesController::class, 'all'],
        'games/topPlayed'   => [GamesController::class, 'topPlayed'],
        'games/featured'    => [GamesController::class, 'featured'],
        'games/play'        => [GamesController::class, 'play'],
        'games/rate'        => [GamesController::class, 'rate'],
        'games/random'      => [GamesController::class, 'random'],
        'games/report'      => [GamesController::class, 'report'],
        'games/download'    => [GamesController::class, 'download'],

        # developers
        'developers'        => [DevelopersController::class, 'index'],

        # about
        'about'             => [AboutController::class, 'index'],

        #profile
        'profile'    => [ProfileController::class, 'index'],
        'profile/addGame' => [ProfileController::class, 'addGame'],
        'profile/editGame' => [ProfileController::class, 'editGame'],
        'profile/deleteGame' => [ProfileController::class, 'deleteGame'],
        'profile/editProfile' => [ProfileController::class, 'editProfile'],
        
        #settings
        'settings'          => [SettingsController::class, 'index'],

        # admin (Admin / Supreme Overlord only)
        'admin'             => [AdminController::class, 'index'],
        'admin/game'        => [AdminController::class, 'gameDetails'],

    # API routes
    'api/profile'=> [AuthController::class, 'profile'],
    'api/settings/me' => [SettingsController::class, 'me'],
    'api/search'      => [SearchController::class, 'index'],
    'api/settings/verify-email-change' => [SettingsController::class, 'verifyEmailChange'],
    'api/settings/email'  => [SettingsController::class, 'updateEmail'],
    'api/settings/password'=> [SettingsController::class, 'updatePassword'],

    # Admin API routes
    'api/admin/games'               => [AdminController::class, 'games'],
    'api/admin/game'                => [AdminController::class, 'gameData'],
    'api/admin/game/status'         => [AdminController::class, 'updateStatus'],
    'api/admin/report/resolve'      => [AdminController::class, 'resolveReport'],
    'api/admin/reports/resolve-all' => [AdminController::class, 'resolveAllReports'],
    'api/admin/users'               => [AdminController::class, 'users'],
    'api/admin/user/suspend'        => [AdminController::class, 'suspendUser'],
    'api/admin/user/unsuspend'      => [AdminController::class, 'unsuspendUser'],
    'api/admin/user/admin-access'   => [AdminController::class, 'setAdminAccess'],
];