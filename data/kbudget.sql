-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 22 Sty 2013, 12:07
-- Wersja serwera: 5.5.25a
-- Wersja PHP: 5.4.4

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
-- Struktura tabeli dla tabeli `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identyfikator kategorii',
  `uid` int(10) unsigned NOT NULL COMMENT 'identyfikator usera',
  `c_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '0 - przychód, 1 - wydatek',
  `c_name` varchar(100) NOT NULL COMMENT 'nazwa',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=31 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transaction`
--

CREATE TABLE IF NOT EXISTS `transaction` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identyfikator transakcji',
  `uid` int(10) unsigned NOT NULL COMMENT 'identyfikator usera',
  `cid` int(10) unsigned NOT NULL COMMENT 'identyfikator kategorii',
  `t_type` tinyint(3) unsigned NOT NULL COMMENT 'typ transakcji (0 - przychód, 1 - wydatek)',
  `t_date` date NOT NULL COMMENT 'data transakcji',
  `t_content` varchar(200) NOT NULL COMMENT 'opis transakcji',
  `t_value` decimal(10,2) NOT NULL COMMENT 'kwota transakcji',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=70 ;

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
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=4 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
