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
        private $status;
        private $genreNames;
        private $avgRating;
        private $devNames;
        private $userRating;
        private $totalRatingWeekly;
        private $totalPlaysWeekly;
        private $bayesianScore;

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
            $status = "",
            $genreNames = "",
            $devNames = "",
            $avgRating = "",
            $userRating = null,
            $totalRatingWeekly = "",
            $totalPlaysWeekly = "",
            $bayesianScore = "",
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
            $this->status = $status;
            $this->genreNames = $genreNames;
            $this->devNames = $devNames;
            $this->avgRating = $avgRating;
            $this->userRating = $userRating;
            $this->totalRatingWeekly = $totalRatingWeekly;
            $this->totalPlaysWeekly = $totalPlaysWeekly;
            $this->bayesianScore = $bayesianScore;
        }

        # getters and setters
        public function getGameId() { return $this->gameId; }
        public function setGameId($gameId) { $this->gameId = $gameId; }

        public function getTitle() { return $this->title; }
        public function setTitle($title) { $this->title = $title; }

        public function getControls() { return $this->controls; }
        public function setControls($controls) { $this->controls = $controls; }

        public function getDescription() { return $this->description; }
        public function setDescription($description) { $this->description = $description; }

        public function getTotalPlays() { return $this->totalPlays; }
        public function setTotalPlays($totalPlays) { $this->totalPlays = $totalPlays; }

        public function getDateReleased() { return $this->dateReleased; }
        public function setDateReleased($dateReleased) { $this->dateReleased = $dateReleased; }

        public function getLastUpdated() { return $this->lastUpdated; }
        public function setLastUpdated($lastUpdated) { $this->lastUpdated = $lastUpdated; }

        public function getThumbnail() { return $this->thumbnail; }
        public function setThumbnail($thumbnail) { $this->thumbnail = $thumbnail; }

        public function getGameFiles() { return $this->gameFiles; }
        public function setGameFiles($gameFiles) { $this->gameFiles = $gameFiles; }

        public function getStatus() { return $this->status; }
        public function setStatus($status) { $this->status = $status; }

        public function getGenreNames() { return $this->genreNames; }
        public function setGenreNames($genreNames) { $this->genreNames = $genreNames; }

        public function getDevNames() { return $this->devNames; }
        public function setDevNames($devNames) { $this->devNames = $devNames; }

        public function getAvgRating() { return $this->avgRating; }
        public function setAvgRating($avgRating) { $this->avgRating = $avgRating; }

        # The current viewer's own submitted rating (1-5), or null if they
        # haven't rated this game / aren't logged in.
        public function getUserRating() { return $this->userRating; }
        public function setUserRating($userRating) { $this->userRating = $userRating; }

        public function getTotalRatingWeekly() { return $this->totalRatingWeekly; }
        public function setTotalRatingWeekly($totalRatingWeekly) { $this->totalRatingWeekly = $totalRatingWeekly; }

        public function getTotalPlaysWeekly() { return $this->totalPlaysWeekly; }
        public function setTotalPlaysWeekly($totalPlaysWeekly) { $this->totalPlaysWeekly = $totalPlaysWeekly; }

        public function getBayesianScore() { return $this->bayesianScore; }
        public function setBayesianScore($bayesianScore) { $this->bayesianScore = $bayesianScore; }

        #[\ReturnTypeWillChange]
        public function jsonSerialize(): mixed {
            return [
                "gameId" => $this->gameId,
                "title" => $this->title,
                "description" => $this->description,
                "controls" => $this->controls,
                "totalPlays" => $this->totalPlays,
                "dateReleased" => $this->dateReleased,
                "lastUpdated" => $this->lastUpdated,
                "thumbnail" => $this->thumbnail,
                "gameFiles" => $this->gameFiles,
                "status" => $this->status,
                "genreNames" => $this->genreNames,
                "avgRating" => $this->avgRating,
                "devNames" => $this->devNames,
                "userRating" => $this->userRating,
                "totalRatingWeekly" => $this->totalRatingWeekly,
                "totalPlaysWeekly" => $this->totalPlaysWeekly,
                "bayesianScore" => $this->bayesianScore,    
            ];
        }

        # searches all games in the database (public catalog: games/all, /topPlayed, /featured)
        public function getAllGames($title = "", $genre = "") {
            $pdo = Database::connect();

            $params = [];
            $sql = "WITH RatingStats AS (
                        SELECT
                            gameId,
                            AVG(rating) AS avgRating,
                            COUNT(CASE WHEN date_rated >= CURDATE() - INTERVAL 7 DAY THEN 1 END) AS weeklyRatings,
                            AVG(CASE WHEN date_rated >= CURDATE() - INTERVAL 7 DAY THEN rating END) AS weeklyAvgRating
                        FROM rating
                        GROUP BY gameId
                    ),
                    PlayStats AS (
                        SELECT
                            gameId,
                            COUNT(CASE WHEN date_played >= CURDATE() - INTERVAL 7 DAY THEN 1 END) AS weeklyPlays
                        FROM play
                        GROUP BY gameId
                    ),
                    GameWeeklyStats AS (
                        SELECT
                            ga.gameId,
                            ga.title,
                            ga.totalPlays,
                            ga.thumbnail,
                            GROUP_CONCAT(DISTINCT ge.name SEPARATOR ', ') AS genreNames,
                            COALESCE(rs.avgRating, 0) AS avgRating,
                            COALESCE(rs.weeklyRatings, 0) AS weeklyRatings,
                            COALESCE(rs.weeklyAvgRating, 0) AS weeklyAvgRating,
                            COALESCE(ps.weeklyPlays, 0) AS weeklyPlays
                        FROM game ga
                        LEFT JOIN game_genre gg ON ga.gameId = gg.gameId
                        LEFT JOIN genre ge ON gg.genreId = ge.genreId
                        LEFT JOIN RatingStats rs ON ga.gameId = rs.gameId
                        LEFT JOIN PlayStats ps ON ga.gameId = ps.gameId
                        WHERE ga.status = 'published'
                        GROUP BY ga.gameId, ga.title, ga.totalPlays, ga.thumbnail,
                                rs.avgRating, rs.weeklyRatings, rs.weeklyAvgRating, ps.weeklyPlays
                    ),
                    GlobalStats AS (
                        SELECT
                            AVG(weeklyAvgRating) AS global_avg_rating,
                            AVG(weeklyRatings) AS rating_threshold
                        FROM GameWeeklyStats
                        WHERE weeklyRatings > 0
                    )
                    SELECT
                        gws.gameId,
                        gws.title,
                        gws.totalPlays,
                        gws.thumbnail,
                        gws.genreNames,
                        gws.avgRating,
                        gws.weeklyRatings AS totalRatingWeekly,
                        gws.weeklyAvgRating,
                        gws.weeklyPlays,
                        (
                            (gws.weeklyRatings / NULLIF(gws.weeklyRatings + gs.rating_threshold, 0)) * gws.weeklyAvgRating +
                            (gs.rating_threshold / NULLIF(gws.weeklyRatings + gs.rating_threshold, 0)) * gs.global_avg_rating
                        ) AS bayesianScore
                    FROM GameWeeklyStats gws
                    CROSS JOIN GlobalStats gs
                    WHERE 1=1";

            if ($title !== "") {
                $sql .= " AND gws.title LIKE ?";
                $params[] = "%" . $title . "%";
            }
            if ($genre !== "") {
                $sql .= " AND gws.genreNames LIKE ?";
                $params[] = "%" . $genre . "%";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $games = [];
            foreach ($rows as $row) {
            $games[] = new Game(
                gameId: $row["gameId"],
                title: $row["title"],
                totalPlays: $row["totalPlays"],
                thumbnail: $row["thumbnail"],
                genreNames: $row["genreNames"],
                avgRating: $row["avgRating"],
                totalRatingWeekly: $row["totalRatingWeekly"],
                totalPlaysWeekly: $row["weeklyPlays"],
                bayesianScore: $row["bayesianScore"],
            );
        }

            return $games;
        }

        # get the full details of a single game (used for the game details modal
        # AND to hand back the saved row after add/edit in the profile page)
        # $userId (optional): when given, also returns that user's own
        # rating for this game as userRating, so the star widget can be
        # pre-filled on load.
        public function getGameById($gameId, $userId = null) {
            $pdo = Database::connect();

            $sql = "SELECT ga.gameId, ga.title, ga.description, ga.controls, ga.totalPlays,
                    ga.dateReleased, ga.lastUpdated, ga.thumbnail, ga.gameFiles, ga.status,
                    GROUP_CONCAT(DISTINCT ge.name SEPARATOR ', ') AS genreNames,
                    GROUP_CONCAT(DISTINCT us.username SEPARATOR ', ') AS devNames,
                    COALESCE(AVG(ra.rating), 0) AS avgRating,
                    (SELECT r2.rating FROM rating r2 WHERE r2.gameId = ga.gameId AND r2.userId = ?) AS userRating
                FROM game ga
                LEFT JOIN game_genre gg ON ga.gameId = gg.gameId
                LEFT JOIN genre ge ON gg.genreId = ge.genreId
                LEFT JOIN rating ra ON ga.gameId = ra.gameId
                LEFT JOIN game_devs gd ON ga.gameId = gd.gameId
                LEFT JOIN user_account us ON gd.userId = us.userId
                WHERE ga.gameId = ?
                GROUP BY ga.gameId";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId, $gameId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
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
                status: $row["status"],
                genreNames: $row["genreNames"],
                devNames: $row["devNames"],
                avgRating: $row["avgRating"],
                userRating: $row["userRating"],
            );
        }

        # Bumps a game's totalPlays by 1 (called when the player actually
        # starts a game, not just when they view its page). Returns the
        # updated total so the caller can refresh the on-screen count
        # without a second round trip.
        public function incrementTotalPlays($gameId, $userId = null): int {
            $pdo = Database::connect();

            // Insert an individual play record (for weekly tracking)
            $insertStmt = $pdo->prepare('INSERT INTO play (gameId, userId, date_played) VALUES (?, ?, CURDATE())');
            $insertStmt->execute([$gameId, $userId]);

            // Keep the lifetime running counter (unchanged behavior)
            $stmt = $pdo->prepare('UPDATE game SET totalPlays = totalPlays + 1 WHERE gameId = ?');
            $stmt->execute([$gameId]);

            $selectStmt = $pdo->prepare('SELECT totalPlays FROM game WHERE gameId = ?');
            $selectStmt->execute([$gameId]);
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

            return $row ? (int) $row['totalPlays'] : 0;
        }

        # Inserts or updates a user's 1-5 star rating for a game, then
        # returns the game's new average plus the rating that was just
        # saved (so the caller can immediately re-render both).
        # rating(userId, gameId) is a composite primary key, so this is a
        # single atomic upsert rather than a check-then-write.
        public function rateGame($gameId, $userId, $ratingValue): array {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();

                $upsertStmt = $pdo->prepare('
                    INSERT INTO rating (gameId, userId, rating)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE rating = VALUES(rating)
                ');
                $upsertStmt->execute([$gameId, $userId, $ratingValue]);

                $avgStmt = $pdo->prepare('SELECT COALESCE(AVG(rating), 0) AS avgRating FROM rating WHERE gameId = ?');
                $avgStmt->execute([$gameId]);
                $avgRow = $avgStmt->fetch(PDO::FETCH_ASSOC);

                $pdo->commit();

                return [
                    'avgRating' => round((float) $avgRow['avgRating'], 1),
                    'userRating' => (int) $ratingValue,
                ];
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Rate Game Error: " . $e->getMessage());
                throw $e;
            }
        }

        # Function that adds a game to the database
        public function addGame($creatorUserId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status, $collaboratorUserIds = []) {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('
                    INSERT INTO game (title, description, controls, thumbnail, gameFiles, status, dateReleased, lastUpdated)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ');

                $stmt->execute([
                    $title,
                    $description,
                    $controls,
                    $thumbnailPath,
                    $gameFilePath,
                    $status
                ]);

                $gameId = $pdo->lastInsertId();
                $allDevUserIds = array_unique(array_merge([$creatorUserId], (array) $collaboratorUserIds));
                $devStmt = $pdo->prepare('INSERT INTO game_devs (userId, gameId) VALUES (?, ?)');
                foreach ($allDevUserIds as $devUserId) {
                    if (!empty($devUserId)) {
                        $devStmt->execute([$devUserId, $gameId]);
                    }
                }

                $this->syncGameGenres($pdo, $gameId, $genreNames);

                $pdo->commit();
                return $gameId;

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Add Game Error: " . $e->getMessage());
                throw $e;
            }
        }

        # Function that edits game details
        public function editGame($gameId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status): bool {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('
                    UPDATE game
                    SET title = ?,
                        description = ?,
                        controls = ?,
                        thumbnail = COALESCE(?, thumbnail),
                        gameFiles = COALESCE(?, gameFiles),
                        status = ?,
                        lastUpdated = NOW()
                    WHERE gameId = ?
                ');

                $stmt->execute([
                    $title,
                    $description,
                    $controls,
                    $thumbnailPath,
                    $gameFilePath,
                    $status,
                    $gameId
                ]);

                $this->syncGameGenres($pdo, $gameId, $genreNames);

                $pdo->commit();
                return true;

            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Edit Game Error: " . $e->getMessage());
                throw $e;
            }
        }

        private function syncGameGenres($pdo, $gameId, $genreNames): void {
            $genreNames = array_filter(array_map('trim', (array) $genreNames));
            $pdo->prepare('DELETE FROM game_genre WHERE gameId = ?')->execute([$gameId]);

            if (empty($genreNames)) {
                return;
            }

            $lookupStmt = $pdo->prepare('SELECT genreId FROM genre WHERE name = ? LIMIT 1');
            $insertStmt = $pdo->prepare('INSERT INTO game_genre (gameId, genreId) VALUES (?, ?)');

            foreach ($genreNames as $genreName) {
                $lookupStmt->execute([$genreName]);
                $genreRow = $lookupStmt->fetch(PDO::FETCH_ASSOC);
                if ($genreRow) {
                    $insertStmt->execute([$gameId, $genreRow['genreId']]);
                }
            }
        }

        # Function that gets games made by the user (raw rows — consumed as
        # $game['gameId'], $game['status'], etc. in the profile view/JS)
        public function getGamesByUser($userId) {
            $pdo = Database::connect();
            $sql = 'SELECT g.*, GROUP_CONCAT(gen.name ORDER BY gen.name SEPARATOR ", ") AS genre
                    FROM game g
                    INNER JOIN game_devs gd ON g.gameId = gd.gameId
                    LEFT JOIN game_genre gg ON g.gameId = gg.gameId
                    LEFT JOIN genre gen ON gg.genreId = gen.genreId
                    WHERE gd.userId = ?
                    GROUP BY g.gameId
                    ORDER BY g.dateReleased DESC';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        # Function that deletes game from database
        public function deleteGame($id): bool {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();

                $stmtSelect = $pdo->prepare('SELECT thumbnail, gameFiles FROM game WHERE gameId = ?');
                $stmtSelect->execute([$id]);
                $gameData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

                $stmtDevs = $pdo->prepare('DELETE FROM game_devs WHERE gameId = ?');
                $stmtDevs->execute([$id]);

                $stmtGame = $pdo->prepare('DELETE FROM game WHERE gameId = ?');
                $stmtGame->execute([$id]);

                $pdo->commit();

                if ($gameData) {
                    // Adjust this path to match your absolute project directory
                    $publicDir = $_SERVER['DOCUMENT_ROOT'] . '/Yaw8/public';

                    if (!empty($gameData['thumbnail'])) {
                        $thumbPath = $publicDir . $gameData['thumbnail'];
                        if (file_exists($thumbPath)) {
                            unlink($thumbPath); // Physically deletes the image
                        }
                    }

                    if (!empty($gameData['gameFiles'])) {
                        $gameFilePath = $publicDir . $gameData['gameFiles'];
                        if (file_exists($gameFilePath)) {
                            unlink($gameFilePath); // Physically deletes the game file
                        }
                    }
                }

                return true;

            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                return false;
            }
        }

        public function getAllGenres() {
            $pdo = Database::connect();

            $sql = 'SELECT * FROM genre ORDER BY genreId ASC';
            $stmt = $pdo->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>