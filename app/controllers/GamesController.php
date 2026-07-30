<?php
class GamesController extends Controller{
    private GameService $gameService;

    public function __construct(){
        $this->layout = 'main';
        $this->gameService = new GameService();
    }

    # Function that takes user to the games page
    public function index(){
        $this -> view('games/index', []);
    }

    # Function that takes user to the game player
    public function player(){
        $this -> view('games/player', []);
    }

    # gets the info of all games
    public function all($title = "", $genre = ""){
        AuthMiddleware::requireAuth();

        $games = $this->gameService->getAllGames($title, $genre);

        $this->json($games);
    }

    # gets the info of the games sorted by totalPlays
    public function topPlayed($title = "", $genre = ""){
        AuthMiddleware::requireAuth();

        $topPlayedGames = $this->gameService->getTopPlayedGames($title, $genre);

        $this->json($topPlayedGames);
    }

    # gets the info of games sorted by rating
    public function featured($title = "", $genre = ""){
        AuthMiddleware::requireAuth();

        $featuredGames = $this->gameService->getFeaturedGames($title, $genre);

        $this->json($featuredGames);
    }

    # gets the full details of the selected game to be printed on the modal
    public function details($gameId = null){
        AuthMiddleware::requireAuth();

        if($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        $game = $this->gameService->getGameInfo($gameId);

        if($game === null) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        $this->json($game);
    }
}