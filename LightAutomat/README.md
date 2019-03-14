[![Version](https://img.shields.io/badge/Symcon-PHP--Modul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Product](https://img.shields.io/badge/Symcon%20Version-4.1%20%3E-blue.svg)](https://www.symcon.de/produkt/)
[![Version](https://img.shields.io/badge/Modul%20Version-3.2.20170322-orange.svg)](https://github.com/Wilkware/IPSymconToolmatic)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://github.styleci.io/repos/76893952/shield?style=flat)](https://github.styleci.io/repos/76893952)

# Lichtautomat
Überwacht und schaltet das Licht automatisch nach einer bestimmten Zeit wieder aus.

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

* Überwacht und schaltet das Licht automatisch nach einer bestimmten Zeit wieder aus.
* Dabei wird der Schaltstatus eines HomeMatic Tasters (z.B. HM-LC-Sw1PBU-FM) überwacht.
* Bei Variablenänderung der Statusvariable (STATE)) wird ein Timer gestartet.
* Nach eingestellter Zeit wird der Staus wieder zurückgestellt ("STATE" = flase).
* Sollte das Licht schon vorher manuell aus geschalten worden sein, wird der Timer deaktiviert.
* Zusätzlich bzw. ausschließlich kann ein Script ausgeführt werden. 
* Dauerbetrieb mittels hinterlegter boolean Variable, wenn **true** wird kein Timer gestartet.
* Modul mit Bewegungsmelder, wenn dieser aktiv ist wird der Timer immer wieder erneuert.
* Über die Funktion _TLA_Duration(id, minuten)_ kann die Wartezeit via Script (WebFront) gesetzt werden.
* Statusvariable muss nicht von einer HM-Instanze sein, kann auch einfach eine boolsche Variable sein.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.1 

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.<br />
`https://github.com/Wilkware/IPSymconToolmatic` oder `git://github.com/Wilkware/IPSymconToolmatic.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Lichtautomat'-Modul (Alias: Treppenautomat, Tasterschaltung) unter dem Hersteller '(Sonstige)' aufgeführt.

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
StateVariable      | Quellvariable, über welche der Automat getriggert wird.  Meistens im Kanal 1 von HomeMatic Geräten zu finden und ist vom Typ boolean  und hat den Namen "STATE" (z.B: wenn man die Geräte mit dem HomeMatic Configurator anlegen lässt.).
Duration           | Zeit, bis das Licht(Aktor) wieder ausgeschaltet werden soll.
MotionVariable     | Statusvariable eines Bewegungsmelders (true = Anwesend; false = Abwesend).
PermanentVariable  | Statusvariable, über welchen der Automat zeitweise deaktiviert werden kann (true = Dauerbetrieb).
ExecScript         | Schalter, ob zusätzlich ein Script ausgeführt werden soll (IPS_ExecScript).
ScriptVariable     | Script(auswahl), welches zum Einsatz kommen soll.
OnlyScript         | Schalter, ob nur das Script ausgeführt werden soll, kein Schaltvorgang.
OnlyBool           | Schalter, ob die Statusvariable über HM-Befehl geschaltet werden soll oder einfach ein nur einfacher boolscher Switch gemacht werden soll.


### 5. Statusvariablen und Profile

Die Statusvariablen/Timer werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
TriggerTimer       | Timer     | Timmer zum auslösen der AUS-Schaltung nach vordefinierter Zeit.

Es werden keine zusätzlichen Profile benötigt.

### 6. WebFront

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.<br />
Der Dauerbetrieb kann über einen einfachen Switch im WF realsiert werden.<br />
Die Wartezeit kann auch über ein Textfeld oder Variablenprofil und Script gesteuert (TLA_Duration) werden.

### 7. PHP-Befehlsreferenz

`void TLA_Trigger(int $InstanzID);`<br />
Schaltet das Licht (den Actor) aus.<br />
Die Funktion liefert keinerlei Rückgabewert.<br />

Beispiel:<br />
`TLA_Trigger(12345);`<br />

`void TLA_Duration(int $InstanzID, int x);`<br />
Setzt die Wartezeit (Timer) auf die neuen 'x' Minuten.<br />
Die Funktion liefert keinerlei Rückgabewert.

Beispiel:<br />
`TLA_Duration(12345, 10);`<br />
Setzt die Wartezeit auf 10 Minuten.

### 8. Versionshistorie

v3.2.20170322
* _FIX_: Anpassungen für IPS Version 5

v3.1.20170120
* _FIX_: Korrekte Auswertung der Schaltvariable.

v3.0.20170109
* _NEU_: Dauerbetrieb miitels hinterlegter boolean Variable, wenn _true_ wird kein Timer gestartet.
* _NEU_: Modul mit Bewegungsmelder, wenn dieser aktiv ist wird der Timer immer wieder erneuert.
* _NEU_: Über die Funktion _TLA_Duration(id, minuten)_ kann die Wartezeit via Script (WebFront) gesetzt werden.

v2.0.20170101
* _FIX_: Umstellung auf Nachrichten (RegisterMessage/MessageSink)
* _NEU_: Erweiterung zum Ausführen eines Scriptes

v1.0.20161220
* _NEU_: Initialversion

### Entwickler
* Heiko Wilknitz ([@wilkware](https://github.com/wilkware))

### Spenden
Die Software ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Entwickler bitte hier:<br />
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8816166" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

### Lizenz
[![Licence](https://licensebuttons.net/i/l/by-nc-sa/transparent/00/00/00/88x31-e.png)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
