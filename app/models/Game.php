<?php
    class Game implements JsonSerializable {
        private $gameId;
        private $title;
        private $description;
        private $controls;  
        private $totalPlays;
        private $dateReleased;
        private $lastUpdated;
        private $thumbnail;
        private $gameFiles;
        private $genreNames;
        private $avgRating;
        private $devNames;

        public function __construct(
            $gameId = "",
            $title = "",
            $description = "",
            $controls = "",
            $totalPlays = "",
            $dateReleased = "",
            $lastUpdated = "",
            $thumbnail = "",
            $gameFiles = "",
            $genreNames = "",
            $devNames = "",
            $avgRating = ""
        ) {
            $this->gameId = $gameId;
            $this->title = $title;
            $this->description = $description;
            $this->controls = $controls;
            $this->totalPlays = $totalPlays;
            $this->dateReleased = $dateReleased;
            $this->lastUpdated = $lastUpdated;
            $this->thumbnail = $thumbnail;
            $this->gameFiles = $gameFiles;
            $this->genreNames = $genreNames;
            $this->devNames = $devNames;
            $this->avgRating = $avgRating;
        }

        # getters and setters
        public function getGameId() { return $this->gameId; }
        public function setGameId($gameId) { $this->gameId = $gameId; }

        public function getTitle() { return $this->title;}
        public function setTitle($title) { $this->title = $title; }

        public function getControls() {return $this->controls; }
        public function setControls($controls) { $this->controls = $controls; }

        public function getDescription() {return $this->description; }
        public function setDescription($description) { $this->description = $description; }

        public function getTotalPlays() { return $this->totalPlays; }
        public function setTotalPlays($totalPlays) { $this->totalPlays = $totalPlays; }

        public function getDateReleased() { return $this->dateReleased; }
        public function setDateReleased($dateReleased) { $this->dateReleased = $dateReleased;}

        public function getLastUpdated() { return $this->lastUpdated; }
        public function setLastUpdated($lastUpdated) { $this->lastUpdated = $lastUpdated;}

        public function getThumbnail() { return $this->thumbnail; }
        public function setThumbnail($thumbnail) { $this->thumbnail = $thumbnail; }

        public function getGameFiles() { return $this->gameFiles; }
        public function setGameFiles($gameFiles) { $this->gameFiles = $gameFiles; }

        public function getGenreNames() { return $this->genreNames; }
        public function setGenreNames($genreNames) { $this->genreNames = $genreNames; }

        public function getDevNames() { return $this->devNames; }
        public function setDevNames($devNames) { $this->devNames = $devNames; }

        public function getAvgRating() { return $this->avgRating; }
        public function setAvgRating($avgRating) { $this->avgRating = $avgRating; }

        #[\ReturnTypeWillChange]
        public function jsonSerialize(): mixed {
            return[
                "gameId" => $this->gameId,
                "title" => $this->title,
                "description" => $this->description,
                "controls" => $this->controls,
                "totalPlays" => $this->totalPlays,
                "dateReleased" => $this->dateReleased,
                "lastUpdated" => $this->lastUpdated,
                "thumbnail" => $this->thumbnail,
                "gameFiles" => $this->gameFiles,
                "genreNames" => $this->genreNames,
                "avgRating" => $this->avgRating,
                "devNames" => $this->devNames,
            ];
        }

        # searches all games in the database
        public function getAllGames($title = "", $genre = ""){
            $pdo = Database::connect();
            
            $params = [];
            $sql = (
                "SELECT 
                    ga.gameId, ga.title, ga.totalPlays, ga.thumbnail,   
                    GROUP_CONCAT(DISTINCT ge.name SEPARATOR ', ') AS genreNames,
                    COALESCE(AVG(ra.rating), 0) AS avgRating 
                FROM game ga
                LEFT JOIN game_genre gg ON ga.gameId = gg.gameId
                LEFT JOIN genre ge ON gg.genreId = ge.genreId
                LEFT JOIN rating ra ON ga.gameId = ra.gameId
                WHERE ga.status = 'published'"
            );

            # append for search function
            if($title !== "") {
                $sql .= " AND ga.title LIKE ?";
                $params[] = "%". $title. "%";
            }
            # append for filter by genre function
            if($genre !== "") {
                $sql .= " AND ge.name LIKE ?";
                $params[] = "%". $genre. "%";
            }

            $sql .= " GROUP BY ga.gameId";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $games = [];
            foreach($rows as $row) {
                $games[] = new Game(
                    gameId: $row["gameId"],
                    title: $row["title"],
                    totalPlays: $row["totalPlays"],
                    thumbnail: $row["thumbnail"],
                    genreNames: $row["genreNames"],
                    avgRating: $row["avgRating"],
                );
            }

            return $games;
        }

        # get the full details of a single game
        public function getGameById($gameId) {
            $pdo = Database::connect();

            $sql = "SELECT ga.gameId, ga.title, ga.description, ga.controls, ga.totalPlays, 
                    ga.dateReleased, ga.lastUpdated, ga.thumbnail, ga.gameFiles, 
                    GROUP_CONCAT(DISTINCT ge.name SEPARATOR ', ') AS genreNames, 
                    GROUP_CONCAT(DISTINCT us.username SEPARATOR ', ') AS devNames, 
                    COALESCE(AVG(ra.rating), 0) AS avgRating
                FROM game ga
                LEFT JOIN game_genre gg ON ga.gameId = gg.gameId
                LEFT JOIN genre ge ON gg.genreId = ge.genreId
                LEFT JOIN rating ra ON ga.gameId = ra.gameId
                LEFT JOIN game_devs gd ON ga.gameId = gd.gameId
                LEFT JOIN user_account us ON gd.userId = us.userId
                WHERE ga.gameId = ?
                GROUP BY ga.gameId";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$gameId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$row) {
                return null;
            }

            return new Game(
                gameId: $row["gameId"],
                title: $row["title"],
                description: $row["description"],
                controls: $row["controls"],
                totalPlays: $row["totalPlays"],
                dateReleased: $row["dateReleased"],
                lastUpdated: $row["lastUpdated"],
                thumbnail: $row["thumbnail"],
                gameFiles: $row["gameFiles"],
                genreNames: $row["genreNames"],
                devNames: $row["devNames"],
                avgRating: $row["avgRating"],
            );
        }
    }
?>
