<?php
// src/Service/EmojiRemover.php
namespace App\Service;

class EmojiRemover
{
    public function remove(?string $text): string
    {
        if (empty($text)) return '';
        $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);
        $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);
        $text = preg_replace('/[\x{2702}-\x{27B0}]/u', '', $text);
        $text = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F000}-\x{1F02F}]/u', '', $text);
        $text = preg_replace('/[\x{1F0A0}-\x{1F0FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F100}-\x{1F1FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F200}-\x{1F2FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F300}-\x{1F3FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F400}-\x{1F4FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F500}-\x{1F5FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F600}-\x{1F6FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F700}-\x{1F7FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F800}-\x{1F8FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $text);
        $text = preg_replace('/[\x{1FA00}-\x{1FA6F}]/u', '', $text);
        $text = preg_replace('/[\x{1FA70}-\x{1FAFF}]/u', '', $text);
        $text = preg_replace('/[\x{2702}-\x{27B0}]/u', '', $text);
        $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);

        return trim($text);
    }
}
