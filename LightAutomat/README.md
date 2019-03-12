# Lichtautomat

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

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
* _NEU_: Statusvariable muss nicht von einer HM-Instanze sein, kann auch einfach eine boolsche Variable sein.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Wilkware/IPSymconToolmatic.git`

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

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.  
Der Dauerbetrieb kann über einen einfachen Switch im WF realsiert werden.  
Die Wartezeit kann auch über ein Textfeld oder Variablenprofil und Script gesteuert (TLA_Duration) werden.

### 7. PHP-Befehlsreferenz

`void TLA_Trigger(int $InstanzID);`  
Schaltet das Licht (den Actor) aus.  
Die Funktion liefert keinerlei Rückgabewert.  

Beispiel:  
`TLA_Trigger(12345);`  

`void TLA_Duration(int $InstanzID, int x);`  
Setzt die Wartezeit (Timer) auf die neuen 'x' Minuten.  
Die Funktion liefert keinerlei Rückgabewert.

Beispiel:  
`TLA_Duration(12345, 10);`  
Setzt die Wartezeit auf 10 Minuten.
