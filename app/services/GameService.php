<?php
    class GameService{
        private Game $gameModel;
        private GameReport $gameReportModel;

        public function __construct() {
            $this->gameModel = new Game();
            $this->gameReportModel = new GameReport();
        }

        # Does this game exist at all (any status)? Used to validate an
        # incoming report before we bother inserting it.
        public function gameExists($gameId): bool {
            return $this->gameModel->exists($gameId);
        }

        # Files a new report against a game on behalf of the given user.
        # Returns the new report's id.
        public function reportGame($gameId, $userId, $reason, $details = null): int {
            return $this->gameReportModel->create($gameId, $userId, $reason, $details);
        }
        
        # Id of a random published game (or null if there are none yet)
        public function getRandomGameId($excludeGameId = null): ?int {
            return $this->gameModel->getRandomPublishedGameId($excludeGameId);
        }

        # function that returns certain info of all games
        public function getAllGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $games;
        }

        # function that returns certain info of all games
        public function getTopPlayedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByPlays($games);
        }

        # function that returns certain info of all games
        public function getTopRatedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByRating($games);
        }

        # function that returns certain info of all games
        public function getTopWeeklyRatedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByWeeklyRating($games);
        }

        # function that returns certain info of all games
        public function getTopWeeklyPlayedGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByWeeklyPlays($games);
        }

        # function that returns certain info of all games
        public function getTrendingGames($title, $genre) : array {
            $games = $this->gameModel->getAllGames($title, $genre);

            return $this->sortByBayesianScore($games);
        }

        # function that returns full game info into the game details modal
        # (and the game player page). $userId is optional — when given,
        # the returned Game also carries that user's own rating.
        public function getGameInfo($gameId, $userId = null) {
            $game = $this->gameModel->getGameById($gameId, $userId);

            return $game;
        }

        # Who may open a game's details / player?
        #   - anyone, if it is published
        #   - otherwise (under review / unlisted) only its developers and admins
        # This is what keeps under-review / unlisted games off every public page
        # while the owner can still reach them from their own profile.
        public function canView(Game $game, $userId): bool {
            if (Game::normalizeStatus($game->getStatus()) === Game::STATUS_PUBLISHED) {
                return true;
            }

            if (empty($userId)) {
                return false;
            }

            return Auth::isAdminUser($userId)
                || $this->gameModel->isDeveloper($game->getGameId(), $userId);
        }

        # Bumps totalPlays by 1. Called once when a game actually starts
        # loading in the player (not just when the page is viewed).
        public function incrementPlays($gameId): int {
            return $this->gameModel->incrementTotalPlays($gameId);
        }

        # Saves (or updates) a user's 1-5 star rating for a game.
        public function rateGame($gameId, $userId, $rating): array {
            return $this->gameModel->rateGame($gameId, $userId, $rating);
        }

        # Works out what the player should actually load for a given game,
        # based on the uploaded game file's extension:
        #   - .html / .htm  -> load directly, it's already playable
        #   - .zip          -> assumed to be an HTML5 export; extracted once
        #                      (cached after the first play) and pointed at
        #                      its index.html
        #   - .jar          -> browsers can't run Java bytecode; there is no
        #                      real "play in an iframe" for this, so it's
        #                      surfaced as a download instead of pretending
        #                      to play it
        #   - anything else -> unsupported
        # Returns ['type' => 'html'|'jar'|'unsupported', 'url' => string|null]
        # where url is relative to /public.
        public function resolvePlayable(Game $game): array {
            $gameFiles = $game->getGameFiles();

            if (empty($gameFiles)) {
                return ['type' => 'unsupported', 'url' => null];
            }

            $extension = strtolower(pathinfo($gameFiles, PATHINFO_EXTENSION));

            if ($extension === 'html' || $extension === 'htm') {
                $publicRoot = dirname(__DIR__, 2) . '/public';
                $this->injectAudioShim($publicRoot . '/' . ltrim($gameFiles, '/'));

                return ['type' => 'html', 'url' => $gameFiles];
            }

            if ($extension === 'jar') {
                return ['type' => 'jar', 'url' => $gameFiles];
            }

            if ($extension === 'zip') {
                return $this->resolveZippedHtml5Game($game, $gameFiles);
            }

            return ['type' => 'unsupported', 'url' => null];
        }

        # Extracts an HTML5 game's zip into a per-game folder (only once —
        # skipped if it's already been extracted) and locates its
        # index.html so the iframe has something to point at.
        private function resolveZippedHtml5Game(Game $game, string $gameFilesRelativePath): array {
            $publicRoot = dirname(__DIR__, 2) . '/public';
            $zipFullPath = $publicRoot . '/' . ltrim($gameFilesRelativePath, '/');

            if (!file_exists($zipFullPath)) {
                return ['type' => 'unsupported', 'url' => null];
            }

            $extractRelativeDir = 'uploads/games/extracted/' . $game->getGameId() . '/';
            $extractFullDir = $publicRoot . '/' . $extractRelativeDir;

            $indexRelativePath = $this->findIndexHtml($extractFullDir);

            if ($indexRelativePath === null) {
                // Not extracted yet (or extracted but empty/broken) — extract it now.
                if (!is_dir($extractFullDir)) {
                    mkdir($extractFullDir, 0755, true);
                }

                $zip = new ZipArchive();
                if ($zip->open($zipFullPath) === true) {
                    $zip->extractTo($extractFullDir);
                    $zip->close();
                }

                $indexRelativePath = $this->findIndexHtml($extractFullDir);
            }

            if ($indexRelativePath === null) {
                return ['type' => 'unsupported', 'url' => null];
            }

            // Always (re)check the shim — games extracted before it existed, or
            // before this version, would otherwise never get volume control.
            // It's idempotent, so an up-to-date file is left alone.
            $this->injectAudioShim($extractFullDir . $indexRelativePath);

            return ['type' => 'html', 'url' => $extractRelativeDir . $indexRelativePath];
        }

        # Injects a small script into a game's entry HTML that gives the parent page
        # (this site) real control over that game's audio, whatever engine made it
        # and whether it uses <audio>/<video>/new Audio() or the Web Audio API
        # (which is what Unity WebGL and most game engines actually use).
        #
        # Without it, the postMessage({type:'setVolume'/'setMuted'}) sent by
        # gamepage.js does nothing — an arbitrary uploaded game has no idea what
        # that message means.
        #
        # Idempotent: a file that already has the current version is left alone,
        # and an older version (v1) is replaced.
        private function injectAudioShim(string $indexFullPath): void {
            if (!is_file($indexFullPath) || !is_writable($indexFullPath)) {
                return;
            }

            $html = file_get_contents($indexFullPath);
            if ($html === false || strpos($html, self::AUDIO_SHIM_MARKER) !== false) {
                return; // already up to date, or couldn't read the file
            }

            // Drop the older shim (its marker comment has no " v2" after it)
            $html = preg_replace('#<script>\s*/\* yaw8-audio-shim(?! v2).*?</script>#s', '', $html) ?? $html;

            $shim = $this->buildAudioShimScript();
            $insert = function (array $m) use ($shim) {
                return $m[0] . $shim;
            };

            // As early as possible, so our wrapped AudioContext / media setters are
            // the ones the game's own scripts get. preg_replace_callback (not
            // preg_replace) so nothing in the script is read as a "$1" reference.
            $count = 0;
            $patched = preg_replace_callback('/<head(?:\s[^>]*)?>/i', $insert, $html, 1, $count);

            if ($count === 0) {
                $patched = preg_replace_callback('/<html(?:\s[^>]*)?>/i', $insert, $html, 1, $count);
            }
            if ($count === 0) {
                // A doctype must stay first, or the browser drops into quirks mode
                $patched = preg_replace_callback('/<!DOCTYPE[^>]*>/i', $insert, $html, 1, $count);
            }
            if ($count === 0 || $patched === null) {
                $patched = $shim . $html;
            }

            file_put_contents($indexFullPath, $patched, LOCK_EX);
        }

        private const AUDIO_SHIM_MARKER = 'yaw8-audio-shim v2';

        private function buildAudioShimScript(): string {
            return <<<'HTML'

<script>
/* yaw8-audio-shim v2 — injected by YAW8. Lets the parent page control this
   game's volume/mute via postMessage, whether it plays sound through
   <audio>/<video>/new Audio() or the Web Audio API (Unity, Godot, Phaser, Howler...). */
(function () {
  if (window.__yaw8AudioShim) return;
  window.__yaw8AudioShim = true;

  var master = { volume: 1, muted: false };
  function eff() { return master.muted ? 0 : master.volume; }

  // ── <audio> / <video> / new Audio() ─────────────────────────────────────
  // The game keeps reading/writing el.volume and el.muted as normal; we remember what
  // it asked for and apply (that x our master volume) to the real element.
  var proto = window.HTMLMediaElement && window.HTMLMediaElement.prototype;
  var wantedVol = new WeakMap();
  var wantedMuted = new WeakMap();
  var seen = new WeakSet();
  var tracked = [];
  var volDesc = proto && Object.getOwnPropertyDescriptor(proto, 'volume');
  var mutedDesc = proto && Object.getOwnPropertyDescriptor(proto, 'muted');

  function track(el) {
    if (seen.has(el)) return;
    seen.add(el);
    tracked.push(typeof WeakRef === 'function' ? new WeakRef(el) : { deref: function () { return el; } });
    if (tracked.length > 2000) {
      tracked = tracked.filter(function (r) { return r.deref() !== undefined; });
    }
  }

  function applyMedia(el) {
    try {
      if (!wantedVol.has(el)) wantedVol.set(el, volDesc.get.call(el));
      if (!wantedMuted.has(el)) wantedMuted.set(el, mutedDesc.get.call(el));
      volDesc.set.call(el, wantedVol.get(el) * eff());
      mutedDesc.set.call(el, wantedMuted.get(el) || master.muted);
    } catch (e) {}
  }

  if (proto && volDesc && mutedDesc && volDesc.set && mutedDesc.set) {
    Object.defineProperty(proto, 'volume', {
      configurable: true,
      enumerable: volDesc.enumerable,
      get: function () { return wantedVol.has(this) ? wantedVol.get(this) : volDesc.get.call(this); },
      set: function (v) {
        var n = Number(v);
        if (!(n >= 0 && n <= 1)) return volDesc.set.call(this, v); // let the browser throw as usual
        wantedVol.set(this, n);
        track(this);
        applyMedia(this);
      }
    });

    Object.defineProperty(proto, 'muted', {
      configurable: true,
      enumerable: mutedDesc.enumerable,
      get: function () { return wantedMuted.has(this) ? wantedMuted.get(this) : mutedDesc.get.call(this); },
      set: function (v) {
        wantedMuted.set(this, !!v);
        track(this);
        applyMedia(this);
      }
    });

    var origPlay = proto.play;
    proto.play = function () {
      track(this);
      applyMedia(this);
      return origPlay.apply(this, arguments);
    };
  }

  // ── Web Audio ───────────────────────────────────────────────────────────
  // Every AudioContext gets one master GainNode in front of its real destination,
  // and ctx.destination is redirected to it, so the game's whole graph goes through it.
  var contexts = [];

  function wrapContext(name) {
    var Native = window[name];
    if (typeof Native !== 'function') return;

    var Wrapped = function () {
      var args = Array.prototype.slice.call(arguments);
      var ctx = new (Function.prototype.bind.apply(Native, [null].concat(args)))();
      try {
        var gain = ctx.createGain();
        gain.gain.value = eff();
        gain.connect(ctx.destination);
        Object.defineProperty(ctx, 'destination', {
          configurable: true,
          get: function () { return gain; }
        });
        ctx.__yaw8Gain = gain;
        contexts.push(ctx);
      } catch (e) {}
      return ctx;
    };
    Wrapped.prototype = Native.prototype;
    try { Object.setPrototypeOf(Wrapped, Native); } catch (e) {}
    window[name] = Wrapped;
  }

  wrapContext('AudioContext');
  wrapContext('webkitAudioContext');

  // ── Apply master volume / mute everywhere ───────────────────────────────
  function applyAll() {
    contexts.forEach(function (ctx) {
      try { ctx.__yaw8Gain.gain.value = eff(); } catch (e) {}
    });
    tracked.forEach(function (ref) {
      var el = ref.deref();
      if (el) applyMedia(el);
    });
    try {
      document.querySelectorAll('audio, video').forEach(function (el) {
        track(el);
        applyMedia(el);
      });
    } catch (e) {}
  }

  window.addEventListener('message', function (e) {
    if (e.source !== window.parent) return;
    var d = e.data;
    if (!d || typeof d !== 'object') return;

    if (d.type === 'setVolume') {
      var v = Number(d.volume);
      master.volume = isFinite(v) ? Math.max(0, Math.min(1, v)) : 1;
      applyAll();
    } else if (d.type === 'setMuted') {
      master.muted = !!d.muted;
      applyAll();
    }
  });

})();
</script>
HTML;
        }

        # Recursively looks for an index.html under $dir (some HTML5 exports
        # nest it inside a subfolder rather than putting it at the zip root).
        # Returns the path relative to $dir, or null if none was found.
        private function findIndexHtml(string $dir): ?string {
            if (!is_dir($dir)) {
                return null;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (strtolower($file->getFilename()) === 'index.html') {
                    return ltrim(substr($file->getPathname(), strlen($dir)), '/');
                }
            }

            return null;
        }

        # sorts games by total plays
        private function sortByPlays(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getTotalPlays() <=> $first->getTotalPlays();
            });

            return $games;
        }

        # sorts games by average rating
        private function sortByRating(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getAvgRating() <=> $first->getAvgRating();
            });

            return $games;
        }

        # sorts games by average rating
        private function sortByWeeklyRating(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getTotalRatingWeekly() <=> $first->getTotalRatingWeekly();
            });

            return $games;
        }

        private function sortByWeeklyPlays(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getTotalPlaysWeekly() <=> $first->getTotalPlaysWeekly();
            });

            return $games;
        }

        private function sortByBayesianScore(array $games): array {
            usort($games, function (Game $first, Game $second) {
                return $second->getBayesianScore() <=> $first->getBayesianScore();
            });

            return $games;
        }
    }
?>