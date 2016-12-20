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
Statusvariable     | Quellvariable, über welche der Automat getriggert wird.
Dauer              | Zeit, bis das Licht(Aktor) wieder ausgeschaltet werden soll.


### 5. Statusvariablen und Profile

Die Statusvariablen/Timer werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

Name               | Typ       | Beschreibung
------------------ | --------- | ----------------
TriggerEvent       | Timer     | Event Trigger, welcher bei Veränderung des Zustandes (Schaltvorgang) ausgelöst wird.
TriggerTimer       | Timer     | Timmer zum auslösen der AUS-Schaltung nach vordefinierter Zeit.

Es werden keine zusätzlichen Profile benötigt.

### 6. WebFront

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

`void LTA_Activate(int $InstanzID, boolean $Value);`  
Aktiviert (true) oder deaktiviert (false) den Timer mit der InstanzID $InstanzID.  
Die Funktion liefert keinerlei Rückgabewert.  

Beispiel:  
`LTA_Activate(12345, true);`  

`void LTA_Trigger(int $InstanzID);`  
Schaltet das Licht (den Actor) aus.  
Die Funktion liefert keinerlei Rückgabewert.  

Beispiel:  
`LTA_Trigger(12345);`  
