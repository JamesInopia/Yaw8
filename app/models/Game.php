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
    public function addGame($creatorUserId, $title, $description, $controls, $thumbnailPath, $gameFilePath, $status, $collaboratorUserIds = []): bool {
        $pdo = Database::connect();

        try {
            $pdo->beginTransaction();

            // 1. Insert game record
            $stmt = $pdo->prepare('
                INSERT INTO Game (title, description, controls, thumbnail, gameFiles, status, dateReleased, lastUpdated) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                trim($title), 
                trim($description),
                trim($controls), 
                trim($thumbnailPath), 
                trim($gameFilePath), 
                trim($status)
            ]);

            $gameId = $pdo->lastInsertId();

            // 2. Merge creator ID and collaborators, removing duplicate IDs
            $allDevUserIds = array_unique(array_merge([$creatorUserId], $collaboratorUserIds));

            // 3. Populate game_devs junction table
            $stmtDev = $pdo->prepare('INSERT INTO game_devs (userId, gameId) VALUES (?, ?)');
            foreach ($allDevUserIds as $devUserId) {
                if (!empty($devUserId)) {
                    $stmtDev->execute([$devUserId, $gameId]);
                }
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }

    # Function that edits/updates an existing game in the database.
    public function editGame($id, $title, $description, $controls, $status, $thumbnailPath = '', $gameFilePath = ''): bool {
        $pdo = Database::connect();

        $columns = ['title = ?', 'description = ?', 'controls = ?', 'status = ?'];
        $params = [trim($title), trim($description), trim($controls), trim($status)];

        if (!empty($thumbnailPath)) {
            $columns[] = 'thumbnail = ?';
            $params[] = trim($thumbnailPath);
        }

        if (!empty($gameFilePath)) {
            $columns[] = 'gameFiles = ?';
            $params[] = trim($gameFilePath);
        }

        $columns[] = 'lastUpdated = NOW()';
        $params[] = $id;

        $sql = 'UPDATE game SET ' . implode(', ', $columns) . ' WHERE gameId = ?';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }

    public function getGamesByUser($userId) {
        $pdo = Database::connect();
        
        $sql = 'SELECT g.* 
                FROM Game g
                INNER JOIN game_devs gd ON g.gameId = gd.gameId
                WHERE gd.userId = ?
                ORDER BY g.dateReleased DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteGame($id): bool {
        $pdo = Database::connect();
        
        try {
            $pdo->beginTransaction();

            // 1. Fetch file paths before deleting the database record
            $stmtSelect = $pdo->prepare('SELECT thumbnail, gameFiles FROM game WHERE gameId = ?');
            $stmtSelect->execute([$id]);
            $gameData = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            // 2. Delete from game_devs junction table first (prevents foreign key constraint errors)
            $stmtDevs = $pdo->prepare('DELETE FROM game_devs WHERE gameId = ?');
            $stmtDevs->execute([$id]);

            // 3. Delete the main game record
            $stmtGame = $pdo->prepare('DELETE FROM game WHERE gameId = ?');
            $stmtGame->execute([$id]);

            $pdo->commit();

            // 4. If database deletions succeed, delete the physical files from the server
            if ($gameData) {
                // Adjust this path to match your absolute project directory
                $publicDir = $_SERVER['DOCUMENT_ROOT'] . '/Yaw8/public'; 
                
                // Delete Thumbnail
                if (!empty($gameData['thumbnail'])) {
                    $thumbPath = $publicDir . $gameData['thumbnail'];
                    if (file_exists($thumbPath)) {
                        unlink($thumbPath); // Physically deletes the image
                    }
                }
                
                // Delete Game Zip/File
                if (!empty($gameData['gameFiles'])) {
                    $gameFilePath = $publicDir . $gameData['gameFiles'];
                    if (file_exists($gameFilePath)) {
                        unlink($gameFilePath); // Physically deletes the game file
                    }
                }
            }

            return true;

        } catch (Exception $e) {
            // If anything fails (like a database constraint), roll back the changes
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }
}