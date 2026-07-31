<?php
class HomeController extends Controller{
    public function __construct(){
        $this -> layout = 'main';
    }

    # Function that takes user to the index
    public function index(){
        $gameService = new GameService();
        $featuredGames = $gameService->getFeaturedGames("", "");

        $this -> view('home/index', [
            'featuredGames' => $featuredGames
        ]);
    }
}