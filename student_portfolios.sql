-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 23, 2014 at 04:39 PM
-- Server version: 5.1.73
-- PHP Version: 5.3.3-7+squeeze19

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `elearn`
--

-- --------------------------------------------------------

--
-- Table structure for table `student_portfolios`
--

CREATE TABLE IF NOT EXISTS `student_portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `portfolio_course_id` int(11) NOT NULL,
  `student_canvas_id` int(11) NOT NULL,
  `student_name` varchar(200) NOT NULL,
  `parent_course_id` int(11) NOT NULL,
  `parent_course_name` varchar(200) NOT NULL,
  `term` varchar(50) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=285 ;
