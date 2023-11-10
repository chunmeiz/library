-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2023 at 07:25 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookID` int(11) NOT NULL,
  `Title` varchar(30) DEFAULT NULL,
  `Author` varchar(30) DEFAULT NULL,
  `Publisher` varchar(30) DEFAULT NULL,
  `Language` enum('English','French','German','Mandarin','Japanese','Russian','Other') DEFAULT 'English',
  `Category` enum('Fiction','Nonfiction','Reference') DEFAULT 'Fiction',
  `PhotoName` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`BookID`, `Title`, `Author`, `Publisher`, `Language`, `Category`, `PhotoName`) VALUES
(1, 'Great expectations', 'charles dickens', 'macmillan collectors library', 'English', 'Fiction', 'book_1.png'),
(2, 'An inconvenient truth', 'al gore', 'penguin books', 'English', 'Nonfiction', 'book_2.png'),
(3, 'Oxford dictionary', 'Oxford press', 'Oxford press', 'English', 'Reference', 'book_3.png'),
(4, 'Anna karenina', 'leo tolstoy', 'star publishing', 'Russian', 'Fiction', 'book_4.png'),
(5, 'The tale of genji', 'murasaki shikibu', 'kinokuniya', 'Japanese', 'Fiction', 'book_5.png');

-- --------------------------------------------------------

--
-- Table structure for table `bookstatus`
--

CREATE TABLE `bookstatus` (
  `ID` int(11) NOT NULL,
  `BookID` int(11) NOT NULL,
  `MemberID` int(11) DEFAULT NULL,
  `Status` enum('Available','Onloan','Deleted') DEFAULT 'Available',
  `AppliedDate` datetime DEFAULT current_timestamp(),
  `ReturnDate` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookstatus`
--

INSERT INTO `bookstatus` (`ID`, `BookID`, `MemberID`, `Status`, `AppliedDate`, `ReturnDate`) VALUES
(1, 1, 15, 'Onloan', '2023-10-27 00:00:00', '2023-09-11 12:03:42'),
(2, 2, NULL, 'Available', '0000-00-00 00:00:00', '2023-09-11 12:03:42'),
(3, 3, 1, 'Onloan', '2023-09-11 00:00:00', '2023-09-11 12:03:42'),
(4, 4, 1, 'Onloan', '2023-09-11 00:00:00', '2023-09-11 12:03:42'),
(5, 5, 2, 'Onloan', '2023-08-28 00:00:00', '2023-09-11 12:03:42');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `MemberID` int(11) NOT NULL,
  `MemberType` enum('Member','Admin') DEFAULT 'Member',
  `FirstName` varchar(20) DEFAULT NULL,
  `LastName` varchar(20) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `PasswordHash` char(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`MemberID`, `MemberType`, `FirstName`, `LastName`, `Email`, `PasswordHash`) VALUES
(1, 'Admin', 'chunmei', 'Zhu', 'chunmei@gelos.com', '34819d7beeabb9260a5c854bc85b3e44'),
(2, 'Member', 'Mike', 'Kay', 'mike@gmail.com', '9a22c724ee5d62e35c08e2158a3b28d4'),
(15, 'Member', 'new', 'v', 'newtest@mail.com', 'f53aa1603fe96fce2288b0e10b97cd62');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`BookID`);

--
-- Indexes for table `bookstatus`
--
ALTER TABLE `bookstatus`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `MemberID` (`MemberID`),
  ADD KEY `BookID` (`BookID`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`MemberID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `BookID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bookstatus`
--
ALTER TABLE `bookstatus`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `MemberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookstatus`
--
ALTER TABLE `bookstatus`
  ADD CONSTRAINT `bookstatus_ibfk_2` FOREIGN KEY (`MemberID`) REFERENCES `members` (`MemberID`),
  ADD CONSTRAINT `bookstatus_ibfk_3` FOREIGN KEY (`BookID`) REFERENCES `books` (`BookID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
