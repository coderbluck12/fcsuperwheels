<?php
session_start();

// ── GD availability guard ─────────────────────────────────────────────────────
if (!function_exists('imagecreatetruecolor')) {
    // GD not available — generate a code anyway and output a simple SVG fallback
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code  = '';
    for ($i = 0; $i < 5; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $_SESSION['captcha_code'] = $code;
    // Return the code as a readable SVG so the user can still see it
    header('Content-Type: image/svg+xml');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="180" height="58">'
       . '<rect width="180" height="58" fill="#f0f2f8" rx="4"/>'
       . '<text x="90" y="38" font-family="monospace" font-size="26" font-weight="bold" '
       . 'fill="#1e3a8a" text-anchor="middle" letter-spacing="6">' . htmlspecialchars($code) . '</text>'
       . '<text x="90" y="54" font-family="sans-serif" font-size="9" fill="#9ca3af" text-anchor="middle">'
       . 'GD unavailable – SVG fallback</text>'
       . '</svg>';
    exit;
}

// Generate a random 5-character code (no lookalike chars)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code  = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}
$_SESSION['captcha_code'] = $code;

// ── Dimensions ───────────────────────────────────────────────────────────────
$width  = 180;
$height = 58;

$img = imagecreatetruecolor($width, $height);

// ── Background: pale blue-grey gradient ──────────────────────────────────────
for ($y = 0; $y < $height; $y++) {
    $shade = (int)(238 + $y * 0.15);
    $c = imagecolorallocate($img, $shade, $shade + 2, min(255, $shade + 8));
    imageline($img, 0, $y, $width, $y, $c);
}

// ── Noise dots ───────────────────────────────────────────────────────────────
for ($i = 0; $i < 700; $i++) {
    $nc = imagecolorallocate($img, rand(140,210), rand(140,210), rand(140,210));
    imagesetpixel($img, rand(0, $width - 1), rand(0, $height - 1), $nc);
}

// ── Wavy background lines ────────────────────────────────────────────────────
for ($i = 0; $i < 5; $i++) {
    $lc  = imagecolorallocate($img, rand(170,220), rand(170,220), rand(185,225));
    $y0  = rand(5, $height - 5);
    for ($x = 0; $x < $width - 1; $x++) {
        $y1 = (int)($y0 + 6 * sin(($x + $i * 20) / 12.0));
        $y2 = (int)($y0 + 6 * sin(($x + 1 + $i * 20) / 12.0));
        imageline($img, $x, $y1, $x + 1, $y2, $lc);
    }
}

// ── Draw characters using GD built-in font (no freetype needed) ──────────────
// Font 5 is the largest built-in GD font: 9×15 px per char
$font      = 5;
$charW     = imagefontwidth($font);
$charH     = imagefontheight($font);
$totalW    = strlen($code) * $charW + (strlen($code) - 1) * 8; // spacing
$startX    = (int)(($width - $totalW) / 2);

for ($i = 0; $i < strlen($code); $i++) {
    // Random dark colour per character
    $r     = rand(20,  90);
    $g     = rand(20,  90);
    $b     = rand(80, 170);
    $color = imagecolorallocate($img, $r, $g, $b);

    // Vertical jitter
    $y = (int)(($height - $charH) / 2) + rand(-6, 6);
    $x = $startX + $i * ($charW + 8);

    // Draw the character
    imagestring($img, $font, $x, $y, $code[$i], $color);

    // Draw a duplicate shifted by 1px for a pseudo-bold effect
    $bold = imagecolorallocate($img, max(0,$r-20), max(0,$g-20), max(0,$b-20));
    imagestring($img, $font, $x + 1, $y, $code[$i], $bold);
}

// ── Overlay a few random arcs on top of the text ─────────────────────────────
for ($i = 0; $i < 3; $i++) {
    $ac = imagecolorallocate($img, rand(160,210), rand(160,210), rand(160,210));
    imagearc($img, rand(0, $width), rand(0, $height),
             rand(30, 80), rand(20, 45), 0, 360, $ac);
}

// ── Mild sine-wave distortion pass ───────────────────────────────────────────
$dest = imagecreatetruecolor($width, $height);
for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $sx = (int)($x + 3 * sin($y / 10.0));
        $sy = (int)($y + 2 * cos($x / 13.0));
        if ($sx >= 0 && $sx < $width && $sy >= 0 && $sy < $height) {
            imagesetpixel($dest, $x, $y, imagecolorat($img, $sx, $sy));
        } else {
            imagesetpixel($dest, $x, $y, imagecolorallocate($dest, 240, 242, 248));
        }
    }
}

// ── Border ───────────────────────────────────────────────────────────────────
$border = imagecolorallocate($dest, 180, 185, 200);
imagerectangle($dest, 0, 0, $width - 1, $height - 1, $border);

// ── Output ───────────────────────────────────────────────────────────────────
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

imagepng($dest);
imagedestroy($img);
imagedestroy($dest);
