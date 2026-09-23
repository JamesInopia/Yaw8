<?php
    class Game implements JsonSerializable {
        # The three statuses a game can have
        public const STATUS_PUBLISHED = 'published';
        public const STATUS_UNDER_REVIEW = 'under_review';
        public const STATUS_UNLISTED = 'unlisted';

        # How a game can be obtained
        public const ACCESS_ONLINE = 'online';
        public const ACCESS_DOWNLOAD_ONLY = 'download_only';

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
        private $accessType;
        private $allowDownload;
        private $featureGraphics;

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
            $accessType = self::ACCESS_ONLINE,
            $allowDownload = false,
            $featureGraphics = []
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
            $this->accessType = $accessType ?: self::ACCESS_ONLINE;
            $this->allowDownload = (bool) $allowDownload;
            $this->featureGraphics = $featureGraphics;
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

        # The current viewer's own submitted rating
        public function getUserRating() { return $this->userRating; }
        public function setUserRating($userRating) { $this->userRating = $userRating; }

        public function getAccessType() { return $this->accessType; }
        public function setAccessType($accessType) {
            $this->accessType = $accessType === self::ACCESS_DOWNLOAD_ONLY
                ? self::ACCESS_DOWNLOAD_ONLY
                : self::ACCESS_ONLINE;
        }

        public function isDownloadOnly(): bool {
            return $this->accessType === self::ACCESS_DOWNLOAD_ONLY;
        }

        # Raw "also allow download" flag as stored (only meaningful when
        # the game IS online — a download-only game has no such flag to
        # toggle). Use isDownloadable() for "can this actually be
        # downloaded" checks.
        public function getAllowDownload() { return $this->allowDownload; }
        public function setAllowDownload($allowDownload) { $this->allowDownload = (bool) $allowDownload; }

        # Can this game be downloaded
        public function isDownloadable(): bool {
            return $this->isDownloadOnly() || $this->allowDownload;
        }

        # Screenshots/video clips for the details-modal carousel, videos first.
        public function getFeatureGraphics() { return $this->featureGraphics; }
        public function setFeatureGraphics($featureGraphics) { $this->featureGraphics = $featureGraphics; }

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
                "accessType" => $this->accessType,
                "allowDownload" => $this->allowDownload,
                "isDownloadable" => $this->isDownloadable(),
                "featureGraphics" => $this->featureGraphics,
            ];
        }

        # searches all games in the database
        public function getAllGames($title = "", $genre = "") {
            $pdo = Database::connect();

            $params = [];
            $sql = (
                "SELECT
                    ga.gameId, ga.title, ga.totalPlays, ga.thumbnail, ga.accessType, ga.allowDownload,
                    GROUP_CONCAT(DISTINCT ge.name SEPARATOR ', ') AS genreNames,
                    COALESCE(AVG(ra.rating), 0) AS avgRating
                FROM game ga
                LEFT JOIN game_genre gg ON ga.gameId = gg.gameId
                LEFT JOIN genre ge ON gg.genreId = ge.genreId
                LEFT JOIN rating ra ON ga.gameId = ra.gameId
                WHERE ga.status = 'published'"
            );

            # append for search function
            if ($title !== "") {
                $sql .= " AND ga.title LIKE ?";
                $params[] = "%" . $title . "%";
            }
            # append for filter by genre function
            if ($genre !== "") {
                $sql .= " AND ge.name LIKE ?";
                $params[] = "%" . $genre . "%";
            }

            $sql .= " GROUP BY ga.gameId";

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
                    accessType: $row["accessType"] ?? self::ACCESS_ONLINE,
                    allowDownload: (bool) ($row["allowDownload"] ?? false),
                );
            }

            return $games;
        }

        # get the full details of a single game
        public function getGameById($gameId, $userId = null) {
            $pdo = Database::connect();

            $sql = "SELECT ga.gameId, ga.title, ga.description, ga.controls, ga.totalPlays,
                    ga.dateReleased, ga.lastUpdated, ga.thumbnail, ga.gameFiles, ga.status,
                    ga.accessType, ga.allowDownload,
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

            $featureGraphicModel = new GameFeatureGraphic();

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
                accessType: $row["accessType"] ?? self::ACCESS_ONLINE,
                allowDownload: (bool) ($row["allowDownload"] ?? false),
                featureGraphics: $featureGraphicModel->getByGame($gameId),
            );
        }

        # Bumps a game's totalPlays by 1
        public function incrementTotalPlays($gameId): int {
            $pdo = Database::connect();

            $stmt = $pdo->prepare('UPDATE game SET totalPlays = totalPlays + 1 WHERE gameId = ?');
            $stmt->execute([$gameId]);

            $selectStmt = $pdo->prepare('SELECT totalPlays FROM game WHERE gameId = ?');
            $selectStmt->execute([$gameId]);
            $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

            return $row ? (int) $row['totalPlays'] : 0;
        }

        # Inserts or updates a user's 1-5 star rating for a game
        public function rateGame($gameId, $userId, $ratingValue): array {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();

                # Update the user's existing rating, or insert the first one. Done in two
                # steps (instead of ON DUPLICATE KEY UPDATE) so it works even if the
                # rating table has no UNIQUE (gameId, userId) key.
                $existsStmt = $pdo->prepare('SELECT 1 FROM rating WHERE gameId = ? AND userId = ? LIMIT 1');
                $existsStmt->execute([$gameId, $userId]);

                if ($existsStmt->fetchColumn()) {
                    $saveStmt = $pdo->prepare('UPDATE rating SET rating = ? WHERE gameId = ? AND userId = ?');
                    $saveStmt->execute([$ratingValue, $gameId, $userId]);
                } else {
                    $saveStmt = $pdo->prepare('INSERT INTO rating (gameId, userId, rating) VALUES (?, ?, ?)');
                    $saveStmt->execute([$gameId, $userId, $ratingValue]);
                }

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

        # A random published game that actually has a file to play/download.
        # If $excludeId is the only such game, it is returned anyway.
        public function getRandomPublishedGameId($excludeId = null): ?int {
            $pdo = Database::connect();
            $base = "SELECT gameId FROM game WHERE status = 'published' AND gameFiles IS NOT NULL AND gameFiles <> ''";

            if ($excludeId !== null && ctype_digit((string) $excludeId)) {
                $stmt = $pdo->prepare($base . ' AND gameId <> ? ORDER BY RAND() LIMIT 1');
                $stmt->execute([(int) $excludeId]);
                $id = $stmt->fetchColumn();

                if ($id !== false) {
                    return (int) $id;
                }
            }

            $id = $pdo->query($base . ' ORDER BY RAND() LIMIT 1')->fetchColumn();

            return $id === false ? null : (int) $id;
        }

        # Navbar search: published games whose title or genre matches.
        # $like / $prefix are already-escaped LIKE patterns ("%q%" and "q%").
        public function searchPublished(string $like, string $prefix, int $limit = 6): array {
            $pdo = Database::connect();

            $stmt = $pdo->prepare(
                "SELECT ga.gameId, ga.title, ga.thumbnail, ga.accessType,
                    (SELECT GROUP_CONCAT(DISTINCT ge.name ORDER BY ge.name SEPARATOR ', ')
                       FROM game_genre gg JOIN genre ge ON ge.genreId = gg.genreId
                      WHERE gg.gameId = ga.gameId) AS genreNames,
                    (SELECT GROUP_CONCAT(DISTINCT us.username ORDER BY us.username SEPARATOR ', ')
                       FROM game_devs gd JOIN user_account us ON us.userId = gd.userId
                      WHERE gd.gameId = ga.gameId) AS devNames
                 FROM game ga
                 WHERE ga.status = 'published'
                   AND (ga.title LIKE ?
                        OR EXISTS (SELECT 1 FROM game_genre gg2 JOIN genre ge2 ON ge2.genreId = gg2.genreId
                                    WHERE gg2.gameId = ga.gameId AND ge2.name LIKE ?))
                 ORDER BY (ga.title LIKE ?) DESC, ga.totalPlays DESC, ga.title ASC
                 LIMIT " . max(1, $limit)
            );
            $stmt->execute([$like, $like, $prefix]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        # Function that adds a game to the database
        public function addGame($creatorUserId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status, $collaboratorUserIds = [], $accessType = self::ACCESS_ONLINE, $allowDownload = false) {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('
                    INSERT INTO game (title, description, controls, thumbnail, gameFiles, status, accessType, allowDownload, dateReleased, lastUpdated)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ');

                $stmt->execute([
                    $title,
                    $description,
                    $controls,
                    $thumbnailPath,
                    $gameFilePath,
                    $status,
                    $accessType === self::ACCESS_DOWNLOAD_ONLY ? self::ACCESS_DOWNLOAD_ONLY : self::ACCESS_ONLINE,
                    $allowDownload ? 1 : 0,
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
        public function editGame($gameId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $accessType = null, $allowDownload = null): bool {
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
                        accessType = COALESCE(?, accessType),
                        allowDownload = COALESCE(?, allowDownload),
                        lastUpdated = NOW()
                    WHERE gameId = ?
                ');

                $stmt->execute([
                    $title,
                    $description,
                    $controls,
                    $thumbnailPath,
                    $gameFilePath,
                    $accessType === null ? null : ($accessType === self::ACCESS_DOWNLOAD_ONLY ? self::ACCESS_DOWNLOAD_ONLY : self::ACCESS_ONLINE),
                    $allowDownload === null ? null : ($allowDownload ? 1 : 0),
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

        # Recursively deletes a folder and everything in it
        private function deleteDirectoryRecursive(string $dir): void {
            $items = @scandir($dir);
            if ($items === false) {
                return;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    $this->deleteDirectoryRecursive($path);
                } else {
                    @unlink($path);
                }
            }

            @rmdir($dir);
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

        # Function that gets games made by the user
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

                $stmtReports = $pdo->prepare('DELETE FROM game_report WHERE gameId = ?');
                $stmtReports->execute([$id]);

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

                    $featureGraphicsDir = $publicDir . '/uploads/feature-graphics/' . $id;
                    if (is_dir($featureGraphicsDir)) {
                        $this->deleteDirectoryRecursive($featureGraphicsDir);
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

        # ───────────────────────────────────────────
        # STATUS + ADMIN HELPERS
        # ───────────────────────────────────────────

        # Maps whatever is in the DB to one of the three real statuses
        public static function normalizeStatus($status): string {
            $s = str_replace([' ', '-'], '_', strtolower(trim((string) $status)));

            if ($s === self::STATUS_PUBLISHED) {
                return self::STATUS_PUBLISHED;
            }
            if ($s === self::STATUS_UNLISTED) {
                return self::STATUS_UNLISTED;
            }

            return self::STATUS_UNDER_REVIEW;
        }

        public function exists($gameId): bool {
            $pdo = Database::connect();
            $stmt = $pdo->prepare('SELECT 1 FROM game WHERE gameId = ? LIMIT 1');
            $stmt->execute([$gameId]);

            return (bool) $stmt->fetchColumn();
        }

        # Is this user one of the game's developers (owner or collaborator)?
        public function isDeveloper($gameId, $userId): bool {
            if (empty($userId)) {
                return false;
            }

            $pdo = Database::connect();
            $stmt = $pdo->prepare('SELECT 1 FROM game_devs WHERE gameId = ? AND userId = ? LIMIT 1');
            $stmt->execute([$gameId, $userId]);

            return (bool) $stmt->fetchColumn();
        }

        # Admin action: sets game.status to published / under_review / unlisted.
        public function updateStatus($gameId, $status): bool {
            $pdo = Database::connect();
            $stmt = $pdo->prepare('UPDATE game SET status = ? WHERE gameId = ?');

            return $stmt->execute([$status, $gameId]);
        }

        # Totals per status for the dashboard stat cards.
        public function getStatusCounts(): array {
            $pdo = Database::connect();
            $rows = $pdo->query('SELECT status, COUNT(*) AS total FROM game GROUP BY status')
                        ->fetchAll(PDO::FETCH_ASSOC);

            $counts = [
                'total' => 0,
                self::STATUS_PUBLISHED => 0,
                self::STATUS_UNDER_REVIEW => 0,
                self::STATUS_UNLISTED => 0,
            ];

            foreach ($rows as $row) {
                $key = self::normalizeStatus($row['status']);
                $counts[$key] += (int) $row['total'];
                $counts['total'] += (int) $row['total'];
            }

            return $counts;
        }

        # Every game, whatever its status, for the admin dashboard table
        public function getAllForAdmin(): array {
            $pdo = Database::connect();
            $sql = 'SELECT ga.gameId, ga.title, ga.status, ga.dateReleased, ga.lastUpdated,
                        (SELECT GROUP_CONCAT(u.username ORDER BY u.username SEPARATOR ", ")
                           FROM game_devs gd
                           JOIN user_account u ON u.userId = gd.userId
                          WHERE gd.gameId = ga.gameId) AS devNames,
                        (SELECT COALESCE(AVG(r.rating), 0) FROM rating r WHERE r.gameId = ga.gameId) AS avgRating,
                        (SELECT COUNT(*) FROM rating r WHERE r.gameId = ga.gameId) AS ratingCount,
                        (SELECT COUNT(*) FROM game_report gr
                          WHERE gr.gameId = ga.gameId AND gr.status = "open") AS openReports
                    FROM game ga
                    ORDER BY ga.dateReleased DESC, ga.gameId DESC';

            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($row) {
                return [
                    'gameId'       => (int) $row['gameId'],
                    'title'        => $row['title'],
                    'status'       => self::normalizeStatus($row['status']),
                    'devNames'     => $row['devNames'] ?? '',
                    'avgRating'    => round((float) $row['avgRating'], 1),
                    'ratingCount'  => (int) $row['ratingCount'],
                    'openReports'  => (int) $row['openReports'],
                    'dateReleased' => $row['dateReleased'],
                    'lastUpdated'  => $row['lastUpdated'],
                ];
            }, $rows);
        }

        # Full read-only details of one game
        public function getAdminGameDetail($gameId): ?array {
            $pdo = Database::connect();
            $sql = 'SELECT ga.gameId, ga.title, ga.description, ga.controls, ga.totalPlays,
                        ga.dateReleased, ga.lastUpdated, ga.thumbnail, ga.status,
                        (SELECT GROUP_CONCAT(DISTINCT ge.name ORDER BY ge.name SEPARATOR ", ")
                           FROM game_genre gg
                           JOIN genre ge ON ge.genreId = gg.genreId
                          WHERE gg.gameId = ga.gameId) AS genreNames,
                        (SELECT GROUP_CONCAT(DISTINCT u.username ORDER BY u.username SEPARATOR ", ")
                           FROM game_devs gd
                           JOIN user_account u ON u.userId = gd.userId
                          WHERE gd.gameId = ga.gameId) AS devNames,
                        (SELECT COUNT(DISTINCT gd.userId) FROM game_devs gd WHERE gd.gameId = ga.gameId) AS devCount,
                        (SELECT COALESCE(AVG(r.rating), 0) FROM rating r WHERE r.gameId = ga.gameId) AS avgRating,
                        (SELECT COUNT(*) FROM rating r WHERE r.gameId = ga.gameId) AS ratingCount
                    FROM game ga
                    WHERE ga.gameId = ?
                    LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$gameId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return [
                'gameId'       => (int) $row['gameId'],
                'title'        => $row['title'],
                'description'  => $row['description'],
                'controls'     => $row['controls'],
                'totalPlays'   => (int) $row['totalPlays'],
                'dateReleased' => $row['dateReleased'],
                'lastUpdated'  => $row['lastUpdated'],
                'thumbnail'    => $row['thumbnail'],
                'status'       => self::normalizeStatus($row['status']),
                'genreNames'   => $row['genreNames'] ?? '',
                'devNames'     => $row['devNames'] ?? '',
                'projectType'  => ((int) $row['devCount']) > 1 ? 'Collaboration' : 'Solo',
                'avgRating'    => round((float) $row['avgRating'], 1),
                'ratingCount'  => (int) $row['ratingCount'],
            ];
        }

        public function getAllGenres() {
            $pdo = Database::connect();

            $sql = 'SELECT * FROM genre ORDER BY genreId ASC';
            $stmt = $pdo->query($sql);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>