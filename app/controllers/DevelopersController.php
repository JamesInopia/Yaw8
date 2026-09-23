<?php
class DevelopersController extends Controller {
    public function __construct(){
        $this->layout = 'main';
    }

    public function index(){
        $userModel = new User();
        $gameModel = new Game();
        $users = $userModel->getAllUsers();

        $m = 10;
        $C = 4.0;

        foreach ($users as &$user) {
            $games = $gameModel->getGamesByUser($user['id']);

            $published = array_values(array_filter($games, function ($g) {
                return strtolower(trim($g['status'] ?? '')) === 'published';
            }));

            $user['gamesMade'] = array_map(function ($g) {
                return [
                    'name'       => $g['title'],
                    'genre'      => $g['genre'],
                    'thumbImage' => $g['thumbnail'],
                ];
            }, $published);

            $v = (int) $user['ratingCount'];
            $R = $user['avgRating'] !== null ? (float) $user['avgRating'] : 0;

            $user['rating'] = $v > 0 ? round($R, 1) : null;
            $user['weightedScore'] = ($v / ($v + $m)) * $R + ($m / ($v + $m)) * $C;
        }
        unset($user);
        usort($users, fn($a, $b) => $b['weightedScore'] <=> $a['weightedScore']);
        $topCount = 5;
        foreach ($users as $i => &$user) {
            $user['top'] = $user['games'] > 0 && $i < $topCount;
        }
        unset($user);

        $this->view('developers/index', [
            'developerDatabase' => $users
        ]);
    }
}