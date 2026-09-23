<?php
class HomeController extends Controller{
    public function __construct(){
        $this -> layout = 'main';
    }

    # Function that takes user to the index
    public function index(){
        $gameService = new GameService();
        $topAllTimeGames = $gameService->getTopPlayedGames("", "");

        $this -> view('home/index', [
            'topAllTimeGames' => $topAllTimeGames
        ]);
    }
}