<?php
class AdminService {
    private Game $gameModel;
    private User $userModel;
    private GameReport $reportModel;

    # The only statuses an admin is allowed to set.
    private const SETTABLE_STATUSES = [
        Game::STATUS_PUBLISHED,
        Game::STATUS_UNDER_REVIEW,
        Game::STATUS_UNLISTED,
    ];

    public function __construct() {
        $this->gameModel = new Game();
        $this->userModel = new User();
        $this->reportModel = new GameReport();
    }

    # Numbers for the stat cards at the top of the dashboard.
    public function getStats(): array {
        $counts = $this->gameModel->getStatusCounts();

        return [
            'totalUsers'  => $this->userModel->countAll(),
            'totalGames'  => $counts['total'],
            'published'   => $counts['published'],
            'underReview' => $counts['under_review'],
            'unlisted'    => $counts['unlisted'],
        ];
    }

    # Every game (any status) for the dashboard table.
    public function getGames(): array {
        return $this->gameModel->getAllForAdmin();
    }

    # Everything the game-details page needs: the game + its reports.
    public function getGameDetail($gameId): ?array {
        $game = $this->gameModel->getAdminGameDetail($gameId);

        if ($game === null) {
            return null;
        }

        $reports = $this->reportModel->getByGame($gameId);

        return [
            'game'        => $game,
            'reports'     => $reports,
            'openReports' => $this->countOpen($reports),
        ];
    }

    # Confirm publication / put under review / unlist.
    public function setStatus($gameId, $status): array {
        if (!in_array($status, self::SETTABLE_STATUSES, true)) {
            return ['success' => false, 'code' => 422, 'message' => 'Invalid status.'];
        }

        if (!$this->gameModel->exists($gameId)) {
            return ['success' => false, 'code' => 404, 'message' => 'Game not found.'];
        }

        if (!$this->gameModel->updateStatus($gameId, $status)) {
            return ['success' => false, 'code' => 500, 'message' => 'Could not update the game status.'];
        }

        return [
            'success' => true,
            'gameId'  => (int) $gameId,
            'status'  => $status,
            'stats'   => $this->getStats(),
        ];
    }

    # Resolve a single report. Returns the game's refreshed report list.
    public function resolveReport($reportId, $adminId): array {
        $report = $this->reportModel->find($reportId);

        if ($report === null) {
            return ['success' => false, 'code' => 404, 'message' => 'Report not found.'];
        }

        $this->reportModel->resolve($reportId, $adminId);

        return $this->reportsPayload((int) $report['gameId']);
    }

    # Resolve every open report on a game. Returns the refreshed report list.
    public function resolveAllReports($gameId, $adminId): array {
        if (!$this->gameModel->exists($gameId)) {
            return ['success' => false, 'code' => 404, 'message' => 'Game not found.'];
        }

        $this->reportModel->resolveAllForGame($gameId, $adminId);

        return $this->reportsPayload((int) $gameId);
    }

    // ═════════════════════════════════════════
    // USERS TAB
    // ═════════════════════════════════════════

    # Longest timed suspension an admin can set (use "indefinite" for anything longer).
    public const MAX_SUSPEND_DAYS = 365;

    # Accounts the acting admin is allowed to see.
    #   Admin              -> only non-admin accounts
    #   Supreme Overlord   -> non-admin accounts AND admins (never other Supreme Overlords or themselves)
    # Admin-related fields are only included for a Supreme Overlord, so a
    # normal Admin can never see them in the response either.
    public function getUsers($actorId): array {
        $isSupreme = Auth::isSupremeOverlordUser($actorId);
        $users = $this->userModel->getUsersForAdmin($isSupreme, $actorId);

        return [
            'success' => true,
            'viewer'  => ['isSupremeOverlord' => $isSupreme],
            'users'   => array_map(fn($u) => $this->presentUser($u, $isSupreme), $users),
        ];
    }

    # Suspend an account: $mode is 'indefinite' or 'timed' (then $days is 1..MAX_SUSPEND_DAYS).
    public function suspendUser($actorId, $targetId, $mode, $days): array {
        $target = $this->loadModeratableUser($actorId, $targetId);
        if (isset($target['success'])) {
            return $target; // an error result
        }

        if ($mode === 'indefinite') {
            $days = null;
        } elseif ($mode === 'timed') {
            if (!is_numeric($days) || (int) $days != $days || $days < 1 || $days > self::MAX_SUSPEND_DAYS) {
                return ['success' => false, 'code' => 422,
                        'message' => 'Enter a whole number of days from 1 to ' . self::MAX_SUSPEND_DAYS . '.'];
            }
            $days = (int) $days;
        } else {
            return ['success' => false, 'code' => 422, 'message' => 'Choose indefinite or timed.'];
        }

        if (!$this->userModel->suspend($targetId, $days)) {
            return ['success' => false, 'code' => 500, 'message' => 'Could not suspend the account.'];
        }

        return $this->userPayload($actorId, $targetId);
    }

