-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 15 Maj 2013, 20:56
-- Wersja serwera: 5.5.27
-- Wersja PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `kbudget`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Account identifier',
  `uid` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `a_name` varchar(30) NOT NULL COMMENT 'Account name',
  `balance` decimal(10,2) NOT NULL COMMENT 'Account balance',
  PRIMARY KEY (`aid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identyfikator kategorii',
  `pcid` int(10) unsigned DEFAULT NULL,
  `uid` int(10) unsigned NOT NULL COMMENT 'identyfikator usera',
  `c_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '0 - profit, 1 - expense, 2 - transfer',
  `c_name` varchar(100) NOT NULL COMMENT 'nazwa',
  PRIMARY KEY (`cid`),
  KEY `pcid` (`pcid`),
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=127 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `imports`
--

CREATE TABLE IF NOT EXISTS `imports` (
  `uid` int(10) unsigned NOT NULL COMMENT 'Identyfikator usera',
  `fname` varchar(50) NOT NULL COMMENT 'nazwa załadowanego pliku',
  `bank` varchar(50) NOT NULL COMMENT 'nazwa banku',
  `fpos` int(10) unsigned NOT NULL COMMENT 'pozycja w pliku',
  `nfpos` int(10) unsigned NOT NULL COMMENT 'Nowa pozycja w pliku po zapisie transakcji',
  `count` int(10) unsigned NOT NULL COMMENT 'Liczba wszystkich transakcji w pliku',
  `counted` int(10) unsigned NOT NULL COMMENT 'Liczba zaimportowanych transakcji',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transaction`
--

CREATE TABLE IF NOT EXISTS `transaction` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identyfikator transakcji',
  `aid` int(10) unsigned NOT NULL COMMENT 'Account identifier',
  `taid` int(10) unsigned DEFAULT NULL COMMENT 'Account which transfers money',
  `uid` int(10) unsigned NOT NULL COMMENT 'identyfikator usera',
  `cid` int(10) unsigned NOT NULL COMMENT 'identyfikator kategorii',
  `t_type` tinyint(3) unsigned NOT NULL COMMENT 'transaction type (0 - profit, 1 - expense, 2 - outgoing transfer, 3 incoming transfer)',
  `t_date` date NOT NULL COMMENT 'data transakcji',
  `t_content` varchar(200) NOT NULL COMMENT 'opis transakcji',
  `t_value` decimal(10,2) NOT NULL COMMENT 'kwota transakcji',
  PRIMARY KEY (`tid`),
  KEY `aid` (`aid`),
  KEY `uid` (`uid`),
  KEY `cid` (`cid`),
  KEY `taid` (`taid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=138 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transfers`
--

CREATE TABLE IF NOT EXISTS `transfers` (
  `trid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'transfer identifier',
  `uid` int(10) unsigned NOT NULL COMMENT 'user identifier',
  `tid_out` int(10) unsigned NOT NULL COMMENT 'outgoing transaction id',
  `tid_in` int(10) unsigned NOT NULL COMMENT 'incoming transaction id',
  PRIMARY KEY (`trid`),
  KEY `uid` (`uid`),
  KEY `tid_out` (`tid_out`),
  KEY `tid_in` (`tid_in`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identyfikator usera',
  `email` varchar(50) NOT NULL COMMENT 'e-mail usera',
  `login` varchar(30) NOT NULL COMMENT 'login usera',
  `pass` varchar(100) NOT NULL COMMENT 'hasło usera',
  `passs` varchar(50) NOT NULL,
  `u_type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - user, 1 - admin',
  `active` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - nieaktywny, 1 - aktywny',
  `register_date` datetime NOT NULL DEFAULT '2013-01-01 00:00:00' COMMENT 'Data rejestracji usera',
  `last_login_date` datetime DEFAULT NULL COMMENT 'Data ostatniego logowania usera',
  `default_aid` int(10) unsigned NOT NULL COMMENT 'Default bank account',
  PRIMARY KEY (`uid`),
  KEY `default_aid` (`default_aid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=5 ;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Ograniczenia dla tabeli `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`pcid`) REFERENCES `category` (`cid`),
  ADD CONSTRAINT `category_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Ograniczenia dla tabeli `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_ibfk_1` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
