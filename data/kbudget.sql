-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 24 Maj 2013, 11:12
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Category identifier',
  `pcid` int(10) unsigned DEFAULT NULL COMMENT 'Parent category identifier',
  `uid` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `c_type` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Category type (0 - profit, 1 - expense, 2 - transfer)',
  `c_name` varchar(100) NOT NULL COMMENT 'Category name',
  PRIMARY KEY (`cid`),
  KEY `pcid` (`pcid`),
  KEY `uid` (`uid`),
  KEY `uid_2` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=137 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `imports`
--

CREATE TABLE IF NOT EXISTS `imports` (
  `uid` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `fname` varchar(50) NOT NULL COMMENT 'Uploaded file name',
  `bank` varchar(50) NOT NULL COMMENT 'Bank name',
  `fpos` int(10) unsigned NOT NULL COMMENT 'Actual position in file',
  `nfpos` int(10) unsigned NOT NULL COMMENT 'New position in the file after saving the transaction',
  `count` int(10) unsigned NOT NULL COMMENT 'Number of all transactions in file',
  `counted` int(10) unsigned NOT NULL COMMENT 'Number of imported transactions',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transactions`
--

CREATE TABLE IF NOT EXISTS `transactions` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Transaction identifier',
  `aid` int(10) unsigned NOT NULL COMMENT 'Account identifier',
  `taid` int(10) unsigned DEFAULT NULL COMMENT 'Account which transfers money',
  `uid` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `cid` int(10) unsigned NOT NULL COMMENT 'Category identifier',
  `t_type` tinyint(3) unsigned NOT NULL COMMENT 'Transaction type (0 - profit, 1 - expense, 2 - outgoing transfer, 3 incoming transfer)',
  `t_date` date NOT NULL COMMENT 'Transaction date',
  `t_content` varchar(200) NOT NULL COMMENT 'Transaction description',
  `t_value` decimal(10,2) NOT NULL COMMENT 'Transaction value',
  PRIMARY KEY (`tid`),
  KEY `aid` (`aid`),
  KEY `uid` (`uid`),
  KEY `cid` (`cid`),
  KEY `taid` (`taid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=148 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transfers`
--

CREATE TABLE IF NOT EXISTS `transfers` (
  `trid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Transfer identifier',
  `uid` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `tid_out` int(10) unsigned NOT NULL COMMENT 'Outgoing transaction id',
  `tid_in` int(10) unsigned NOT NULL COMMENT 'Incoming transaction id',
  PRIMARY KEY (`trid`),
  KEY `uid` (`uid`),
  KEY `tid_out` (`tid_out`),
  KEY `tid_in` (`tid_in`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User identifier',
  `email` varchar(50) NOT NULL COMMENT 'User e-mail',
  `login` varchar(30) NOT NULL COMMENT 'User login',
  `pass` varchar(60) NOT NULL COMMENT 'User password',
  `u_type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User type (0 - user, 1 - admin)',
  `active` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Activation flag (0 - inactive, 1 - active)',
  `register_date` datetime NOT NULL DEFAULT '2013-01-01 00:00:00' COMMENT 'User register date',
  `last_login_date` datetime DEFAULT NULL COMMENT 'Last login date',
  `default_aid` int(10) unsigned NOT NULL COMMENT 'Default bank account',
  PRIMARY KEY (`uid`),
  KEY `default_aid` (`default_aid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2 AUTO_INCREMENT=6 ;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Ograniczenia dla tabeli `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_5` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `categories_ibfk_4` FOREIGN KEY (`pcid`) REFERENCES `categories` (`cid`);

--
-- Ograniczenia dla tabeli `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_ibfk_3` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

--
-- Ograniczenia dla tabeli `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`cid`) REFERENCES `categories` (`cid`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`aid`) REFERENCES `accounts` (`aid`),
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`taid`) REFERENCES `accounts` (`aid`);

--
-- Ograniczenia dla tabeli `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`uid`) REFERENCES `users` (`uid`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