    public function unsuspendUser($actorId, $targetId): array {
        $target = $this->loadModeratableUser($actorId, $targetId);
        if (isset($target['success'])) {
            return $target;
        }

        if (!$this->userModel->unsuspend($targetId)) {
            return ['success' => false, 'code' => 500, 'message' => 'Could not unsuspend the account.'];
        }

        return $this->userPayload($actorId, $targetId);
    }

    # Supreme Overlord only: $makeAdmin = true promotes a member to Admin, false demotes an Admin to Member.
    public function setAdminAccess($actorId, $targetId, bool $makeAdmin): array {
        if (!Auth::isSupremeOverlordUser($actorId)) {
            return ['success' => false, 'code' => 403, 'message' => 'Only a Supreme Overlord can do that.'];
        }

        $target = $this->loadModeratableUser($actorId, $targetId);
        if (isset($target['success'])) {
            return $target;
        }

        $isAdmin = Auth::isAdminRole($target['role']);

        if ($makeAdmin && $isAdmin) {
            return ['success' => false, 'code' => 422, 'message' => $target['name'] . ' is already an admin.'];
        }
        if (!$makeAdmin && !$isAdmin) {
            return ['success' => false, 'code' => 422, 'message' => $target['name'] . ' is not an admin.'];
        }

        if (!$this->userModel->updateRole($targetId, $makeAdmin ? 'Admin' : 'Member')) {
            return ['success' => false, 'code' => 500, 'message' => 'Could not change the role.'];
        }

        return $this->userPayload($actorId, $targetId);
    }

    # Loads the target account and checks the acting admin may touch it.
    # Returns the account array, or an error result (which has a 'success' key).
    #   - nobody can act on their own account
    #   - Supreme Overlord accounts can't be changed by anyone
    #   - a normal Admin can't touch admins at all (answered with "not found",
    #     so it doesn't even confirm that such an account exists)
    private function loadModeratableUser($actorId, $targetId): array {
        $notFound = ['success' => false, 'code' => 404, 'message' => 'User not found.'];
        $target = $this->userModel->getUserForAdmin($targetId);

        if ($target === null) {
            return $notFound;
        }

        if ((int) $target['userId'] === (int) $actorId) {
            return ['success' => false, 'code' => 403, 'message' => "You can't do that to your own account."];
        }

        $actorIsSupreme = Auth::isSupremeOverlordUser($actorId);

        if (Auth::isSupremeOverlordRole($target['role'])) {
            return $actorIsSupreme
                ? ['success' => false, 'code' => 403, 'message' => "Supreme Overlord accounts can't be changed."]
                : $notFound;
        }

        if (Auth::isAdminRole($target['role']) && !$actorIsSupreme) {
            return $notFound;
        }

        return $target;
    }

    # The refreshed account, in the shape the Users table expects.
    private function userPayload($actorId, $targetId): array {
        $isSupreme = Auth::isSupremeOverlordUser($actorId);
        $user = $this->userModel->getUserForAdmin($targetId);

        return [
            'success' => true,
            'user'    => $this->presentUser($user, $isSupreme),
        ];
    }

    # What the browser receives for one account. 'role' / 'isAdmin' are only
    # sent to a Supreme Overlord.
    private function presentUser(array $user, bool $viewerIsSupreme): array {
        $out = [
            'userId'         => $user['userId'],
            'name'           => $user['name'],
            'username'       => $user['username'],
            'email'          => $user['email'],
            'avgRating'      => $user['avgRating'],
            'ratingCount'    => $user['ratingCount'],
            'suspended'      => $user['suspended'],
            'suspendedUntil' => $user['suspendedUntil'],
            'indefinite'     => $user['indefinite'],
        ];

        if ($viewerIsSupreme) {
            $out['isAdmin'] = Auth::isAdminRole($user['role']);
        }

        return $out;
    }

    private function reportsPayload(int $gameId): array {
        $reports = $this->reportModel->getByGame($gameId);

        return [
            'success'     => true,
            'gameId'      => $gameId,
            'reports'     => $reports,
            'openReports' => $this->countOpen($reports),
        ];
    }

    private function countOpen(array $reports): int {
        return count(array_filter($reports, fn($r) => $r['status'] === GameReport::STATUS_OPEN));
    }
}
