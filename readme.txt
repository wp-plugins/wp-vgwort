=== Prosodia VGW OS für Zählmarken (VG WORT) ===
Contributors: raubvogel, smoo1337
Donate link: http://prosodia.de/
Tags: VG WORT, Zählmarke, T.O.M., Zählpixel, Geld, VGW, Verwertungsgesellschaft WORT, Prosodia, Verlag
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Verdienen Sie mit Ihren Beiträgen/Texten Geld durch die Integration von Zählmarken der VG WORT.

== Description ==

= Beschreibung und Funktionen =

Das Plugin *Prosodia VGW OS* (open source) vom Literaturverlag [Prosodia](http://prosodia.de/) und die [Verwertungsgesellschaft WORT (VG WORT)](http://www.vgwort.de/die-vg-wort.html) ermöglichen es Ihnen, *jährlich Geld* mit Ihren in WordPress geschriebenen Beiträgen zu verdienen. Die VG WORT ist ein deutscher, rechtsfähiger Verein, in dem sich Autoren zur gemeinsamen Verwertung von Urheberrechten zusammengeschlossen haben. Derart können Sie mit Ihren [Texten im Internet (Beiträge)](http://www.vgwort.de/verguetungen/auszahlungen/texte-im-internet.html), die aus mindestens *1.800 Zeichen* bestehen, von der Verwertung (Geld, Tantiemen) Gebrauch machen. Dazu ist eine kostenlose Mitgliedschaft bei der VG WORT erforderlich: [kostenlose Anmeldung](https://tom.vgwort.de/portal/login). Jeder Beitrag, für den Sie jährlich Geld bekommen wollen, muss eine gewissen Zahl an Lesern/Zugriffen aufweisen. Die Anzahl der Zugriffe für einen Beitrag wird von der VG WORT über *Zählmarken* ermittelt. Dabei wird jedem Beitrag ein eindeutiger Code – eine Zählmarke – zugeordnet. Besucht ein Leser einen Beitrag, wird die dem Beitrag zugeordnete Zählmarke an die VG WORT übermittelt und somit die Gesamtzahl der Leser für diesen Beitrag erhöht. Einmal im Jahr wird dann *pro* Beitrag Geld an Sie ausgeschüttet, abhängig davon wie hoch die Leserzahl eines Beitrags im vorherigen Jahr gewesen ist – die notwendige Mindestzahl wird von der VG WORT festgelegt (siehe auch [Wikipedia](http://de.wikipedia.org/wiki/Meldesystem_f%C3%BCr_Texte_auf_Internetseiten)). Je nach dem wie viele Beiträge Sie verfasst haben und wie häufig diese gelesen wurden, ist ein Verdienst von jährlich mehreren hundert bis tausend Euro durchaus nicht utopisch.

Die Hauptaufgabe von Prosodia VGW OS ist, Zählmarken und deren Zuordnungen zu Beiträgen bequem für Sie im WordPress-Administrationsbereich zu verwalten und sicherzustellen, dass keine Zählmarken verloren gehen oder mehrfach vergeben werden. Besonders dann, wenn Sie im Laufe der Zeit viele Texte mit Zählmarken versehen, werden Sie feststellen, dass eine manuelle Zuordnung durch direktes Einfügen der Zählmarken in Beiträgen in hinreichend großer Unüberschaubarkeit endet – dem wirkt Prosodia VGW OS durch Formalisierung und ausgeklügelter Technik entgegen. Im Folgenden werden die Funktionen und Fähigkeiten von Prosodia VGW OS kurz aufgelistet:

* Übersicht aller Zählmarken, Beitrags-Zuordnungen und weiterer Daten mit vielen Filter- und Sortierfunktionen
* Massenbearbeitung von Zählmarken und Beitrags-Zuordnungen
* Integration in die Beitrags-Übersicht („Alle Beiträge“) von WordPress
* automatisches Zuordnen von Zählmarken zu Beiträgen – auch massenhaft
* Anzeige der Zeichenanzahl eines Beitrags sowie der Anzahl fehlender Zeichen beim Schreiben
* Zeichenanzahl wird nach VG-WORT-Vorgabe berechnet – keine Bilder und Beschriftungen, Shortcodes, HTML-Tags usw.
* es können Beitrags-Typen (Beiträge, Seiten usw.) für die Zählmarken-Funktion ausgewählt werden
* importieren von Zählmarken aus CSV-Dateien, die von der VG WORT bereitgestellt werden
* importieren von Zählmarken aus CSV-Text oder manueller Eingabe
* importieren von Zählmarken und Beitrags-Zuordnungen aus dem Plugin „VG Wort“ von Torben Leuschner
* importieren von manuell zugeordneten Zählmarken aus Beiträgen – „&lt;img&gt;“-Tag wird erkannt und optional gelöscht
* nachträglicher Import von fehlenden privaten Zählmarken, falls die entsprechend öffentlichen bereits vorhanden sind
* exportieren von Zählmarken, Beitrags-Zuordnungen und weiterer Daten als CSV-Datei mit Filter- und Sortierfunktionen
* Zählmarken („&lt;img&gt;“-Tags) werden in den Beiträgen auf Ihrer Website ausgegeben
* Format der Zählmarkenausgabe kann frei angegeben werden (Platzhalter für Server und öffentliche Zählmarke)
* Datenintegrität der Zählmarken und Beitrags-Zuordnungen wird stets gewährleistet
* Zählmarken und Beitrags-Zuordnungen werden in einer eigenen Datenbanktabelle gespeichert – hohe Leistung
* inaktiv setzen von Zählmarken – Beitrags-Zuordnung wird nicht aufgehoben, Zählmarke wird nicht ausgegeben
* Warnung wird ausgegeben, falls andere VG-WORT-Plugins aktiviert sind
* Datenschutz-Vorlage für die Datenschutz-Erklärung Ihrer Website, wenn Sie Zählmarken der VG WORT verwenden
* Möglichkeit der vollständigen Deinstallation und Löschung der Datenbanktabellen und Einstellungen
* läuft auf Multisite-Installationen

Schauen Sie sich bitte auch die [Bildschirm-Fotos (Screenshots)](/plugins/wp-vgwort/screenshots/) von Prosodia VGW OS an.

= Eignung =

Was Prosodia VGW OS nicht leistet:

* Differenzierung der Zählmarken zwischen den Autoren eines Blogs
* direktes kopieren von Daten für die Abschluss-Meldung von Beiträgen bei der VG WORT (aber über CSV-Export möglich)
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

Prosodia VGW OS wird von der [Max Heckel, Ronny Harbich, Rebekka Hempel, Torsten Klein – Prosodia GbR](http://prosodia.de/kontakt/) unter der GPLv2-Lizenz vertrieben, die unter [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html) nachzulesen ist. Sie vertreibt diese Software vollständig kostenlos und übernimmt für diese daher keine Haftung außer die vom Bürgerlichen Gesetzbuch (BGB) zwingend erforderliche. Der Haftungsausschluss soll – soweit wie mit dem BGB vereinbar – der GPLv2-Lizenz entsprechen.

Prosodia VGW OS wird von der VG WORT weder unterstützt noch von ihr vertrieben.

= Hilfe und Support =

Wenn Sie einen Wunsch für eine neue Funktion haben oder einfach nur Hilfe benötigen, treten Sie bitte mit uns in Kontakt:

* [FAQ-Seite](/plugins/wp-vgwort/faq/)
* [Support-Seite](/support/plugin/wp-vgwort)
* [Plugin-Seite auf my Webcheck](http://www.mywebcheck.de/vg-wort-plugin-wordpress/)
* E-Mail-Adressen gibt es im Plugin unter „Prosodia VGW OS“ → „Hilfe“

== Installation ==

= Installation =

Automatische Installation über den Plugin-Bereich im Administrationsbereich von WordPress oder manuell wie folgt:

1. Laden Sie die Plugin-Zip-Datei herunter.
1. Entpacken Sie die Zip-Datei ins WordPress-Plugin-Verzeichnis (wp-content/plugins/).
1. Aktivieren Sie das Plugin im Plugin-Bereich im Administrationsbereich.

== Frequently Asked Questions ==

= Häufig gestellt Fragen =

= Aktualisierung auf Version 3.0.0 oder höher =

Da das Plugin ab Version 3.0.0 vollständig neu entwickelt wurde, ist es nicht mehr direkt mit den vorherigen Versionen kompatibel. Allerdings werden sämtliche Daten und Einstellungen aus den Vorgängerversionen nach der Aktualisierung übernommen – insbesondere die Zählmarken und deren Zuordnungen zu Beiträgen. Dies funktioniert nicht vollautomatisch, sondern erst nach einigen, wenigen Mausklicks. So werden nach der Aktualisierung im Administrationsbereich einige Warnungen angezeigt. Diese Warnungen enthalten Anweisungen und Links, wie die dargelegten Sachverhalte zu lösen sind – keine Sorge, dies ist mit wenigen Mausklicks erledigt. Und selbst wenn dabei etwas schief gehen sollte – wovon wir nicht ausgehen –, werden die alten Daten und Einstellungen nicht gelöscht. Das Einsetzen einer Version vor 3.0.0 ist stets möglich: [alte Versionen](/plugins/wp-vgwort/developers/).

Sollte das Plugin die ältere Version nicht erkennen, so führen Sie bitte manuell „Prosodia VGW OS“ → „Operationen“ → „Zählmarken aus altem VG-WORT-Plugin vor Version 3.0.0 importieren“ (Haken setzen) → „Alte Zählmarken und Beitrags-Zuordnungen importieren“ (Schaltfläche) aus.

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

= 3.0.0 =
PLUGIN VOLLSTÄNDIG NEU ENTWICKELT! Nach der Aktualisierung werden Warnungen erscheinen, was normal ist. Diese bitte einfach abarbeiten. Weitere Informationen: https://wordpress.org/plugins/wp-vgwort/faq/
