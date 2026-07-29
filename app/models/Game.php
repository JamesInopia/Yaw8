<?php
class Game {
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

    public function __construct($gameId = '', $title = '', $description = '', $controls = '', $totalPlays = '', $dateReleased = '', $lastUpdated = '', $thumbnail = '', $gameFiles = '', $status = ''){
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
    }

    # Getters and Setters
    public function getId() { return $this->gameId; }
    public function setId($gameId) { $this->gameId = $gameId; }

    public function getTitle() { return $this->title; }
    public function setTitle($title) { $this->title = $title; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getControls() { return $this->controls; }
    public function setControls($controls) { $this->controls = $controls; }

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

    public function getGameById($gameId) {
        $pdo = Database::connect();

        $sql = 'SELECT g.*, GROUP_CONCAT(gen.name ORDER BY gen.name SEPARATOR ", ") AS genre
                FROM game g
                LEFT JOIN game_genre gg ON g.gameId = gg.gameId
                LEFT JOIN genre gen ON gg.genreId = gen.genreId
                WHERE g.gameId = ?
                GROUP BY g.gameId';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$gameId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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