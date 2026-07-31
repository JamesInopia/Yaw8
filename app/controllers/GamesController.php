<?php
class GamesController extends Controller{
    private GameService $gameService;

    public function __construct(){
        $this->layout = 'main';
        $this->gameService = new GameService();
    }

    # Function that takes user to the games page
    public function index(){
        $games = $this->gameService->getAllGames("", "");
        $topPlayedGames = $this->gameService->getTopPlayedGames("", "");
        $featuredGames = $this->gameService->getFeaturedGames("", "");

        $this -> view('games/index', [
            'games' => $games,
            'topPlayedGames' => $topPlayedGames,
            'featuredGames' => $featuredGames
        ]);
    }

    # Function that takes user to the game player
    public function player($gameId = null){
        if ($gameId === null) {
            header('Location: ?url=games');
            exit;
        }

        // Soft auth: a plain page navigation never carries the JWT Bearer
        // header (that's only attached by fetch() calls), so we read the
        // PHP session instead. Guests can still view/play; rating still
        // requires being logged in (enforced in rate()).
        $userId = $this->currentUserIdOrNull();

        $game = $this->gameService->getGameInfo($gameId, $userId);

        if ($game === null) {
            header('Location: ?url=games');
            exit;
        }

        $playable = $this->gameService->resolvePlayable($game);

        $this->view('games/player', [
            'game' => $game,
            'playable' => $playable,
        ]);
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
        $payload = AuthMiddleware::requireAuth();

        if($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        $userId = $payload['sub'] ?? null;
        $game = $this->gameService->getGameInfo($gameId, $userId);

        if($game === null) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        $this->json($game);
    }

    # Called once the player actually starts a game (not just views its
    # page). Increments totalPlays and, in the same round trip, resolves
    # what the iframe should actually load (extracting an HTML5 zip on
    # first play if needed).
    public function play($gameId = null){
        AuthMiddleware::requireAuth();

        if ($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        $game = $this->gameService->getGameInfo($gameId);

        if ($game === null) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        $totalPlays = $this->gameService->incrementPlays($gameId);
        $playable = $this->gameService->resolvePlayable($game);

        $this->json([
            "totalPlays" => $totalPlays,
            "playable" => $playable,
        ]);
    }

    # Submits (or updates) the current user's 1-5 star rating for a game.
    public function rate($gameId = null){
        $payload = AuthMiddleware::requireAuth();

        if ($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        $input = $this->jsonInput();
        $rating = (int) ($input['rating'] ?? 0);

        if ($rating < 1 || $rating > 5) {
            $this->json(["error" => "Rating must be between 1 and 5"], 400);
            return;
        }

        $userId = $payload['sub'] ?? null;

        if ($userId === null) {
            $this->json(["error" => "Invalid session"], 401);
            return;
        }

        $result = $this->gameService->rateGame($gameId, $userId, $rating);

        $this->json($result);
    }
}