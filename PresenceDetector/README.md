[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-4.1%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-1.1.20170322-orange.svg)](https://github.com/Wilkware/IPSymconToolmatic)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/76893952/shield?style=flat)](https://github.styleci.io/repos/76893952)

# Präsenzmelder
Schaltet in Abhängikeit von Bewegung und Helligkeit hinterlegt Variablen bzw. führt ein Script aus.

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

* Übernimmt die Bewewgungsdaten vom Melder und verarbeitet bzw. reicht sie weiter.
* Einstellung eines Helligkeitswertes, ab welchem weiterverarbeitet werden soll.
* Zusätzlich bzw. ausschließlich kann ein Script ausgeführt werden. 
* Über die Funktion _TPD_SetThreshold(id, wert)_ kann der Schwellwert der Helligkeit gesetzt werden.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.1

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.<br />
`https://github.com/Wilkware/IPSymconToolmatic` oder `git://github.com/Wilkware/IPSymconToolmatic.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Präsensmelder'-Modul (Alias: Bewegungsmelder) unter dem Hersteller '(Sonstige)' aufgeführt.

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
MotionVariable     | Statusvariable eines Bewegungsmelders (true = Anwesend; false = Abwesend).
BrightnessVariable | Quellvariable, über welche die Helligkeit abgefragt werden kann, bei HmIP-SMI ist es ILLUMINATION.
ThresholdValue     | Schwellwert, von 0 bis 100 Lux.
SwitchVariable     | Zielvariable, die bei hinreichender Bedingung geschalten wird (true). 
ScriptVariable     | Script(auswahl), welches zum Einsatz kommen soll.
OnlyBool           | Schalter, ob die Statusvariable über HM-Befehl geschaltet werden soll oder einfach ein nur einfacher boolscher Switch gemacht werden soll.

### 5. Statusvariablen und Profile

Die Statusvariablen werden automatisch angelegt.

Es werden keine zusätzlichen Profile benötigt.

### 6. WebFront

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`void TPD_SwitchState(int $InstanzID);`<br />
Schaltet bei hinreichender Bedingung die Schaltervariable an.<br />
Die Funktion liefert keinerlei Rückgabewert.<br />
Direkter Aufruf macht aber eigentlich kein Sinn.<br />

Beispiel:  
`TPD_SwitchState(12345);`<br />

`void TPD_SetThreshold(int $InstanzID, int wert);`<br />
Setzt den Helligkeits-Schwellwert auf den neuen Lux-'wert'.<br />
Die Funktion liefert true im Erfolgsfall.<br />

Beispiel:<br />
`TPD_SetThreshold(12345, 50);`<br />
Setzt den Schwellwert auf 50 Lux.

### 8. Versionshistorie

v1.1.20170322
* _FIX_: Anpassungen für IPS Version 5

v1.0.20170125
* _NEU_: Initialversion

### Entwickler
* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

### Spenden
Die Software ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:<br />
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

### Lizenz
[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)