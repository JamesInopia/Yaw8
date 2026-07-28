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

            // "genre" is not a column on game — genres are a many-to-many
            // relationship via the game_genre junction table (see ERD).
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

            // Get the ID of the game we just inserted
            $gameId = $pdo->lastInsertId();

            // Associate the creator + any collaborators via game_devs
            // (this is the same junction table getGamesByUser()/deleteGame() rely on —
            // the old code inserted into a nonexistent "Collaborators" table and
            // never linked the creator at all, so new games never showed up in "My Games").
            $allDevUserIds = array_unique(array_merge([$creatorUserId], (array) $collaboratorUserIds));
            $devStmt = $pdo->prepare('INSERT INTO game_devs (userId, gameId) VALUES (?, ?)');
            foreach ($allDevUserIds as $devUserId) {
                if (!empty($devUserId)) {
                    $devStmt->execute([$devUserId, $gameId]);
                }
            }

            // Associate genres via game_genre (genreNames are looked up against
            // the genres table to resolve their genreId).
            $this->syncGameGenres($pdo, $gameId, $genreNames);

            $pdo->commit();
            // Return the new gameId (truthy) instead of a plain bool so the
            // controller can immediately fetch + return the full saved row —
            // this is what lets the frontend update without a page refresh.
            return $gameId;

        } catch (PDOException $e) {
            // Roll back if anything fails
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Add Game Error: " . $e->getMessage());
            // Rethrow (instead of silently returning false) so the exact SQL error
            // surfaces in ProfileController's response instead of a generic message.
            throw $e;
        }
    }

    // Fetches a single game (with its comma-joined genre string) by id —
    // used after addGame()/editGame() to hand the saved row straight back
    // to the frontend so it can update the UI without a page refresh.
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

    // --- 1. EDIT GAME DATABASE FUNCTION ---
    public function editGame($gameId, $title, $description, $controls, $genreNames, $thumbnailPath, $gameFilePath, $status): bool {
        $pdo = Database::connect();
        
        try {
            $pdo->beginTransaction();

            // We use COALESCE so if no new file was uploaded (null), it keeps the old database value.
            // "genre" is not a column on game — handled via game_genre below.
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

            // Replace this game's genre associations with the submitted set.
            $this->syncGameGenres($pdo, $gameId, $genreNames);

            $pdo->commit();
            return true;

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Edit Game Error: " . $e->getMessage());
            // Rethrow so the exact SQL error surfaces via ProfileController's catch
            // instead of the generic "Database error while updating game."
            throw $e;
        }
    }

    // Resolves genre names to genreIds (via the genres table) and replaces
    // this game's rows in the game_genre junction table with the new set.
    private function syncGameGenres($pdo, $gameId, $genreNames): void {
        $genreNames = array_filter(array_map('trim', (array) $genreNames));

        // Clear existing associations first so edits fully replace the genre list.
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
    

    public function getGamesByUser($userId) {
        $pdo = Database::connect();
        
        // LEFT JOINs so games with zero genres still come back (with genre = NULL),
        // instead of being silently dropped by an INNER JOIN. GROUP_CONCAT collapses
        // the (possibly multiple) genre rows per game into one comma-separated string.
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

    public function getAllGenres() {
    $pdo = Database::connect();
    
    $sql = 'SELECT * FROM genre ORDER BY genreId ASC'; 
    $stmt = $pdo->query($sql);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}