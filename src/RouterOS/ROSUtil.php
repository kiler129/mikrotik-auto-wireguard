<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\RouterOS;

class ROSUtil
{
    /**
     * @return array<string>
     */
    function listToArray(string $rosList): array
    {
        $out = [];
        foreach(\explode(',', $rosList) as $item) {
            $out[] = trim($item);
        }
        return $out;
    }
}
