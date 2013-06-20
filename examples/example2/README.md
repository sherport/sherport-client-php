Sherport example 2
==================

Dieses Beispiel zeigt, wie man eine Login-Box gestalten kann wo man den normalen-Login per Tastatur und per Sherport QR-Code alternativ darstellen kann. Diese Methode wird in dem Typo3-Plugin benutzt, was als Beispiel unter http://typo.sherport.com/sherport/demo-sherport-plugin-typo3.html zu sehen ist.

Anwendung
=========

Der HTML-Code für die anzeige eines alternativen Logins basiert auf folgendem Grundgerüst.

<div id="sherport-frame">
    <div id="login-traditional">
        <div id="sherport-switcher"></div>
        ### Hier der Code für den normalen Login mit form input ###
    </div>
    <div id="login-sherport">
        ### Hier der html-code für den Sherport code, per loginGetSnippet generiert ###
    </div>
</div>

Das div "sherport-frame" ist dabei der Container für die beiden Varianten, "login-traditional" ist der Container für Ihren bisherigen Login  (form input) und "login-sherport" ist der Container für den Login per Sherport. Das Sherport-Javascript ermöglicht über einen Button das Umschalten zwischen beiden Varianten.
