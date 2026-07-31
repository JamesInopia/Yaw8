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
    public function addGame($title, $description, $controls, $thumbnailPath, $gameFilePath, $status): bool {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO game (title, description, controls, thumbnail, gameFiles, status, dateReleased, lastUpdated) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
        
        return $stmt->execute([
            trim($title), 
            trim($description),
            trim($controls), 
            trim($thumbnailPath), 
            trim($gameFilePath), 
            trim($status)
        ]);
    }

    # Function that edits/updates an existing game in the database.
    # thumbnailPath and gameFilePath are optional — only passed in (non-empty)
    # when the user actually replaced the thumbnail/game file, so the
    # existing stored files aren't overwritten with blanks otherwise.
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

        $sql = 'UPDATE game SET ' . implode(', ', $columns) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);

        return $stmt->execute($params);
    }
}