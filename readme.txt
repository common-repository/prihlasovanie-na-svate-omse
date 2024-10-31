=== Prihlasovanie na sväté omše===
Contributors: matejpodstrelenec, europegraphics 
Donate link: 
Tags: sväté omše, prihlasovanie, kostol
Requires at least: 3.5
Tested up to: 5.8.1
Requires PHP: 5.2.4
Stable tag: 1.9.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Prihlasovanie na sväté omše

== SK ==
Prihlasovanie na sväté omše
Tento plugin umožňuje prihlasovanie na sväté omše v rámci farnosti. Mená usporadúva do vytlačiteľnej tabuľky a zabezpečuje aby sa neprekročila kapacita.
Možné zobrazenie výberu omší cez select box alebo cez tlačidlá. 
Od verzie 1.3 sú dáta prihlásených zálohované 2 týždňe (pre dohľadanie Covid-19 kontaktov) a následne sú odstránené z databázy.
Vyvinulo pre vás TheSpirit.studio.
Vaše postrehy a nápady na vylepšenie nám prosím hláste.

== EN ==
This plugin serves as a simple registration form to manage number of participants attending sunday services. 
Due to coronavirus, capacity of each church service is limited. Parishioner can easily register and you as a parish administrator know who is registered -> who to let in.
As of now, this plugin is only available in Slovak language, but can be easily translated. 
Please let us know and we will gladly do that.
Team TheSpirit.studio

== Installation ==

== SK ==
1. Plugin nainštalujete priamo cez Wordpress -> Administrácia -> Pluginy -> Pridať nový -> Hľadajte Prihlasovanie na sväté omše
2. Prípadne stiahnite si .zip súbor a nahrajte ho cez možnosť "Nahrať plugin" v administrácii. 

== EN ==
1. Upload the full prihlasovanie-na-svate-omse directory into your wp-content/plugins directory.
2. In WordPress select Plugins from your sidebar menu and activate the Prihlasovanie na sväté omše plugin.

== Youtube tutoriál ==
https://youtu.be/98LkR2WDQ4U

== Screenshots ==

1. Frontend - Buttons (Tlačidlá)
2. Frontend - Prihlásenie sa
3. Frontend - Select box
4. Admin - Services table (Tabuľka sv. omší) 
5. Admin - Registration table (Registračná tabuľka)
 


== Changelog ==

= 1.9.1 =
* show_date - Nový parameter shortcode [tsspsv_service] - na zobrazenie dátumu pri tlačítkovom prežime - Za nápad patrí vďaka @stieranka
* Bug fix v shortcode pre zobrazovanie dátumu [tsspsv_day_of_service]
* Pridanie dátumu do potvrdzovacieho emailu
* Za testovanie a nové nápady vďaka Jezuiti Trnava

= 1.9 =
* Automatické pridanie dátumu pre prihlasovanie cez select box [tsspsv_form]
* Úprava defaultného času zálohy a vyprázdnenia formulárov na 21:00
* Možnosť povoliť prácu s pluginom aj role Editor
* Za testovanie a nové nápady vďaka Farnosť Trnavá Hora

= 1.8 =
* Možnosť pridať nové checkbox potvrdenie pre prihlásenie (napr. Potvrdzujem, že som očkovaný)

= 1.7 =
* Pridanie podpory pre jQuery
* Pridanie tel. čísla do CSV exportu
* Za testovanie a nové nápady vďaka Farnosť Trnavá Hora

= 1.6 =
* Fix pre bug "Uzatvorenie zapisovania - zapisovanie možné na druhý deň"
* Fix pre bug "Nová omša vždy uložená ako omša s neobmedzenou kapacitou"
* Fix pre bug "História sv. omší - duplicitné záznamy"
* Za testovanie a nové nápady opäť vďaka Farnosť Malacky

= 1.5 =
* Možnosť nastaviť neobmedzenú kapacitu (sv. omše pre zaočkovaných)
* Fix validácie emailu

= 1.4 =
* Zoznam registrovaných je zoradený podľa priezviska
* CSV export 

= 1.3 =
* V administrácii viete pridať do prihlasovacieho formuláru telefónne číslo
* Údaje o prihlasených sú po konaní sv. omše presunuté do historickej tabuľky, kde budú dostupné ešte 14 dní (Covid-19 vystopovanie)

= 1.2 =
* Pridanie checkboxu "Aktívna" (A.)

= 1.0.2 =
* Oprava mínusového počtu po zmene kapacity 

= 1.0.1 =
* Oprava chyby s uzatváraním zapisovania

= 1.0.0 =
* Initial release