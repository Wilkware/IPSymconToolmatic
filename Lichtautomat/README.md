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

* Berechnet über eine eingerichtete Formel einen Wert aus einer ausgewählten Quellvariable.
* Bei Variablenänderung der Quellvariable wird der Wert automatisch neuberechnet.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Wilkware/IPSymconToolmatic.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Lichtautomat'-Modul unter dem Hersteller '(Sonstige)' aufgeführt.

__Konfigurationsseite__:

Name               | Beschreibung
------------------ | ---------------------------------
Statusvariable     | Quellvariable, über welche der Automat getriggert wird.
Dauer              | Zeit, bis das Licht(Aktor) wieder ausgeschaltet werden soll.


### 5. Statusvariablen und Profile

Keine.


### 6. WebFront

Es ist keine weitere Steuerung oder gesonderte Darstellung integriert.

### 7. PHP-Befehlsreferenz

void LTA_Trigger(integer $InstanzID, integer $Value);`
Schaltet das Automatenmoduls mit der InstanzID $InstanzID .
Die Funktion liefert keinerlei Rückgabewert.  
Beispiel:  
`LTA_Trigger(12345, false);`
