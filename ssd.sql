-- phpMyAdmin SQL Dump
-- version 4.2.7.1
-- http://www.phpmyadmin.net
--
-- Machine: 127.0.0.1
-- Gegenereerd op: 08 jan 2015 om 13:14
-- Serverversie: 5.6.20
-- PHP-versie: 5.5.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `ssd`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bedrijf`
--

CREATE TABLE IF NOT EXISTS `bedrijf` (
`BedrijfID` int(11) NOT NULL,
  `BedrijfNaam` varchar(255) NOT NULL,
  `BedrijfAdres` varchar(255) NOT NULL,
  `BedrijfPostcode` varchar(6) NOT NULL,
  `BedrijfPlaats` varchar(255) NOT NULL,
  `BedrijfTelefoon` varchar(10) NOT NULL,
  `BedrijfEmail` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `faq`
--

CREATE TABLE IF NOT EXISTS `faq` (
`FAQID` int(11) NOT NULL,
  `FAQTitel` varchar(255) NOT NULL,
  `FAQOmschrijving` text NOT NULL,
  `FAQOplossing` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `incident`
--

CREATE TABLE IF NOT EXISTS `incident` (
`IncidentID` int(11) NOT NULL,
  `IncidentTitel` varchar(255) NOT NULL,
  `IncidentType` enum('Vraag','Verzoek','Incident','Functioneel Probleem','Technisch Probleem') NOT NULL,
  `IncidentKanaal` enum('Telefoon','Email','Ticket') NOT NULL,
  `IncidentLijn` tinyint(4) NOT NULL DEFAULT '1',
  `IncidentPrioriteit` enum('Laag','Gemiddeld','Hoog') NOT NULL,
  `IncidentMedewerker` int(11) DEFAULT NULL,
  `IncidentLaatstBekeken` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `increactie`
--

CREATE TABLE IF NOT EXISTS `increactie` (
`IncReactieID` int(11) NOT NULL,
  `IncUser` int(11) NOT NULL,
  `IncReactie` text NOT NULL,
  `IncReactieDatum` datetime NOT NULL,
  `IncStatus` enum('Open','In behandeling','Afgehandeld') NOT NULL,
  `IncID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `product`
--

CREATE TABLE IF NOT EXISTS `product` (
`ProductID` int(11) NOT NULL,
  `Product` enum('FinSoft','Helpdesk') NOT NULL,
  `ProductAanschaf` date NOT NULL,
  `ProductLicentieTot` date NOT NULL,
  `ProductKlantID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`UserID` int(11) NOT NULL,
  `UserInlog` varchar(255) NOT NULL,
  `UserWw` varchar(255) NOT NULL,
  `UserNaam` varchar(255) NOT NULL,
  `UserBedrijf` int(11) NOT NULL,
  `UserFunctie` varchar(255) NOT NULL,
  `UserTelefoon` varchar(10) DEFAULT NULL,
  `UserEmail` varchar(255) NOT NULL,
  `UserFoto` varchar(255) DEFAULT NULL,
  `UserAfdeling` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `bedrijf`
--
ALTER TABLE `bedrijf`
 ADD PRIMARY KEY (`BedrijfID`);

--
-- Indexen voor tabel `faq`
--
ALTER TABLE `faq`
 ADD PRIMARY KEY (`FAQID`);

--
-- Indexen voor tabel `incident`
--
ALTER TABLE `incident`
 ADD PRIMARY KEY (`IncidentID`), ADD KEY `IncidentMedewerker` (`IncidentMedewerker`);

--
-- Indexen voor tabel `increactie`
--
ALTER TABLE `increactie`
 ADD PRIMARY KEY (`IncReactieID`), ADD KEY `IncUser` (`IncUser`), ADD KEY `IncID` (`IncID`);

--
-- Indexen voor tabel `product`
--
ALTER TABLE `product`
 ADD PRIMARY KEY (`ProductID`), ADD KEY `ProductKlantID` (`ProductKlantID`);

--
-- Indexen voor tabel `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`UserID`), ADD KEY `UserBedrijf` (`UserBedrijf`), ADD UNIQUE (`UserInlog`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `bedrijf`
--
ALTER TABLE `bedrijf`
MODIFY `BedrijfID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `faq`
--
ALTER TABLE `faq`
MODIFY `FAQID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `incident`
--
ALTER TABLE `incident`
MODIFY `IncidentID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `increactie`
--
ALTER TABLE `increactie`
MODIFY `IncReactieID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `product`
--
ALTER TABLE `product`
MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `user`
--
ALTER TABLE `user`
MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `incident`
--
ALTER TABLE `incident`
ADD CONSTRAINT `incident_ibfk_1` FOREIGN KEY (`IncidentMedewerker`) REFERENCES `user` (`UserID`) ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `increactie`
--
ALTER TABLE `increactie`
ADD CONSTRAINT `increactie_ibfk_1` FOREIGN KEY (`IncUser`) REFERENCES `user` (`UserID`) ON UPDATE CASCADE,
ADD CONSTRAINT `increactie_ibfk_2` FOREIGN KEY (`IncID`) REFERENCES `incident` (`IncidentID`) ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `product`
--
ALTER TABLE `product`
ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`ProductKlantID`) REFERENCES `bedrijf` (`BedrijfID`) ON UPDATE CASCADE;

--
-- Beperkingen voor tabel `user`
--
ALTER TABLE `user`
ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`UserBedrijf`) REFERENCES `bedrijf` (`BedrijfID`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
