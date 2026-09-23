<?php
# Admin dashboard — only Admin / Supreme Overlord accounts may use anything here.
class AdminController extends Controller {
    private AdminService $adminService;

    public function __construct() {
        $this->layout = 'main';
        $this->adminService = new AdminService();
    }

    // ═════════════════════════════════════════
    // PAGES
    // ═════════════════════════════════════════

    # Dashboard: stat cards + Games / Users tabs
    public function index() {
        $this->requireAdminPage();

        $this->view('admin/index', [
            'stats' => $this->adminService->getStats(),
        ]);
    }

    # Read-only details page for one game
    public function gameDetails($gameId = null) {
        $this->requireAdminPage();

        if ($gameId === null || !ctype_digit((string) $gameId)) {
            header('Location: ?url=admin');
            exit;
        }

        $this->view('admin/game', [
            'gameId' => (int) $gameId,
        ]);
    }

    // ═════════════════════════════════════════
    // JSON API
    // ═════════════════════════════════════════

    # GET api/admin/games
    public function games() {
        $this->requireAdminApi();

        $this->json([
            'success' => true,
            'games'   => $this->adminService->getGames(),
            'stats'   => $this->adminService->getStats(),
        ]);
    }

    # GET api/admin/game&gameId=5
    public function gameData($gameId = null) {
        $this->requireAdminApi();

        if ($gameId === null || !ctype_digit((string) $gameId)) {
            $this->json(['success' => false, 'message' => 'Missing or invalid game id.'], 400);
        }

        $detail = $this->adminService->getGameDetail((int) $gameId);

        if ($detail === null) {
            $this->json(['success' => false, 'message' => 'Game not found.'], 404);
        }

        $this->json(['success' => true] + $detail);
    }

    # POST api/admin/game/status
    public function updateStatus() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $gameId = $input['gameId'] ?? null;
        $status = $input['status'] ?? '';

        if ($gameId === null || !ctype_digit((string) $gameId)) {
            $this->json(['success' => false, 'message' => 'Missing or invalid game id.'], 400);
        }

        $result = $this->adminService->setStatus((int) $gameId, (string) $status);
        $this->respond($result);
    }

    # POST api/admin/report/resolve
    public function resolveReport() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $reportId = $input['reportId'] ?? null;

        if ($reportId === null || !ctype_digit((string) $reportId)) {
            $this->json(['success' => false, 'message' => 'Missing or invalid report id.'], 400);
        }

        $this->respond($this->adminService->resolveReport((int) $reportId, $adminId));
    }

    # POST api/admin/reports/resolve-all
    public function resolveAllReports() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $gameId = $input['gameId'] ?? null;

        if ($gameId === null || !ctype_digit((string) $gameId)) {
            $this->json(['success' => false, 'message' => 'Missing or invalid game id.'], 400);
        }

        $this->respond($this->adminService->resolveAllReports((int) $gameId, $adminId));
    }

    # GET api/admin/users
    public function users() {
        $adminId = $this->requireAdminApi();

        $this->json($this->adminService->getUsers($adminId));
    }

    # POST api/admin/user/suspend
    public function suspendUser() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $userId = $this->idFrom($input, 'userId', 'Missing or invalid user id.');

        $this->respond($this->adminService->suspendUser(
            $adminId,
            $userId,
            (string) ($input['mode'] ?? ''),
            $input['days'] ?? null
        ));
    }

    # POST api/admin/user/unsuspend
    public function unsuspendUser() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $userId = $this->idFrom($input, 'userId', 'Missing or invalid user id.');

        $this->respond($this->adminService->unsuspendUser($adminId, $userId));
    }

    # POST api/admin/user/admin-access
    public function setAdminAccess() {
        $adminId = $this->requireAdminApi();
        $this->requirePost();

        $input = $this->jsonInput();
        $userId = $this->idFrom($input, 'userId', 'Missing or invalid user id.');

        $this->respond($this->adminService->setAdminAccess(
            $adminId,
            $userId,
            filter_var($input['makeAdmin'] ?? false, FILTER_VALIDATE_BOOLEAN)
        ));
    }

    // ═════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════

    # Reads a positive whole-number id from the request body, or stops with a 400.
    private function idFrom(array $input, string $key, string $message): int {
        $value = $input[$key] ?? null;

        if ($value === null || !ctype_digit((string) $value)) {
            $this->json(['success' => false, 'message' => $message], 400);
        }

        return (int) $value;
    }


    private function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'POST method required'], 405);
        }
    }

    # Turns a service result into a JSON response (uses its 'code' on failure).
    private function respond(array $result): void {
        if (empty($result['success'])) {
            $status = $result['code'] ?? 400;
            unset($result['code']);
            $this->json($result, $status);
        }

        $this->json($result);
    }
}
