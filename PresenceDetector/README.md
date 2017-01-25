# Präsenzmelder

### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Übernimmt die Bewewgungsdaten vom Melder und verarbeitet bzw. reicht sie weiter.
* Einstellung eines Helligkeitswertes, ab welchem weiterverarbeitet werden soll.
* Zusätzlich bzw. ausschließlich kann ein Script ausgeführt werden. 
* Über die Funktion _TPD_SetThreshold(id, wert)_ kann der Schwellwert der Helligkeit gesetzt werden.

### 2. Voraussetzungen

- IP-Symcon ab Version 4.x (getestet mit Version 4.1.534 auf RP3)

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`git://github.com/Wilkware/IPSymconToolmatic.git`

### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'Lichtautomat'-Modul (Alias: Treppenautomat, Tasterschaltung) unter dem Hersteller '(Sonstige)' aufgeführt.

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

`void TPD_SwitchState(int $InstanzID);`  
Schaltet bei hinreichender Bedingung die Schaltervariable an.  
Die Funktion liefert keinerlei Rückgabewert. 
Direkter Aufruf macht aber eigentlich kein Sinn. 

Beispiel:  
`TPD_SwitchState(12345);`  

`void TPD_SetThreshold(int $InstanzID, int wert);`  
Setzt den Helligkeits-Schwellwert auf den neuen Lux-'wert'.  
Die Funktion liefert true im Erfolgsfall.

Beispiel:  
`TPD_SetThreshold(12345, 50);`  
Setzt den Schwellwert auf 50 Lux.
