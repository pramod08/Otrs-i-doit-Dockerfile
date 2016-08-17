<?php
/**
 * Custom Sprachset für "Deutsch"
 *
 * Eigene Konstanten hinzufügen:
 * $g_langcache["LC__MY_CUSTOM_LANGUAGE_CONSTANT"] = "Meine eigene Sprachkonstante";
 *
 * Bestehende überschreiben bzw. verändern:
 * $g_langcache["LC__CMDB__CATG__SYSID"] = "System-Nummer";
 *
 * Wobei LC__* die Identifikation der Sprachkonstante ist. Hierbei können
 * Sprachkonstanten aus de.inc.php einfach überschrieben und nach dem oben beschriebenen
 * Beispiel eingefügt werden. Überschriebene Sprachkonstanten sind auf der Oberfläche
 * direkt gültig. Werden überschriebene Konstanten aus dieser Datei wieder
 * entfernt, wird der i-doit System Standard auf der Oberfläche angezeigt.
 *
 * Der zugewiesene Wert nach dem Zuweisungsoperator "=" ist der übersetzte Inhalt. Dieser sollte
 * stets in doppelte oder einfache Anführungszeichen gesetzt werden: "beispiel"
 *
 * Um diese Beispieldatei gültig zu machen muss das "example" aus dem Dateinamen entfernt werden,
 * so dass die Datei folgenden Namen erhält de_custom.inc.php. Somit ist die Datei Updatesicher und
 * wird nach einem i-doit Update nicht überschrieben.
 *
 * @name   i -doit custom language file
 * @copyright synetics GmbH
 * @global $g_langcache
 * @author    Dennis Stücken <dstuecken@synetics.de>
 * @version   1.0
 */

$g_langcache["LC__MY_CUSTOM_LANGUAGE_CONSTANT"] = "Meine eigene Sprachkonstante";