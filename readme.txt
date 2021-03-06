=== Prosodia VGW OS für Zählmarken (VG WORT) ===
Contributors: raubvogel
Donate link: http://prosodia.de/
Tags: VG WORT, Zählmarke, Beitrag, T.O.M., Zählpixel, Geld, VGW, Verwertungsgesellschaft WORT, Prosodia, Verlag
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 3.11.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Verdienen Sie mit Ihren Beiträgen/Texten Geld durch die Integration von Zählmarken der VG WORT.

== Description ==

> <strong>Anleitungen</strong><br>
> Unter [http://prosodia.de/prosodia-vgw-os/](http://prosodia.de/prosodia-vgw-os/) gibt es bebilderte Anleitungen zum Plugin.

= Beschreibung und Funktionen =

Das Plugin *Prosodia VGW OS* (open source) vom Literaturverlag [Prosodia](http://prosodia.de/) und die [Verwertungsgesellschaft WORT (VG WORT)](http://www.vgwort.de/die-vg-wort.html) ermöglichen es Ihnen, *jährlich Geld* mit Ihren in WordPress geschriebenen Beiträgen zu verdienen. Die VG WORT ist ein deutscher, rechtsfähiger Verein, in dem sich Autoren zur gemeinsamen Verwertung von Urheberrechten zusammengeschlossen haben. Derart können Sie mit Ihren [Texten im Internet (Beiträge)](http://www.vgwort.de/verguetungen/auszahlungen/texte-im-internet.html), die aus mindestens *1.800 Zeichen* bestehen, von der Verwertung (Geld, Tantiemen) Gebrauch machen. Dazu ist eine kostenlose Mitgliedschaft bei der VG WORT erforderlich: [kostenlose Anmeldung](https://tom.vgwort.de/portal/login). Jeder Beitrag, für den Sie jährlich Geld bekommen wollen, muss eine gewissen Zahl an Lesern/Zugriffen aufweisen. Die Anzahl der Zugriffe für einen Beitrag wird von der VG WORT über *Zählmarken* ermittelt. Dabei wird jedem Beitrag ein eindeutiger Code – eine Zählmarke – zugeordnet. Besucht ein Leser einen Beitrag, wird die dem Beitrag zugeordnete Zählmarke an die VG WORT übermittelt und somit die Gesamtzahl der Leser für diesen Beitrag erhöht. Einmal im Jahr wird dann *pro* Beitrag Geld an Sie ausgeschüttet, abhängig davon wie hoch die Leserzahl eines Beitrags im vorherigen Jahr gewesen ist – die notwendige Mindestzahl wird von der VG WORT festgelegt (siehe auch [Wikipedia](http://de.wikipedia.org/wiki/Meldesystem_f%C3%BCr_Texte_auf_Internetseiten)). Je nach dem wie viele Beiträge Sie verfasst haben und wie häufig diese gelesen wurden, ist ein Verdienst von jährlich mehreren hundert bis tausend Euro durchaus nicht utopisch.

Die Hauptaufgabe von Prosodia VGW OS ist, Zählmarken und deren Zuordnungen zu Beiträgen bequem für Sie im WordPress-Administrationsbereich zu verwalten und sicherzustellen, dass keine Zählmarken verloren gehen oder mehrfach vergeben werden. Besonders dann, wenn Sie im Laufe der Zeit viele Texte mit Zählmarken versehen, werden Sie feststellen, dass eine manuelle Zuordnung durch direktes Einfügen der Zählmarken in Beiträgen in hinreichend großer Unüberschaubarkeit endet – dem wirkt Prosodia VGW OS durch Formalisierung und ausgeklügelter Technik entgegen. Im Folgenden werden die Funktionen und Fähigkeiten von Prosodia VGW OS kurz aufgelistet:

* Übersicht aller Zählmarken, Zählmarken-Zuordnungen und weiterer Daten mit vielen Filter- und Sortierfunktionen
* Massenbearbeitung von Zählmarken und Zählmarken-Zuordnungen
* Integration in die Beitrags-Übersicht („Alle Beiträge“) von WordPress
* automatisches Zuordnen von Zählmarken zu Beiträgen – auch massenhaft
* Anzeige der Zeichenanzahl eines Beitrags sowie der Anzahl fehlender Zeichen beim Schreiben
* Zeichenanzahl wird nach VG-WORT-Vorgabe berechnet – keine Bilder und Beschriftungen, Shortcodes, HTML-Tags usw.
* es können Beitrags-Typen (Beiträge, Seiten usw.) für die Zählmarken-Funktion ausgewählt werden
* private Zählmarken, Beitrags-Titel, -Texte und -Links können für eine Meldung schnell in die Zwischenablage kopiert werden
* importieren von Zählmarken aus CSV-Dateien, die von der VG WORT bereitgestellt werden
* importieren von Zählmarken aus CSV-Text oder manueller Eingabe
* importieren von Zählmarken und Zählmarken-Zuordnungen aus dem Plugin „Worthy“ von B. Holzmüller
* importieren von Zählmarken und Zählmarken-Zuordnungen aus dem Plugin „VG Wort“ von [Torben Leuschner](http://www.torbenleuschner.de/blog/922/vg-wort-wordpress-plugin/)
* importieren von Zählmarken und Zählmarken-Zuordnungen aus dem Plugin „VG-Wort Krimskram“ von [Heiner Otterstedt](https://wordpress.org/plugins/vgw-vg-wort-zahlpixel-plugin/)
* importieren von manuell zugeordneten Zählmarken aus Beiträgen – `<img>`-Tag wird erkannt und optional gelöscht
* nachträglicher Import von fehlenden privaten Zählmarken, falls die entsprechend öffentlichen bereits vorhanden sind
* exportieren von Zählmarken, Zählmarken-Zuordnungen und weiterer Daten als CSV-Datei mit Filter- und Sortierfunktionen
* Zählmarken (`<img>`-Tags) werden in den Beiträgen auf Ihrer Website ausgegeben
* Format der Zählmarkenausgabe kann frei angegeben werden (Platzhalter für Server und öffentliche Zählmarke)
* Unterstützung der Übertragung von Zählmarken (`<img>`-Tags) über verschlüsselte Verbindungen (TLS/SSL, https)
* Datenintegrität der Zählmarken und Zählmarken-Zuordnungen wird stets gewährleistet
* Zählmarken und Zählmarken-Zuordnungen werden in einer eigenen Datenbanktabelle gespeichert – hohe Leistung (getestet über 10.000 Beiträgen)
* inaktiv setzen von Zählmarken – Zählmarken-Zuordnung wird nicht aufgehoben, Zählmarke wird nicht ausgegeben
* Warnung wird ausgegeben, falls andere VG-WORT-Plugins aktiviert sind
* Datenschutz-Vorlage für die Datenschutz-Erklärung Ihrer Website, wenn Sie Zählmarken der VG WORT verwenden
* Möglichkeit der vollständigen Deinstallation und Löschung der Datenbanktabellen und Einstellungen
* läuft auf Multisite-Installationen

Schauen Sie sich bitte auch die [Bildschirm-Fotos (Screenshots)](/plugins/wp-vgwort/screenshots/) von Prosodia VGW OS an.

Das Plugin selbst wird mit stets aktuellen Software-Entwicklungswerkzeugen (für [PHP](http://php.net/)), Code-Überprüfungswerkzeugen und Code-Versionierungswerkzeugen in mehreren lokalen Entwicklungs- und Test-Umgebungen weiterentwickelt. Wir achten des Weiteren penibel darauf, dass keine PHP-Warnungen, -Hinweise und -Fehler durch unser Plugin im Normalbetrieb ausgegeben werden und dass wir uns stets an empfohlene Vorgehensweisen beim Entwickeln halten (dies scheint bei vielen Plugins leider nicht gängige Praxis zu sein). Daher sind wir – vermutlich zu recht – davon überzeugt, dass unser Plugin auch in Zukunft stabil und fehlerarm läuft.

= Eignung =

Was Prosodia VGW OS nicht leistet:

* Differenzierung der Zählmarken zwischen den Autoren eines Blogs
* keine direkte Interaktion mit [T.O.M.](https://tom.vgwort.de/portal/index), da von der VG WORT nicht unterstützt
* keine Warnung, wenn nicht zugeordnete Zählmarken knapp werden
* separate Behandlung von Lyrik (darf weniger als 1.800 Zeichen haben)
* Zuordnung von Zählmarken zu PDFs
* exklusiven Support – nur über die Community

Für wen es ungeeignet ist:

* für Blogs mit vielen Autoren
* für Unternehmen
* für Verlage ;-)

Einige Funktionen – insbesondere die Autoren-Verwaltung – sind bereits in der Verkaufsversion „Prosodia VGW“ integriert, welche demnächst günstig vertrieben wird.

= Kompatibilität =

Technische Voraussetzungen für Prosodia VGW OS sind:

* mindestens WordPress 3.8
* mindestens PHP 5.3

= Lizenz und Haftung =

Prosodia VGW OS wird von der [Max Heckel, Ronny Harbich – Prosodia GbR](http://prosodia.de/kontakt/) unter der GPLv2-Lizenz vertrieben, die unter [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html) nachzulesen ist. Sie vertreibt diese Software vollständig kostenlos und übernimmt für diese daher keine Haftung außer die vom Bürgerlichen Gesetzbuch (BGB) zwingend erforderliche. Der Haftungsausschluss soll – soweit wie mit dem BGB vereinbar – der GPLv2-Lizenz entsprechen.

Prosodia VGW OS wird von der VG WORT weder unterstützt noch von ihr vertrieben.

= Hilfe und Anleitungen =

Unter [http://prosodia.de/prosodia-vgw-os/](http://prosodia.de/prosodia-vgw-os/) gibt es bebilderte Anleitungen zum Plugin.

Wenn Sie einen Wunsch für eine neue Funktion haben oder Hilfe benötigen, treten Sie bitte mit uns in Kontakt:

* [FAQ-Seite](/plugins/wp-vgwort/faq/)
* [Support-Seite](/support/plugin/wp-vgwort)
* E-Mail-Adressen befindet sich im Plugin unter „Prosodia VGW OS“ → „Hilfe“

== Installation ==

= Installation =

Automatische Installation über den Plugin-Bereich im Administrationsbereich von WordPress oder manuell wie folgt:

1. Laden Sie die Plugin-Zip-Datei herunter.
1. Entpacken Sie die Zip-Datei ins WordPress-Plugin-Verzeichnis (wp-content/plugins/).
1. Aktivieren Sie das Plugin im Plugin-Bereich im Administrationsbereich.

== Frequently Asked Questions ==

= Häufige Probleme und wissenswertes =

= Anleitungen =

Unter [http://prosodia.de/prosodia-vgw-os/](http://prosodia.de/prosodia-vgw-os/) gibt es bebilderte Anleitungen zum Plugin.

= Aktualisierung auf Version 3.0.0 oder höher =

Da das Plugin ab Version 3.0.0 vollständig neu entwickelt wurde, ist es nicht mehr direkt mit den vorherigen Versionen kompatibel. Allerdings werden sämtliche Daten und Einstellungen aus den Vorgängerversionen nach der Aktualisierung übernommen – insbesondere die Zählmarken und deren Zuordnungen zu Beiträgen. Dies funktioniert nicht vollautomatisch, sondern erst nach einigen, wenigen Mausklicks. So werden nach der Aktualisierung im Administrationsbereich einige Warnungen angezeigt. Diese Warnungen enthalten Anweisungen und Links, wie die dargelegten Sachverhalte zu lösen sind – keine Sorge, dies ist mit wenigen Mausklicks erledigt. Und selbst wenn dabei etwas schief gehen sollte – wovon wir nicht ausgehen –, werden die alten Daten und Einstellungen nicht gelöscht. Das Einsetzen einer Version vor 3.0.0 ist stets möglich: [alte Versionen](/plugins/wp-vgwort/developers/).

Sollte das Plugin die ältere Version nicht erkennen, so führen Sie bitte manuell „Prosodia VGW OS“ → „Operationen“ → „Zählmarken aus altem VG-WORT-Plugin vor Version 3.0.0 importieren“ (Haken setzen) → „Alte Zählmarken und Zählmarken-Zuordnungen importieren“ (Schaltfläche) aus.

= „Parse error“ nach Update auf Version 3.0.0 oder höher =

Es erscheint der Fehler `Parse error: syntax error, unexpected T_FUNCTION, expecting ')' in …` nach dem Update des Plugins auf Version 3.0.0 oder höher. Dieser tritt in der Regel auf, wenn eine PHP-Version kleiner als 5.3 eingesetzt wird. Das Plugin setzt allerdings mindestens Version 5.3 voraus. Um das Problem zu beheben, sollten Sie sich an Ihren Systemadministrator wenden (siehe auch [PHP auf der englischen Wikipedia](http://en.wikipedia.org/wiki/PHP#Release_history)).

= Es werden keine Zählmarken aus CSV-Dateien importiert =

Die Spalten der [CSV-Dateien](http://de.wikipedia.org/wiki/CSV_%28Dateiformat%29), die von der VG WORT heruntergeladen werden können, sind mit Semikolon (;) getrennt. Werden CSV-Dateien mit [LibreOffice Calc](https://de.libreoffice.org/) oder Microsoft Excel geöffnet und wieder abgespeichert, kann es ja nach Einstellung vorkommen, dass das Semikolon durch einen Tabulator, Komma oder anderes Zeichen ersetzt wird. In diesem Fall kann das Plugin die Zählmarken in der CSV-Datei nicht mehr erkennen. Bitte verwenden Sie ausschließlich unveränderte CSV-Dateien für den Import.

= Zählmarken lassen sich nicht beim Bearbeiten eines Beitrags einfügen =

Im Gegensatz zur alten Plugin-Versionen sind die Felder im Bereich „Zählmarke für VG WORT“ beim Bearbeiten eines Beitrags nicht dazu da, um Zählmarken in das System einzufügen / zu importieren, sondern dienen lediglich der Zuordnung. Hier dürfen nur bereits importierte Zählmarken angeben werden. Normalerweise findet die Zuordnung durch Setzen des Häkchens „Zählmarke automatisch zuordnen“ (Standardeinstellung) automatisch statt. Am besten, Sie verfahren so: Importieren Sie zunächst Zählmarken der VG WORT als CSV-Datei unter „Import“. Dann sind neue Zählmarken im System. Als nächstes gehen Sie zum Beitrag und weisen diesem automatisch eine neue Zählmarke zu. Eine manuelle Zuordnung ist ebenfalls möglich: Dazu können Sie die bereits importierte, öffentliche Zählmarke angeben (die private findet das Plugin automatisch). Öffentliche Zählmarken sind Codes wie „00a0f12e1113423cc56ff5“ und keine `<img>`-HTML-Tags wie `<img src="http://vg04.met.vgwort.de/na/00a0f12e1113423cc56ff5" width="1" height="1" alt="">`. Zusammenfassend: unbenutzte Zählmarken von der VG WORT importieren, dann automatisch zu Beiträgen zuordnen lassen.

= Zeichenanzahl wird bei Verwendung von Shortcodes falsch berechnet =

Es besteht die Möglichkeit, die Einstellung „Prosodia VGW OS“ → „Operationen“ → „Shortcodes bei Berechnung der Zeichenanzahl auswerten“ zu aktivieren. Dann werden die Shortcodes in einem Beitrag aufgelöst und die Anzahl der Zeichen der Ausgabe der Shortcodes mitberechnet. Dies funktioniert allerdings nur, wenn der jeweilige Shortcode auch im Administrationsbereich aufgelöst werden kann. Plugins haben die Möglichkeit, ihre Shortcodes nur auf der eigentlichen Website – also nicht im Administrationsbereich – auflösen zu lassen. In diesem Fall kann Prosodia VGW OS die Ausgabe der Shortcodes nicht erhalten und folglich auch nicht die korrekte Zeichenanzahl bestimmen. Zur Zeit ist uns für dieses Problem leider keine Lösung bekannt. Davon abgesehen, kann stets eine Zählmarke zugeordnet werden, auch wenn die nötige Zeichenanzahl nicht erreicht wurde bzw. nicht korrekt ermittelt werden konnte.

== Screenshots ==

= Bildschirmfotos =

1. Zählmarken und Export.
2. Import von Zählmarken.
3. Einstellungen.
4. Komplexe Operationen und Einstellungen.
5. Integration in der Beitragsübersicht von WordPress (Spalte „Zeichen“, Filter)
6. Integration beim Bearbeiten eines Beitrags.
7. Zählmarkenausgabe (markiert) im HTML-Quelltext eines Beitrags.

== Changelog ==

= Änderungen =

= 3.11.0 =
* In der Zählmarken-Übersicht („Prosodia VGW OS“ → „Zählmarken“) können nun private Zählmarken, Beitrags-Titel, -Texte und -Links einfach in die Zwischenablage kopiert werden. Das Melden von Beiträgen bei der VG WORT verläuft damit schneller.
* Möglichkeit hinzugefügt, TLS/SSL (https) zur Übertragung von Zählmarken zu verwenden („Prosodia VGW OS“ → „Einstellungen“ → „Zählmarken“).
* Die Hilfe-Texte können nun über „i“-Symbole ein- und ausgeblendet werden. Die Seiten „Einstellungen“, „Operationen“ usw. sind daher übersichtlicher.
* Zählmarken und Zählmarken-Zuordnungen können nun aus dem Plugin „Worthy“ von B. Holzmüller importiert werden („Prosodia VGW OS“ → „Operationen“ → „Zählmarken aus dem Plugin ‚Worthy‘ von B. Holzmüller importieren“).

= 3.10.2 =
* Fehler behoben, der die korrekte Anzeige der Spalte „Zeichen“ in der Beitrags-Übersicht im Zusammenhang mit Caching-Plugins (namentlich W3 Total Cache) verhinderte.

= 3.10.1 =
* Fehler behoben, der verhinderte, dass die Massenbearbeitungen in der unteren Auswahlliste (z. B. „Zählmarke zuordnen“) in der Beitrags-Übersicht durchgeführt werden konnten.

= 3.10.0 =
* Neuen Filter „Zählm.-Format“ in der Zählmarken-Übersicht hinzugefügt, mit dem nach ungültigen Zählmarken gefiltert werden kann. Besonders sinnvoll, wenn manuelle Zählmarken aus Beiträgen importiert wurden, da diese eventuell durch falsche Eingabe ungültig sein könnten.
* Fehler behoben, der verhinderte, dass der Lade-Spinner im Bereich „Zählmarke der VG WORT“ in der Beitrags-Bearbeitung ab WordPress 4.2 angezeigt wurde.

= 3.9.0 =
* Die Zeichenanzahl der Auszüge von Beiträgen kann bei der Berechnung der Zeichenanzahl eines Beitrags nun mit einberechnet werden. Aktivierung unter „Einstellungen“.
* Das Plugin ist nun vollständig kompatibel zu WordPress 4.2, insbesondere im Hinblick auf die Änderung der Zeichenkodierung von `utf8` zu `utf8mb4` in der MySQL-Datenbank.

= 3.8.0 =
* Der Shortcode `[pvgw_post_stats]` wurde verbessert: Die Zeichenanzahl pro Blatt kann nun manuell eingestellt werden. Das Runden auf halbe Seiten wurde hinzugefügt.
* In der Beitrags-Übersicht werden „Zählmarke möglich“ und „Zählmarke zugeordnet“ in der Spalte „Zeichen“ zur besseren Übersicht farblich hervorgehoben. Dies kann in den Einstellungen wieder zurückgesetzt werden.

= 3.7.0 =
* Shortcode `[pvgw_post_stats]` hinzugefügt, über den die Zeichenanzahl und die Anzahl der Normseiten eines Beitrags ausgeben werden kann.

= 3.6.0 =
* Es können nun Zählmarken und Zählmarken-Zuordnungen vom Plugin „VG-Wort Krimskram“ von H. Otterstedt importiert werden.

= 3.5.0 =
* Beiträgen kann nun standardmäßig eine Zählmarke zugeordnet werden. Muss in den Einstellungen aktiviert werden.
* Absätze in den Seiten unter „Prosodia VGW OS“ haben nun eine maximale Breite, damit diese nicht mehr zu lang gesetzt werden.

= 3.4.6 =
* Link zu „Import“ wird nun auch in der Nachricht, dass zu wenig Zählmarken vorhanden sind, in der Beitrags-Übersicht angezeigt.
* Fehler behoben, der auftrat, wenn bei der Massenbearbeitung in der Beitrags-Übersicht keine Beiträge ausgewählt wurden (nur Plugin-Aktionen).

= 3.4.5 =
* Regulären Ausdruck zur Erkennung von manuellen Zählmarken in Beiträgen (deren Inhalt) verbessert.

= 3.4.4 =
* Fehler bei Code-Migration, die doppelte Einstellung „Zählmarken pro Seite in der Übersicht“ unter „Einstellungen“ verursacht hatte.

= 3.4.3 =
* Filter „Zuordnung“ in der Zählmarken-Übersicht hinzugefügt.
* Alle Operationen sind nun auf die ausgewählten Beitrags-Typen beschränkt.
* Plugin auf WordPress 4.1 getestet.
* Fehler behoben, die auftraten, wenn kein Beitrags-Typ ausgewählt wurde.
* Fehler behoben, der bei der Plugin-Deinitialisierung auftrat (Null-Referenz, bei Verwendung mit „NextGEN Gallery“).

= 3.4.2 =
* Verbesserung der Handhabung von nicht verfügbaren Beitrags-Typen bei Deaktivierung und Aktualisierung von Plugins/Themes, die eigene Beitrags-Typen definieren.
* Fehler bei Code-Migration, der die Deinstallationsmöglichkeit unter „Einstellungen“ entfernt hatte.

= 3.4.1 =
* Benutzer mit der Rolle „Mitarbeiter“ können nun Zählmarken zuordnen.
* Fehler behoben, der Import von CSV-Daten verhinderte (nur für PHP unter Version 5.5 relevant).

= 3.4.0 =
* Es können nun optional auch Zählmarken vom einem Verlags-Konto bei der VG WORT importiert werden (anderes CSV-Format).
* Die Zeichenanzahlen können nun in der Beitrags-Übersicht und in der Zählmarken-Übersicht für ausgewählte Beiträge neuberechnet werden.
* Die Zeichenanzahl im visuellen Beitrags-Editor wird jetzt genauer berechnet und ist jetzt mit dem textuellen Beitrags-Editor synchron.
* Fehler behoben (JavaScript), der den Beitrags-Editor unbrauchbar machte, wenn bei den Benutzereinstellungen „Beim Schreiben den WYSIWYG-Editor nicht benutzen“ aktiviert wurde.
* Fehler behoben, der anzeigte, dass die Zeichenanzahl nicht genügte, wenn Zählmarken in der Beitrags-Übersicht zugeordnet wurden.
* Fehler „Catchable fatal error: must be an instance of callable, instance of Closure given“ behoben (nur für PHP 5.3 relevant).

= 3.3.0 =
* Möglichkeit hinzugefügt, Shortcodes bei Berechnung der Zeichenanzahl mit auswerten zu lassen („Prosodia VGW OS“ → „Einstellungen“ → „Zeichenanzahl“).
* Möglichkeit hinzugefügt, die maximale Ausführungszeit für Operationen zu ändern, falls Operationen abbrechen („Prosodia VGW OS“ → „Einstellungen“ → „Verschiedenes“).
* Workaround für die Berechnung der Zeichenanzahl bei der Beitrags-Bearbeitung (manche Plugins manipulieren den visuellen Editor).

= 3.2.0 =
* Es sollte nun leichter verständlich sein, dass Zählmarken beim Bearbeiten eines Beitrags nur zugeordnet werden und nicht eingeben/importiert werden können (Benutzeroberfläche verbessert).
* Leistungsverbesserung bei der Auswahl der Beitrags-Typen und der Neuberechnung der Zeichenanzahlen aller Beiträge.
* In der Beitrags-Übersicht „Alle Beiträge“ wird jetzt „nicht berechnet“ angezeigt anstatt „0“, wenn die Zeichenanzahl noch nicht berechnet wurde.
* Fehler behoben, der das Anzeigen aller Beitrags-Typen unter „Operationen“ verhinderte.
* Nachricht im Administrationsbereich hinzugefügt, falls die zu importierende CSV-Datei (oder CSV-Text) ein ungültiges Format hat.

= 3.1.1 =
* Fehler bezüglich leerer Meta-Name-Option aus Plugin-Version < 3.0.0 behoben. Import aus alter Plugin-Version sollte nun in diesem Fall wieder möglich sein.
* Option „Meta-Name“ unter „Operationen“ hinzugefügt.

= 3.1.0 =
* Leistungsverbesserung (insbesondere geringere Arbeitsspeichernutzung) der Funktionen im Bereich „Operationen“.

= 3.0.1 =
* Fehler Behoben, der Import aus anderen Plugins verhinderte (closures unterstützen keinen Zugriff auf private members in PHP 5.3).

= 3.0.0 =
* Plugin wurde vollständig neu entwickelt.
* Viele neue Funktionen. Siehe Plugin-Beschreibung.
* Plugin-Name geändert.
* Unterstützung durch Prosodia – Verlag für Musik und Literatur.

= 2.1.6 =
* Fehlerhaften Link zu „Datenschutz“ behoben (Dank an Jan Eric Hellbusch).

= 2.1.5 =
* Fehlerbehebung (Dank an rrho).

= 2.1.4 =
* Fehlerbehebung.

= 2.1.3 =
* Name des Plugins geändert.
* Hinweis auf Datenschutzrichtlinien hinterlegt.

= 2.1.1 =
* Fehler im Export behoben.

= 2.1.1 =
* Diverse PHP Warnings behoben.
* Rechtschreibung und Ausdruck verbessert.
* PO-Datei für Übersetzer aktualisiert.
* Administrations-Bereich auf WordPress 3.8 angepasst.

= 2.1.0 =
* Kleinere Fehler behoben.
* Filterfunktion für Zählmarkenausgabe hinzugefügt.
* System der Versionsnummer-Vergabe umgestellt (http://semver.org/).

= 2.0.4 = 
* Zählmarken werden direkt vor dem </body>-Tag eingefügt.

= 2.0.3 = 
* Löschen-Schaltfläche hinzugefügt.

= 2.0.2 = 
* Fehlerbehebung.
* Sprachübersetzung/-änderung nun möglich.

= 2.0.1 = 
* Kompatibilität-Problem mit wp_minify behoben.

= 2.0.0 = 
* Neues Feature.

= 1.9 = 
* Fehlerbehebung.

= 1.8 = 
* Fehlerbehebung.

= 1.7 = 
* Zählmarke wird nur in Beiträgen und Seiten angezeigt.

= 1.6 = 
* Zählmarke auch auf Seiten möglich.

= 1.5 = 
* Anzeige von Inhalten mit weniger als 1800 Zeichen im Benutzerprofil.

= 1.4 =
* Probleme mit Shortcode behoben.
* Ausgabe der Zeichen im Editor angepasst.
* Filterfunktion für Zählmarke im Benutzerprofil.
* Feedback-Funktionen hinzugefügt.

= 1.3 =
* Speichern der Zählmarke bei vorhandener Zählmarke (Fehlerbehebung).

= 1.2 =
* Einbau Zählmarke (Fehlerbehebung).

= 1.1 =
* Spalte für Zählmarken in Beitragsübersicht angepasst.

= 1.0 =
* Initial-Release.

== Upgrade Notice ==

= 3.11.0 =
Kopieren von Beitrags-Texten usw. in die Zwischenablage in der Zählmarken-Übersicht möglich. TLS/SSL-Unterstützung. Hilfe-Texte über „i“-Symbole anzeigen. Zählmarken-Import vom Plugin „Worthy“ möglich.

= 3.10.2 =
Einen Fehler behoben.

= 3.10.1 =
Einen Fehler behoben.

= 3.10.0 =
Neuen Filter „Zählm.-Format“ hinzugefügt. Einen Fehler behoben.

= 3.9.0 =
Beitrags-Auszug kann bei Berechnung der Zeichenanzahl mit ausgewertet werden. Kompatibilität zu WordPress 4.2 hergestellt.

= 3.8.0 =
Shortcode `[pvgw_post_stats]` verbessert. Farbhervorhebung für Spalte „Zeichen“ in Beitrags-Übersicht hinzugefügt.

= 3.7.0 =
Shortcode `[pvgw_post_stats]` hinzugefügt.

= 3.6.0 =
Zählmarken und Zählmarken-Zuordnungen vom Plugin „VG-Wort Krimskram“ importierbar.

= 3.5.0 =
Beiträgen kann nun standardmäßig eine Zählmarke zugeordnet werden. Absätze in den Seiten unter „Prosodia VGW OS“ haben nun eine maximale Breite.

= 3.4.6 =
Zwei Fehler behoben.

= 3.4.5 =
Regulären Ausdruck zur Erkennung von manuellen Zählmarken in Beiträgen (deren Inhalt) verbessert.

= 3.4.4 =
Einen Fehler behoben.

= 3.4.3 =
Filter „Zuordnung“ in der Zählmarken-Übersicht hinzugefügt. Alle Operationen sind nun auf die ausgewählten Beitrags-Typen beschränkt. Plugin auf WordPress 4.1 getestet. Zwei Fehler behoben.

= 3.4.2 =
Verbesserung der Handhabung von nicht verfügbaren Beitrags-Typen. Einen Fehler behoben.

= 3.4.1 =
„Mitarbeiter“ können Zählmarken zuordnen. Einen Fehler behoben.

= 3.4.0 =
Zählmarken für Verlags-Konto importierbar. Zeichenanzahlen für ausgewählte Beiträge neuberechenbar. Berechnung Zeichenanzahl im visuellen Beitrags-Editor verbessert Drei Fehler behoben.

= 3.3.0 =
Shortcodes können bei Zeichenanzahl-Berechnung ausgewertet werden. Maximale Ausführungszeit für Operationen änderbar. Workaround für Zeichenanzahl-Berechnung in der Beitrags-Bearbeitung.

= 3.2.0 =
Benutzeroberfläche verbessert. Leistungsverbesserung für Berechnung der Zeichenanzahlen aller Beiträge. Einen Fehler behoben.

= 3.1.1 =
Fehler bezüglich leerer Meta-Name-Option aus Plugin-Version < 3.0.0 behoben. Import aus alter Plugin-Version wieder möglich.

= 3.1.0 =
Leistungsverbesserung (insbesondere geringere Arbeitsspeichernutzung) der Funktionen im Bereich „Operationen“.

= 3.0.1 =
Fehler Behoben, der Import aus anderen Plugins verhinderte (closures unterstützen keinen Zugriff auf private members in PHP 5.3).

= 3.0.0 =
PLUGIN VOLLSTÄNDIG NEU ENTWICKELT! Nach der Aktualisierung werden Warnungen erscheinen, was normal ist. Diese bitte einfach abarbeiten. Weitere Informationen: https://wordpress.org/plugins/wp-vgwort/faq/
