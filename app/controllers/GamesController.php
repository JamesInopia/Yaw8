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
        $userId = $this->currentUserIdOrNull();

        $game = $this->gameService->getGameInfo($gameId, $userId);

        if ($game === null || !$this->gameService->canView($game, $userId)) {
            header('Location: ?url=games');
            exit;
        }


        if ($game->isDownloadOnly()) {
            header('Location: ?url=downloader&gameId=' . $gameId);
            exit;
        }

        $playable = $this->gameService->resolvePlayable($game);

        $this->view('games/player', [
            'game' => $game,
            'playable' => $playable,
        ]);
    }

    # Function that takes user to the Downloader page — the equivalent of
    # player() for games with no in-browser experience at all.
    public function downloader($gameId = null){
        if ($gameId === null) {
            header('Location: ?url=games');
            exit;
        }

        $userId = $this->currentUserIdOrNull();
        $game = $this->gameService->getGameInfo($gameId, $userId);

        if ($game === null || !$this->gameService->canView($game, $userId)) {
            header('Location: ?url=games');
            exit;
        }

        if (!$game->isDownloadOnly()) {
            header('Location: ?url=games/player&gameId=' . $gameId);
            exit;
        }

        $this->view('games/downloader', [
            'game' => $game,
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

        if($game === null || !$this->gameService->canView($game, $userId)) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        $this->json($game);
    }

    # Called once the player actually starts a game
    public function play($gameId = null){
        $payload = AuthMiddleware::requireAuth();

        if ($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        $game = $this->gameService->getGameInfo($gameId);

        if ($game === null || !$this->gameService->canView($game, $payload['sub'] ?? null)) {
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

        if (!$this->gameService->gameExists($gameId)) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        try {
            $result = $this->gameService->rateGame($gameId, $userId, $rating);
        } catch (Throwable $e) {
            error_log("Rate Game failed: " . $e->getMessage());
            $this->json(["error" => "Couldn't save your rating."], 500);
            return;
        }

        $this->json($result);
    }

    # Files a report against a game
    public function report($gameId = null){
        $payload = AuthMiddleware::requireAuth();

        if ($gameId === null) {
            $this->json(["error" => "Missing Game Id"], 400);
            return;
        }

        if (!$this->gameService->gameExists($gameId)) {
            $this->json(["error" => "Game not found"], 404);
            return;
        }

        $input = $this->jsonInput();
        $reason = trim((string) ($input['reason'] ?? ''));
        $details = trim((string) ($input['details'] ?? ''));

        if ($reason === '') {
            $this->json(["error" => "Please choose a reason for this report."], 400);
            return;
        }

        if (mb_strlen($reason) > 100) {
            $reason = mb_substr($reason, 0, 100);
        }

        $userId = $payload['sub'] ?? null;

        if ($userId === null) {
            $this->json(["error" => "Invalid session"], 401);
            return;
        }

        $reportId = $this->gameService->reportGame($gameId, $userId, $reason, $details);

        $this->json(["success" => true, "reportId" => $reportId]);
    }

    # Streams a game's uploaded file to the browser as a download
    public function download($gameId = null) {
        if ($gameId === null) {
            http_response_code(400);
            echo "Missing Game Id";
            exit;
        }

        $userId = $this->currentUserIdOrNull();
        $game = $this->gameService->getGameInfo($gameId, $userId);

        if ($game === null || !$this->gameService->canView($game, $userId)) {
            http_response_code(404);
            echo "Game not found.";
            exit;
        }

        if (!$game->isDownloadable()) {
            http_response_code(403);
            echo "This game isn't available for download.";
            exit;
        }

        $relativePath = $game->getGameFiles();
        if (empty($relativePath)) {
            http_response_code(404);
            echo "No downloadable file for this game.";
            exit;
        }

        $publicRoot = realpath(__DIR__ . '/../../public');
        $absolutePath = realpath($publicRoot . '/' . ltrim($relativePath, '/'));

        if ($absolutePath === false || strpos($absolutePath, $publicRoot) !== 0 || !is_file($absolutePath)) {
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        $downloadName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $game->getTitle()) ?: 'game';
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: 'zip';

        if (ob_get_length()) ob_clean();
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '.' . $extension . '"');
        header('Content-Length: ' . filesize($absolutePath));
        header('X-Content-Type-Options: nosniff');
        readfile($absolutePath);
        exit;
    }
}