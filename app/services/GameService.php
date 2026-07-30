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
        public function getGameInfo($gameId) {
            $game = $this->gameModel->getGameById($gameId);

            return $game;
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