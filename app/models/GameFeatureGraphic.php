<?php
    # A single screenshot or video shown in the game details modal's carousel
    class GameFeatureGraphic implements JsonSerializable {
        public const TYPE_IMAGE = 'image';
        public const TYPE_VIDEO = 'video';

        private $featureGraphicId;
        private $gameId;
        private $mediaType;
        private $filePath;
        private $sortOrder;

        public function __construct(
            $featureGraphicId = null,
            $gameId = null,
            $mediaType = self::TYPE_IMAGE,
            $filePath = "",
            $sortOrder = 0
        ) {
            $this->featureGraphicId = $featureGraphicId;
            $this->gameId = $gameId;
            $this->mediaType = $mediaType;
            $this->filePath = $filePath;
            $this->sortOrder = $sortOrder;
        }

        public function getFeatureGraphicId() { return $this->featureGraphicId; }
        public function getGameId() { return $this->gameId; }
        public function getMediaType() { return $this->mediaType; }

        # Every other stored path in this app (thumbnail, gameFiles) is kept
        # relative — no leading slash — so it resolves correctly against
        # whichever URL the page happens to be served from (see
        # ProfileController's thumbnail upload handling). Feature graphics
        # were briefly stored WITH a leading slash, which turns them into an
        # absolute-from-domain-root path and breaks as soon as the app isn't
        # hosted at the domain root. Stripping it here fixes both old rows
        # already saved with the slash and any new ones, without a migration.
        public function getFilePath() { return ltrim((string) $this->filePath, '/'); }
        public function getSortOrder() { return $this->sortOrder; }
        public function isVideo(): bool { return $this->mediaType === self::TYPE_VIDEO; }

        #[\ReturnTypeWillChange]
        public function jsonSerialize(): mixed {
            return [
                "featureGraphicId" => $this->featureGraphicId,
                "gameId" => $this->gameId,
                "mediaType" => $this->mediaType,
                "filePath" => $this->getFilePath(),
                "sortOrder" => $this->sortOrder,
            ];
        }

        # Every feature graphic for a game, videos first
        public function getByGame($gameId): array {
            $pdo = Database::connect();
            $stmt = $pdo->prepare(
                "SELECT * FROM game_feature_graphic
                 WHERE gameId = ?
                 ORDER BY FIELD(mediaType, 'video', 'image'), sortOrder ASC, featureGraphicId ASC"
            );
            $stmt->execute([$gameId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($row) {
                return new self(
                    $row['featureGraphicId'],
                    $row['gameId'],
                    $row['mediaType'],
                    $row['filePath'],
                    (int) $row['sortOrder']
                );
            }, $rows);
        }

        # Replaces every feature graphic on file for a game with a new set.
        public function replaceForGame($gameId, array $items): void {
            $pdo = Database::connect();

            try {
                $pdo->beginTransaction();

                $pdo->prepare('DELETE FROM game_feature_graphic WHERE gameId = ?')->execute([$gameId]);

                $insertStmt = $pdo->prepare(
                    'INSERT INTO game_feature_graphic (gameId, mediaType, filePath, sortOrder) VALUES (?, ?, ?, ?)'
                );

                foreach (array_values($items) as $index => $item) {
                    $insertStmt->execute([
                        $gameId,
                        $item['mediaType'],
                        $item['filePath'],
                        $index,
                    ]);
                }

                $pdo->commit();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log("Replace Feature Graphics Error: " . $e->getMessage());
                throw $e;
            }
        }

        # Every stored file path for a game
        public function getFilePathsForGame($gameId): array {
            $pdo = Database::connect();
            $stmt = $pdo->prepare('SELECT filePath FROM game_feature_graphic WHERE gameId = ?');
            $stmt->execute([$gameId]);

            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'filePath');
        }
    }
?>
