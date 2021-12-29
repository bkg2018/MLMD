<?php
/**
 * Multilingual Markdown generator - Version system
 *
 * @package   mlmd_version
 * @author    Francis Piérot <fpierot@free.fr>
 * @copyright 2020 Francis Piérot
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @link      TODO
 */
namespace MultilingualMarkdown
{
    $VERSION = "1.0.7";
    function GetVersion(): string
    {
        global $VERSION;
        return $VERSION;
    }
}