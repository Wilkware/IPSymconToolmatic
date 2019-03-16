[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-5.0%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.0.20170317-orange.svg)](https://github.com/Wilkware/IPSymconToolmatic)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/76893952/shield?style=flat)](https://github.styleci.io/repos/76893952)

# Rollladensteuerung
Modul zur Übersetzung der Laufzeit des Rollladenmotors zur Position der Lamellen.

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)
8. [Versionshistorie](#8-versionshistorie)

### 1. Funktionsumfang
* Ansteuerung der korrekten Öffnungsposition in Abhängigkeit der Laufzeit

### 2. Voraussetzungen

- IP-Symcon ab Version 5.0

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.<br />
`https://github.com/Wilkware/IPSymconToolmatic` oder `git://github.com/Wilkware/IPSymconToolmatic.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Rollladensteuerung'-Modul (Alias: Jalousiesteuerung) unter dem Hersteller '(Sonstige)' aufgeführt.

__Konfigurationsseite__:

Name                          | Beschreibung
------------------------------| ---------------------------------
Zuordnung                     | Zuordnungstabelle von Laufzeit zu Position

Die Laufzeit (Level) muss vorher manuell gestoppt und aus der 'Level' Gerätevariable ausgelesen werden!

### 5. Statusvariablen und Profile

Die Statusvariablen/Timer werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
Position             | Float     | Öffnungsgrad des Rollladens

Folgende Profile werden angelegt:

Name                 | Typ       | Beschreibung
-------------------- | --------- | ----------------
TSA.Position         | Float     | Öffnungsgrad in Prozent(-schritte)

### 6. WebFront

Die erzeugten Variable kann direkt ins Webfront verlinkt werden.

### 7. PHP-Befehlsreferenz

`float TSA_Position(int $InstanzID, float $Position);`
Fährt den Rolladen in die gewünschte Position.
Die Funktion liefert die prozentuale Laufzeit als Rückgabewert zurück.

Beispiel:
`TSA_Position(12345, 0.25);`<br />
Öffnet bzw. schließt den Rollladen auf 25%.<br />

### 8. Versionshistorie

v1.0.20190317
* _NEU_: Initialversion

### Entwickler
* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

### Spenden
Die Software ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:<br />
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

### Lizenz
[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
