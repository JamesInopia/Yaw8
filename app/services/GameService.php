<?php
    class GameService{
        private Game $gameModel;

        public function __construct() {
            $this->gameModel = new Game();
        }
        
        # function that returns certain info of all games
        public function getAllGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $games;
        }

        # function that returns certain info of all games
        public function getTopPlayedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByPlays($games);
        }

        # function that returns certain info of all games
        public function getFeaturedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByRating($games);
        }

        # function that returns full game info into the game details modal
        # (and the game player page). $userId is optional — when given,
        # the returned Game also carries that user's own rating.
        public function getGameInfo($gameId, $userId = null) {
            $game = $this->gameModel->getGameById($gameId, $userId);

            return $game;
        }

        # Bumps totalPlays by 1. Called once when a game actually starts
        # loading in the player (not just when the page is viewed).
        public function incrementPlays($gameId): int {
            return $this->gameModel->incrementTotalPlays($gameId);
        }

        # Saves (or updates) a user's 1-5 star rating for a game.
        public function rateGame($gameId, $userId, $rating): array {
            return $this->gameModel->rateGame($gameId, $userId, $rating);
        }

        # Works out what the player should actually load for a given game,
        # based on the uploaded game file's extension:
        #   - .html / .htm  -> load directly, it's already playable
        #   - .zip          -> assumed to be an HTML5 export; extracted once
        #                      (cached after the first play) and pointed at
        #                      its index.html
        #   - .jar          -> browsers can't run Java bytecode; there is no
        #                      real "play in an iframe" for this, so it's
        #                      surfaced as a download instead of pretending
        #                      to play it
        #   - anything else -> unsupported
        # Returns ['type' => 'html'|'jar'|'unsupported', 'url' => string|null]
        # where url is relative to /public.
        public function resolvePlayable(Game $game): array {
            $gameFiles = $game->getGameFiles();

            if (empty($gameFiles)) {
                return ['type' => 'unsupported', 'url' => null];
            }

            $extension = strtolower(pathinfo($gameFiles, PATHINFO_EXTENSION));

            if ($extension === 'html' || $extension === 'htm') {
                return ['type' => 'html', 'url' => $gameFiles];
            }

            if ($extension === 'jar') {
                return ['type' => 'jar', 'url' => $gameFiles];
            }

            if ($extension === 'zip') {
                return $this->resolveZippedHtml5Game($game, $gameFiles);
            }

            return ['type' => 'unsupported', 'url' => null];
        }

        # Extracts an HTML5 game's zip into a per-game folder (only once —
        # skipped if it's already been extracted) and locates its
        # index.html so the iframe has something to point at.
        private function resolveZippedHtml5Game(Game $game, string $gameFilesRelativePath): array {
            $publicRoot = dirname(__DIR__, 2) . '/public';
            $zipFullPath = $publicRoot . '/' . ltrim($gameFilesRelativePath, '/');

            if (!file_exists($zipFullPath)) {
                return ['type' => 'unsupported', 'url' => null];
            }

            $extractRelativeDir = 'uploads/games/extracted/' . $game->getGameId() . '/';
            $extractFullDir = $publicRoot . '/' . $extractRelativeDir;

            $indexRelativePath = $this->findIndexHtml($extractFullDir);

            if ($indexRelativePath === null) {
                // Not extracted yet (or extracted but empty/broken) — extract it now.
                if (!is_dir($extractFullDir)) {
                    mkdir($extractFullDir, 0755, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($zipFullPath) === true) {
                    $zip->extractTo($extractFullDir);
                    $zip->close();
                }

                $indexRelativePath = $this->findIndexHtml($extractFullDir);
            }

            if ($indexRelativePath === null) {
                return ['type' => 'unsupported', 'url' => null];
            }

            return ['type' => 'html', 'url' => $extractRelativeDir . $indexRelativePath];
        }

        # Recursively looks for an index.html under $dir (some HTML5 exports
        # nest it inside a subfolder rather than putting it at the zip root).
        # Returns the path relative to $dir, or null if none was found.
        private function findIndexHtml(string $dir): ?string {
            if (!is_dir($dir)) {
                return null;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (strtolower($file->getFilename()) === 'index.html') {
                    return ltrim(substr($file->getPathname(), strlen($dir)), '/');
                }
            }

            return null;
        }

        # sorts games by total plays
        private function sortByPlays(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getTotalPlays() <=> $first->getTotalPlays();
            });

            return $games;
        }

        # sorts games by average rating
        private function sortByRating(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getAvgRating() <=> $first->getAvgRating();
            });

            return $games;
        }
    }
?>