<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CaptchaController extends Controller
{
    // config
    protected int $length = 6; // number of characters
    protected int $ttl = 300; // seconds (5 minutes)
    protected string $charset = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // avoid confusing chars like 0,O,1,I

    /**
     * Generate a captcha (SVG) and store answer in cache (hashed)
     *
     * Response:
     * {
     *   "captcha_id": "uuid",
     *   "svg": "data:image/svg+xml;base64,...",
     *   "expires_in": 300
     * }
     */
    public function generate(Request $request)
    {
        // optional: accept ?length=.. or ?complexity=..
        $length = (int) $request->query('length', $this->length);
        $length = max(3, min(8, $length));

        $code = $this->randomString($length);
        $id = (string) Str::uuid();

        // store hashed (not plaintext) - use simple hash
        $cacheKey = $this->cacheKey($id);
        Cache::put($cacheKey, password_hash(strtolower($code), PASSWORD_BCRYPT), $this->ttl);

        // build SVG
        $svg = $this->buildSvg($code);

        return response()->json([
            'captcha_id' => $id,
            'svg' => 'data:image/svg+xml;base64,' . base64_encode($svg),
            'expires_in' => $this->ttl,
        ]);
    }

    /**
     * Verify posted captcha
     *
     * POST body:
     * { "captcha_id": "<uuid>", "answer": "abcde" }
     *
     * Responses:
     * 200 { "ok": true }
     * 422 validation errors
     * 400 { "ok": false, "message": "expired or invalid" }
     */
    public function verify(Request $request)
    {
        $v = Validator::make($request->all(), [
            'captcha_id' => 'required|uuid',
            'answer' => 'required|string|min:1|max:32',
        ]);

        if ($v->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $v->errors()], 422);
        }

        $id = $request->input('captcha_id');
        $answer = strtolower(trim($request->input('answer')));

        $cacheKey = $this->cacheKey($id);
        $stored = Cache::get($cacheKey);

        if (!$stored) {
            return response()->json(['ok' => false, 'message' => 'Captcha expired or not found'], 400);
        }

        // verify password_hash-like
        $match = password_verify($answer, $stored);

        if ($match) {
            // one-time use: delete cache
            Cache::forget($cacheKey);
            return response()->json(['ok' => true]);
        }

        // don't reveal too much info
        return response()->json(['ok' => false, 'message' => 'Invalid captcha'], 400);
    }

    /* ---------------- helpers ---------------- */

    protected function cacheKey(string $id): string
    {
        return "captcha:{$id}";
    }

    protected function randomString(int $len): string
    {
        $chars = str_split($this->charset);
        $s = '';
        for ($i = 0; $i < $len; $i++) {
            $s .= $chars[random_int(0, count($chars) - 1)];
        }
        return $s;
    }

    /**
     * Build an SVG string for the provided code with some noise.
     * It's pure SVG (no GD), so works on any host.
     */
    protected function buildSvg(string $code): string
    {
        $w = 120 + (strlen($code) * 18);
        $h = 48;
        $bg = '#f7fafc';
        $textColor = '#0f172a';
        $fontSize = 28;

        // randomize some transform per char
        $y = intval($h / 2) + 8;
        $angleRange = 20;

        $chars = str_split($code);
        $x = 12;
        $charSvgs = '';
        foreach ($chars as $c) {
            $angle = rand(-$angleRange, $angleRange);
            $dx = rand(0, 4);
            $dy = rand(-3, 3);
            $charSvgs .= "<g transform=\"translate({$x},{$y}) rotate({$angle})\"><text x=\"0\" y=\"0\" font-family=\"sans-serif\" font-size=\"{$fontSize}\" font-weight=\"700\" fill=\"{$textColor}\">" . htmlspecialchars($c) . "</text></g>";
            $x += 18 + $dx;
        }

        // noise lines
        $lines = '';
        for ($i = 0; $i < 5; $i++) {
            $x1 = rand(0, $w);
            $y1 = rand(0, $h);
            $x2 = rand(0, $w);
            $y2 = rand(0, $h);
            $opacity = 0.15 + (rand(0, 40) / 100);
            $lines .= "<line x1=\"{$x1}\" y1=\"{$y1}\" x2=\"{$x2}\" y2=\"{$y2}\" stroke=\"#000\" stroke-opacity=\"{$opacity}\" stroke-width=\"1\" />";
        }

        // dots
        $dots = '';
        for ($i = 0; $i < 30; $i++) {
            $cx = rand(0, $w);
            $cy = rand(0, $h);
            $r = rand(0,2);
            $opacity = 0.08 + (rand(0, 40) / 100);
            $dots .= "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"#000\" fill-opacity=\"{$opacity}\" />";
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$w}" height="{$h}" viewBox="0 0 {$w} {$h}">
  <rect width="100%" height="100%" fill="{$bg}" rx="6" />
  <g transform="translate(6,0)">
    {$lines}
    {$dots}
    {$charSvgs}
  </g>
</svg>
SVG;
        return $svg;
    }
}
