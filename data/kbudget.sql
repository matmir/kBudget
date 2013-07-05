-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 05 Lip 2013, 09:59
-- Wersja serwera: 5.5.27
-- Wersja PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `kBudget`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `accountId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Account identifier',
  `userId` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `accountName` varchar(30) NOT NULL COMMENT 'Account name',
  `balance` decimal(10,2) NOT NULL COMMENT 'Account balance',
  PRIMARY KEY (`accountId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `categoryId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Category identifier',
  `parentCategoryId` int(10) unsigned DEFAULT NULL COMMENT 'Parent category identifier',
  `userId` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `categoryType` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Category type (0 - profit, 1 - expense, 2 - transfer)',
  `categoryName` varchar(100) NOT NULL COMMENT 'Category name',
  PRIMARY KEY (`categoryId`),
  KEY `parentCategoryId` (`parentCategoryId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `imports`
--

DROP TABLE IF EXISTS `imports`;
CREATE TABLE IF NOT EXISTS `imports` (
  `userId` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `accountId` int(10) unsigned NOT NULL COMMENT 'Bank account identifier into which imports transactions',
  `fileName` varchar(50) NOT NULL COMMENT 'Uploaded file name',
  `bankName` varchar(50) NOT NULL COMMENT 'Bank name',
  `positionInFile` int(10) unsigned NOT NULL COMMENT 'Actual position in file',
  `newPositionInFile` int(10) unsigned NOT NULL COMMENT 'New position in the file after saving the transaction',
  `count` int(10) unsigned NOT NULL COMMENT 'Number of all transactions in file',
  `counted` int(10) unsigned NOT NULL COMMENT 'Number of imported transactions',
  PRIMARY KEY (`userId`),
  KEY `accountId` (`accountId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `transactionId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Transaction identifier',
  `accountId` int(10) unsigned NOT NULL COMMENT 'Account identifier',
  `transferAccountId` int(10) unsigned DEFAULT NULL COMMENT 'Account which transfers money',
  `userId` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `categoryId` int(10) unsigned NOT NULL COMMENT 'Category identifier',
  `transactionType` tinyint(3) unsigned NOT NULL COMMENT 'Transaction type (0 - profit, 1 - expense, 2 - outgoing transfer, 3 incoming transfer)',
  `date` date NOT NULL COMMENT 'Transaction date',
  `content` varchar(200) NOT NULL COMMENT 'Transaction description',
  `value` decimal(10,2) NOT NULL COMMENT 'Transaction value',
  PRIMARY KEY (`transactionId`),
  KEY `accountId` (`accountId`),
  KEY `userId` (`userId`),
  KEY `categoryId` (`categoryId`),
  KEY `transferAccountId` (`transferAccountId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2;

--
-- Wyzwalacze `transactions`
--
DROP TRIGGER IF EXISTS `update_balance_on_delete`;
DELIMITER //
CREATE TRIGGER `update_balance_on_delete` BEFORE DELETE ON `transactions`
 FOR EACH ROW BEGIN
	IF OLD.transactionType = 0 OR OLD.transactionType = 3 THEN -- income
 		UPDATE accounts SET balance = balance - OLD.value WHERE accountId=OLD.accountId;
	ELSEIF OLD.transactionType = 1 OR OLD.transactionType = 2 THEN -- expense
 		UPDATE accounts SET balance = balance + OLD.value WHERE accountId=OLD.accountId;
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_balance_on_insert`;
DELIMITER //
CREATE TRIGGER `update_balance_on_insert` BEFORE INSERT ON `transactions`
 FOR EACH ROW BEGIN
	IF NEW.transactionType = 0 OR NEW.transactionType = 3 THEN
 		UPDATE accounts SET balance = balance + NEW.value WHERE accountId=NEW.accountId;
	ELSEIF NEW.transactionType = 1 OR NEW.transactionType = 2 THEN
 		UPDATE accounts SET balance = balance - NEW.value WHERE accountId=NEW.accountId;
	END IF;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `update_balance_on_update`;
DELIMITER //
CREATE TRIGGER `update_balance_on_update` BEFORE UPDATE ON `transactions`
 FOR EACH ROW BEGIN
	DECLARE DIFF DECIMAL(10,2);
        SET DIFF = (NEW.value - OLD.value);
	IF DIFF != 0 THEN
        	IF NEW.transactionType=0 OR NEW.transactionType=3 THEN -- profit
 			UPDATE accounts SET balance = balance + DIFF WHERE accountId=NEW.accountId;
                ELSEIF NEW.transactionType=1 OR NEW.transactionType=2 THEN -- expense
                	UPDATE accounts SET balance = balance - DIFF WHERE accountId=NEW.accountId;
                END IF;
	END IF;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transfers`
--

DROP TABLE IF EXISTS `transfers`;
CREATE TABLE IF NOT EXISTS `transfers` (
  `transferId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Transfer identifier',
  `userId` int(10) unsigned NOT NULL COMMENT 'User identifier',
  `outTransactionId` int(10) unsigned NOT NULL COMMENT 'Outgoing transaction id',
  `inTransactionId` int(10) unsigned NOT NULL COMMENT 'Incoming transaction id',
  PRIMARY KEY (`transferId`),
  KEY `userId` (`userId`),
  KEY `outTransactionId` (`outTransactionId`),
  KEY `inTransactionId` (`inTransactionId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'User identifier',
  `email` varchar(50) NOT NULL COMMENT 'User e-mail',
  `login` varchar(30) NOT NULL COMMENT 'User login',
  `pass` varchar(60) NOT NULL COMMENT 'User password',
  `type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'User type (0 - user, 1 - admin, 2 - demo)',
  `active` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Activation flag (0 - inactive, 1 - active)',
  `registerDate` datetime NOT NULL DEFAULT '2013-01-01 00:00:00' COMMENT 'User register date',
  `lastLoginDate` datetime DEFAULT NULL COMMENT 'Last login date',
  `defaultAccountId` int(10) unsigned NOT NULL COMMENT 'Default bank account',
  PRIMARY KEY (`userId`),
  KEY `defaultAccountId` (`defaultAccountId`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin2;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Ograniczenia dla tabeli `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_4` FOREIGN KEY (`parentCategoryId`) REFERENCES `categories` (`categoryId`),
  ADD CONSTRAINT `categories_ibfk_5` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Ograniczenia dla tabeli `imports`
--
ALTER TABLE `imports`
  ADD CONSTRAINT `imports_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  ADD CONSTRAINT `imports_ibfk_4` FOREIGN KEY (`accountId`) REFERENCES `accounts` (`accountId`);

--
-- Ograniczenia dla tabeli `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`categoryId`),
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`accountId`) REFERENCES `accounts` (`accountId`),
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`transferAccountId`) REFERENCES `accounts` (`accountId`),
  ADD CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

--
-- Ograniczenia dla tabeli `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
