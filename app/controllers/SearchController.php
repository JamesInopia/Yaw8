<?php
# Navbar search — public JSON endpoint (guests can search too).
# GET ?url=api/search&q=text  ->  { success, query, games: [...], developers: [...] }
class SearchController extends Controller {
    private const MAX_QUERY_LENGTH = 60;

    public function index($q = '') {
        $q = trim((string) $q);

        if (mb_strlen($q) > self::MAX_QUERY_LENGTH) {
            $q = mb_substr($q, 0, self::MAX_QUERY_LENGTH);
        }

        if ($q === '') {
            $this->json(['success' => true, 'query' => '', 'games' => [], 'developers' => []]);
        }

        # Treat % _ and \ in what was typed as plain characters, not LIKE wildcards
        $escaped = addcslashes($q, '%_\\');
        $like = '%' . $escaped . '%';
        $prefix = $escaped . '%';

        try {
            $games = (new Game())->searchPublished($like, $prefix, 6);
            $developers = (new User())->searchDevelopers($like, $prefix, 5);
        } catch (Throwable $e) {
            error_log('Search failed: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Search failed. Please try again.'], 500);
        }

        $this->json([
            'success' => true,
            'query' => $q,
            'games' => array_map(function ($g) {
                return [
                    'gameId'      => (int) $g['gameId'],
                    'title'       => $g['title'],
                    'thumbnail'   => $g['thumbnail'],
                    'genreNames'  => $g['genreNames'] ?? '',
                    'devNames'    => $g['devNames'] ?? '',
                    'downloadOnly' => ($g['accessType'] ?? '') === Game::ACCESS_DOWNLOAD_ONLY,
                ];
            }, $games),
            'developers' => array_map(function ($d) {
                return [
                    'id'       => (int) $d['id'],
                    'name'     => $d['name'],
                    'username' => $d['username'],
                    'games'    => (int) $d['games'],
                ];
            }, $developers),
        ]);
    }
}
