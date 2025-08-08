-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2025 at 11:41 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prefinal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `archived_login_history`
--

CREATE TABLE `archived_login_history` (
  `login_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `login_timestamp` datetime DEFAULT NULL,
  `logout_timestamp` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `special_requests` text DEFAULT NULL,
  `number_of_travelers` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `booking_inquiries`
--

CREATE TABLE `booking_inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `package_id` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `number_of_travelers` int(11) NOT NULL,
  `budget_range` varchar(100) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `booking_inquiries`
--

INSERT INTO `booking_inquiries` (`id`, `name`, `email`, `phone`, `package_id`, `preferred_date`, `number_of_travelers`, `budget_range`, `special_requests`, `status`, `created_at`) VALUES
(1, 'marc jullan pague', 'marcjullanp@gmail.com', '09946274643', 32, '2025-04-26', 2, 'standard', 'dsadas', 'pending', '2025-04-09 05:25:08'),
(4, 'marc jullan pague', 'hansel@caroline.com', '09946274643', 32, '2025-05-03', 21, 'standard', 'sa', 'pending', '2025-04-09 05:37:21'),
(10, 'Marc Jullan Pague', 'mikeangelo_collamat@yahoo.com', '09672476990', 32, '2025-04-12', 2, 'economy', 'asd', 'pending', '2025-04-10 14:48:50'),
(11, 'marc', 'hansel@caroline.com', '09946274643', 34, '2025-04-19', 2, 'economy', 'dsa', 'pending', '2025-04-10 14:50:54'),
(19, 'marc', 'hansel@caroline.com', '09946274643', 33, '2025-04-19', 1, 'standard', 'dsa', 'pending', '2025-04-10 15:07:17'),
(21, 'Mr. P', 'mikaelagalzote16@gmail.com', '09672476990', 32, '2025-04-12', 22, 'economy', 'da', '', '2025-04-10 15:14:05'),
(23, 'marc jullan pague', 'marcjullanp@gmail.com', '099302322', 33, '2025-04-26', 1, 'luxury', 'ds', '', '2025-04-10 15:14:51'),
(26, 'Mr. P', 'mikaelagalzote16@gmail.com', '09672476990', 32, '2025-04-19', 4, 'economy', 'ds', '', '2025-04-10 15:26:54'),
(34, 'Mr. P', 'marcjullanpague@gmail.com', '09672476990', 33, '2025-04-19', 12, 'standard', 'ds', '', '2025-04-10 16:47:37'),
(36, 'Mrpbuddy', 'pepotalvarez@gmail.com', '09672476990', 32, '2025-04-26', 43, 'luxury', 'TAE', '', '2025-04-11 06:07:26'),
(38, 'Boss MJ', 'pepotalvarez@gmail.com', '09672476990', 33, '2025-04-19', 334, 'standard', 'ddad', '', '2025-04-11 06:56:22');

-- --------------------------------------------------------

--
-- Table structure for table `booking_inquiries_backup`
--

CREATE TABLE `booking_inquiries_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `number_of_travelers` int(11) NOT NULL,
  `preferred_date` date NOT NULL,
  `budget_range` varchar(50) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `inquiry_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`, `status`) VALUES
(1, 'marc', 'marcjullanp@gmail.com', '09946274643', 'Good Evening', 'Hello', '2025-03-28 04:39:54', 'unread'),
(2, 'Hansdsdsds', 'hanselcarolineprado@gmail.com', NULL, 'dsa', 'dsds', '2025-03-30 02:42:53', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `best_time_to_visit` text DEFAULT NULL,
  `tour_duration` varchar(100) DEFAULT NULL,
  `price_per_person` decimal(10,2) DEFAULT NULL,
  `price_per_group` decimal(10,2) DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `travel_requirements` text DEFAULT NULL,
  `transportation_details` text DEFAULT NULL,
  `activities_highlights` text DEFAULT NULL,
  `gallery_images` text DEFAULT NULL,
  `highlights` text DEFAULT NULL,
  `best_time` text DEFAULT NULL,
  `things_to_do` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`id`, `name`, `image`, `status`, `featured`, `description`, `latitude`, `longitude`, `country`, `category`, `best_time_to_visit`, `tour_duration`, `price_per_person`, `price_per_group`, `inclusions`, `exclusions`, `travel_requirements`, `transportation_details`, `activities_highlights`, `gallery_images`, `highlights`, `best_time`, `things_to_do`, `updated_at`, `created_at`) VALUES
(36, 'Boracay', '67ecb3b35d0d0.png', 1, 0, 'Boracay is a tropical paradise known for its powdery white sand beaches, crystal-clear waters, and vibrant nightlife. It is a top tourist destination offering a mix of relaxation and adventure. Visitors can enjoy island hopping, snorkeling, diving, and breathtaking sunsets.', NULL, NULL, 'Philippines', NULL, 'November to May (Dry Season)', NULL, NULL, NULL, NULL, NULL, 'Valid passport (at least 6 months before expiration)\r\n\r\nVisa-free for most countries (for stays up to 30 days)\r\n\r\nProof of onward travel\r\n\r\nCOVID-19 requirements may apply (check latest regulations)', 'Nearest Airport: Godofredo P. Ramos Airport (Caticlan) or Kalibo International Airport\r\n\r\nFrom Caticlan: Short boat ride to Boracay Island\r\n\r\nLocal transport: Tricycles, e-trikes, and boats for island hopping', NULL, '[\"67ecb3b35d17d.png\",\"67ecb3b35d184.png\",\"67ecb3b35d188.png\",\"67ecb3b35d18c.png\",\"67ecb3b35d190.jpg\"]', NULL, NULL, NULL, '2025-04-02 03:49:07', '2025-04-02 03:49:07'),
(37, 'Jeju Island', '67ecb5571fe39.png', 1, 0, 'Jeju Island, often called the \"Hawaii of Korea,\" is a volcanic island famous for its stunning landscapes, lava tubes, waterfalls, and unique cultural sites. It offers a perfect mix of nature, history, and adventure, with attractions like Hallasan Mountain, Seongsan Ilchulbong Peak, and beautiful beaches.', NULL, NULL, 'South Korea', NULL, 'March to May (Spring) & September to November (Autumn)', NULL, NULL, NULL, NULL, NULL, 'Valid passport (at least 6 months before expiration)\r\n\r\nVisa-free for most countries (up to 90 days for tourists)\r\n\r\nK-ETA (Korea Electronic Travel Authorization) for some nationalities\r\n\r\nProof of accommodation and return ticket', 'Nearest Airport: Jeju International Airport\r\n\r\nDirect domestic flights from Seoul, Busan, and other major cities\r\n\r\nPublic transport: Buses, taxis, and rental cars available for exploring the island', NULL, '[\"67ecb5571fef0.png\"]', NULL, NULL, NULL, '2025-04-02 03:56:07', '2025-04-02 03:56:07'),
(40, 'Mount Fuji', '67ecb69bb6da8.png', 1, 0, 'Mount Fuji, Japan’s highest and most iconic mountain, is a UNESCO World Heritage site known for its breathtaking views, scenic hiking trails, and cultural significance. It attracts nature lovers, adventure seekers, and photographers who come to witness its beauty, especially during cherry blossom and autumn seasons.', NULL, NULL, 'Japan', NULL, 'July to September (Climbing season)\r\n\r\nOctober to November (Autumn foliage)\r\n\r\nApril to May (Cherry blossom season)', NULL, NULL, NULL, NULL, NULL, 'Valid passport (at least 6 months before expiration)\r\n\r\nVisa-free for many countries (up to 90 days for tourists)\r\n\r\nJapan e-Visa or regular visa (for applicable nationalities)\r\n\r\nJapan Rail Pass (recommended for travelers using trains)', 'Nearest Airport: Tokyo Narita or Haneda Airport\r\n\r\nFrom Tokyo: Shinkansen to Mishima Station, then bus to Mount Fuji\r\n\r\nLocal transport: Buses, taxis, and rental cars available for exploring the Fuji Five Lakes area', NULL, '[\"67ecb69bb78bd.png\"]', NULL, NULL, NULL, '2025-04-02 04:01:31', '2025-04-02 04:01:31'),
(41, 'Marina Bay Sands', '67ece0bfc7d3e.jfif', 1, 0, 'Marina Bay Sands is an iconic integrated resort in Singapore, featuring a luxurious hotel, a rooftop infinity pool, a world-class casino, and the SkyPark Observation Deck with stunning panoramic views of the city. It is also home to The Shoppes, offering high-end shopping, fine dining, and entertainment options, including a museum and a theater.', NULL, NULL, 'Singapore', NULL, 'Evening for the best city skyline views; February to April for pleasant weather.', NULL, NULL, NULL, NULL, NULL, 'A valid passport with at least six months\' validity.\r\n\r\nVisa requirements depend on nationality.\r\n\r\nVisitors must be at least 21 years old to enter the casino.', 'MRT: Bayfront MRT Station (Downtown and Circle Line).\r\n\r\nBuses: Several public bus routes stop near Marina Bay Sands.\r\n\r\nTaxis & Ride-hailing: Grab, Gojek, and taxis are readily available.\r\n\r\nWalking: Connected via pedestrian bridges and walkways.', NULL, '[\"67ece04c68261.jfif\"]', NULL, NULL, NULL, '2025-04-02 07:01:55', '2025-04-02 06:59:05'),
(43, 'New York', '67ed0597cedee.png', 1, 0, 'New York City, often called \"The Big Apple,\" is one of the most vibrant and diverse cities in the world. It is home to iconic landmarks such as Times Square, the Statue of Liberty, Central Park, and the Empire State Building. Known for its culture, Broadway theaters, shopping, and world-class dining, NYC offers an unforgettable experience for every traveler.', NULL, NULL, 'United States', NULL, 'Spring (April–June): Mild weather, blooming flowers, and outdoor activities.\r\n\r\nFall (September–November): Pleasant temperatures, beautiful autumn foliage, and fewer tourists.\r\n\r\nWinter (December–February): Ideal for holiday lights, ice skating, and New Year\'s Eve in Times Square.', NULL, NULL, NULL, NULL, NULL, 'Visa: Travelers may need a U.S. visa or ESTA (Visa Waiver Program).\r\n\r\nPassport: Must be valid for the duration of stay.\r\n\r\nCOVID-19 & Health Requirements: Check for current guidelines.', 'Airports: JFK, LaGuardia (LGA), and Newark (EWR) serve international and domestic flights.\r\n\r\nSubway: The New York City Subway is the fastest and most affordable way to travel.\r\n\r\nBuses & Taxis: NYC has an extensive bus network and iconic yellow taxis.\r\n\r\nRide-hailing: Uber, Lyft, and Via are available throughout the city.\r\n\r\nWalking & Biking: Many tourist spots are walkable, with CitiBike rentals available.', NULL, '[]', NULL, NULL, NULL, '2025-04-02 09:38:31', '2025-04-02 09:38:31'),
(45, 'Christ the Redeemer', '67ed0639dc46a.png', 1, 0, 'One of the New Seven Wonders of the World, Christ the Redeemer is an iconic statue standing atop Mount Corcovado in Rio de Janeiro. The 98-foot-tall statue offers breathtaking panoramic views of the city, including Copacabana Beach, Sugarloaf Mountain, and the surrounding rainforest. It is a must-visit landmark and a symbol of Brazil’s culture and faith.', NULL, NULL, 'Brazil', NULL, 'Spring (September–November): Pleasant weather and fewer tourists.\r\n\r\nAutumn (March–May): Cooler temperatures and clear skies.', NULL, NULL, NULL, NULL, NULL, 'Visa: Visa requirements vary by nationality. Some travelers may need an e-Visa.\r\n\r\nPassport: Must be valid for at least six months.\r\n\r\nHealth Requirements: Some vaccinations may be required for entry.', 'By Train: The Corcovado Train takes visitors up the mountain to the statue.\r\n\r\nBy Van: Official tourist vans operate from several locations in Rio.\r\n\r\nBy Hiking: Adventurous travelers can take a scenic hike through Tijuca Forest.\r\n\r\nBy Taxi/Ride-hailing: Uber and taxis are available to the base of Corcovado.', NULL, '[]', NULL, NULL, NULL, '2025-04-02 09:41:13', '2025-04-02 09:41:13'),
(46, 'Taj Mahal', '67ed0691b2e79.png', 1, 0, 'The Taj Mahal is one of the most famous monuments in the world, known for its stunning white marble architecture and romantic history. Built by Mughal Emperor Shah Jahan in memory of his wife Mumtaz Mahal, this UNESCO World Heritage Site attracts millions of visitors every year. The intricate carvings, reflecting pools, and changing hues of the monument throughout the day make it a breathtaking sight.', NULL, NULL, 'India', NULL, 'Winter (October–March): Pleasant weather, ideal for sightseeing.\r\n\r\nEarly morning or sunset: Best lighting for photography and fewer crowds.', NULL, NULL, NULL, NULL, NULL, 'Visa: Most travelers need an Indian visa, available as an e-Visa for many countries.\r\n\r\nPassport: Must be valid for at least six months.\r\n\r\nHealth Requirements: Some vaccinations may be recommended.', 'By Air: Nearest airport is Agra Airport (limited flights). Major airport: Delhi (Indira Gandhi International Airport).\r\n\r\nBy Train: Agra Cantt Railway Station connects to major Indian cities.\r\n\r\nBy Road: Buses and taxis are available from Delhi, Jaipur, and other nearby cities.\r\n\r\nBy Tuk-tuk/Rickshaw: Local transport available for short distances.', NULL, '[]', NULL, NULL, NULL, '2025-04-02 09:42:41', '2025-04-02 09:42:41'),
(47, 'Keukenhof Gardens', '67ed06ea0d01b.png', 1, 0, 'Known as the \"Garden of Europe,\" Keukenhof is one of the world’s largest flower gardens, famous for its breathtaking tulip displays. Located in Lisse, it features over 7 million flowers, including tulips, daffodils, and hyacinths, arranged in stunning artistic patterns. The gardens provide a colorful, picturesque experience that attracts visitors from around the globe.', NULL, NULL, 'Netherlands', NULL, 'Spring (Mid-March to Mid-May): Peak tulip bloom season, offering the most vibrant views.\r\n\r\nApril: Best time for fully bloomed tulips.', NULL, NULL, NULL, NULL, NULL, 'Visa: Most travelers from outside the EU need a Schengen visa.\r\n\r\nPassport: Must be valid for at least three months beyond stay.\r\n\r\nHealth Requirements: No mandatory vaccinations, but travel insurance is recommended.', 'By Air: Nearest airport is Amsterdam Schiphol Airport (AMS).\r\n\r\nBy Train: Take a train to Leiden, then a bus to Keukenhof.\r\n\r\nBy Bus: Direct buses from Amsterdam, Schiphol, and other cities operate during the tulip season.\r\n\r\nBy Bicycle: Popular option for cycling enthusiasts, with scenic routes from nearby towns.', NULL, '[]', NULL, NULL, NULL, '2025-04-02 09:44:10', '2025-04-02 09:44:10'),
(48, 'Blue Lagoon', '67ed073f5c649.png', 1, 0, 'The Blue Lagoon is a world-famous geothermal spa known for its milky blue waters rich in minerals like silica and sulfur, which are believed to have healing properties. Surrounded by volcanic landscapes, this luxurious hot spring offers a unique and relaxing experience, with spa treatments, saunas, and in-water massages.', NULL, NULL, 'Iceland', NULL, 'Winter (November–March): Experience the warm waters under the Northern Lights.\r\n\r\nSummer (June–August): Enjoy long daylight hours and milder temperatures.', NULL, NULL, NULL, NULL, NULL, 'Visa: Most travelers from outside the EU/Schengen area require a Schengen visa.\r\n\r\nPassport: Must be valid for at least three months beyond stay.\r\n\r\nHealth Requirements: No mandatory vaccinations, but travel insurance is recommended.', 'By Air: Nearest airport is Keflavík International Airport (KEF), about 20 minutes away.\r\n\r\nBy Bus: Direct shuttle buses run from Reykjavík and the airport.\r\n\r\nBy Car: Rental cars available for a scenic drive from Reykjavík (about 50 minutes).', NULL, '[\"67f6a1bc9ec7d.jpg\",\"67f6a1bc9f277.jpg\"]', NULL, NULL, NULL, '2025-04-09 16:35:08', '2025-04-02 09:45:35'),
(49, 'Icehotel', '67ed078d78805.png', 1, 0, 'The Icehotel in Jukkasjärvi is one of the world\'s most unique hotels, built entirely from ice and snow. Every winter, artists from around the world design and sculpt breathtaking ice rooms, creating a magical frozen wonderland. Visitors can sleep in ice suites, enjoy ice-carved bars, and even witness the Northern Lights. In the summer, the Icehotel 365 offers an all-year ice experience.', NULL, NULL, 'Sweden', NULL, 'Winter (December–April): Experience the fully built Icehotel and winter activities like dog sledding and snowmobiling.\r\n\r\nAutumn (September–November): Best time for viewing the Northern Lights.', NULL, NULL, NULL, NULL, NULL, 'Visa: Most travelers from outside the EU/Schengen area require a Schengen visa.\r\n\r\nPassport: Must be valid for at least three months beyond stay.\r\n\r\nHealth Requirements: No mandatory vaccinations, but travel insurance is recommended.', 'By Air: Nearest airport is Kiruna Airport (KRN), about 15 minutes away.\r\n\r\nBy Train: Overnight train from Stockholm to Kiruna, then a short bus/taxi ride.\r\n\r\nBy Car: Rental cars available for a scenic drive from Kiruna.', NULL, '[]', NULL, NULL, NULL, '2025-04-02 09:46:53', '2025-04-02 09:46:53'),
(50, 'das', '67f8cd1c9e005.jpg', 1, 0, 'asd', NULL, NULL, 'asd', NULL, 'ada', NULL, NULL, NULL, NULL, NULL, 'da', 'da', NULL, '[\"67f8cd1c9e524.png\"]', NULL, NULL, NULL, '2025-04-11 08:04:44', '2025-04-11 08:04:44');

-- --------------------------------------------------------

--
-- Table structure for table `featured_destinations`
--

CREATE TABLE `featured_destinations` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `featured_order` int(11) NOT NULL DEFAULT 1,
  `highlight_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `tour_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'general',
  `status` enum('new','in_progress','resolved') DEFAULT 'new',
  `response` text DEFAULT NULL,
  `responded_by` varchar(255) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `tour_title` varchar(255) DEFAULT NULL,
  `metadata` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `browser_info` varchar(255) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'success',
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_history`
--

INSERT INTO `login_history` (`id`, `user_id`, `username`, `email`, `browser_info`, `login_time`, `logout_time`, `status`, `user_agent`) VALUES
(111, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:137.0) Gecko/20100101 Firefox/137.0', '2025-04-09 10:42:30', NULL, 'success', NULL),
(112, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-09 11:00:48', NULL, 'success', NULL),
(113, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:137.0) Gecko/20100101 Firefox/137.0', '2025-04-09 18:15:08', NULL, 'success', NULL),
(114, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-09 19:18:40', NULL, 'success', NULL),
(115, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-10 22:53:27', NULL, 'success', NULL),
(116, NULL, 'marc123', 'marcjullanp@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:26:28', NULL, 'success', NULL),
(117, NULL, 'marc123', 'marcjullanp@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:26:49', NULL, 'success', NULL),
(118, NULL, 'marc123', 'marcjullanp@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:27:09', NULL, 'success', NULL),
(119, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:27:24', NULL, 'success', NULL),
(120, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:40:16', NULL, 'success', NULL),
(121, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:43:20', NULL, 'failed', NULL),
(122, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 00:43:26', NULL, 'success', NULL),
(123, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 14:07:34', NULL, 'success', NULL),
(124, NULL, 'marc123', 'marcjullanp@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 14:16:10', NULL, 'success', NULL),
(125, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 14:55:34', NULL, 'success', NULL),
(126, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:56', NULL, 'failed', NULL),
(127, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:57', NULL, 'failed', NULL),
(128, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(129, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(130, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(131, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(132, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(133, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:58', NULL, 'failed', NULL),
(134, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:59', NULL, 'failed', NULL),
(135, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:59', NULL, 'failed', NULL),
(136, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:59', NULL, 'failed', NULL),
(137, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:35:59', NULL, 'failed', NULL),
(138, NULL, 'admin\\\\]', 'admin\\\\]', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:36:04', NULL, 'failed', NULL),
(139, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 15:36:09', NULL, 'success', NULL),
(140, NULL, 'admin', 'admin@gmail.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-04-11 17:01:35', NULL, 'success', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_history_archive`
--

CREATE TABLE `login_history_archive` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `logout_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'success',
  `user_agent` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `archived_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `login_history_archive`
--

INSERT INTO `login_history_archive` (`id`, `user_id`, `username`, `login_time`, `logout_time`, `status`, `user_agent`, `ip_address`, `archived_date`) VALUES
(1, NULL, 'mar', '2025-03-27 15:29:49', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(2, NULL, 'admin', '2025-03-27 23:41:19', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(3, NULL, 'admin@admin.com', '2025-03-27 23:41:32', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(4, NULL, 'admin@admin.com', '2025-03-27 23:41:48', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(5, NULL, 'admin@admin.com', '2025-03-27 23:41:58', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(6, NULL, 'admin', '2025-03-27 23:43:31', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(7, NULL, 'admin', '2025-03-28 10:24:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(8, NULL, 'admin', '2025-03-28 18:15:19', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(9, NULL, 'admin', '2025-03-28 22:24:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(10, NULL, 'admin', '2025-03-29 14:25:02', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(11, NULL, 'admin', '2025-03-29 14:25:12', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(12, NULL, 'admin@admin.com', '2025-03-29 14:25:39', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(13, NULL, 'admin', '2025-03-29 14:26:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(14, NULL, 'admin', '2025-03-29 15:12:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(15, NULL, 'admin', '2025-03-29 20:41:01', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(16, NULL, 'admin', '2025-03-29 21:08:50', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(17, NULL, 'admin', '2025-03-29 21:15:33', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(18, NULL, 'admin', '2025-03-29 22:47:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(19, NULL, 'admin', '2025-03-30 09:56:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(20, NULL, 'admin', '2025-03-30 11:08:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(21, NULL, 'admin', '2025-03-30 11:49:58', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(22, NULL, 'admin', '2025-03-30 12:11:14', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(23, NULL, 'admin', '2025-03-30 12:18:41', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(24, NULL, 'admin', '2025-03-30 12:18:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(25, NULL, 'admin', '2025-03-30 12:52:18', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(26, NULL, 'admin', '2025-03-30 13:00:02', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(27, NULL, 'admin', '2025-03-30 13:07:43', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(32, NULL, 'mar', '2025-03-27 15:29:49', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(33, NULL, 'admin', '2025-03-27 23:41:19', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(34, NULL, 'admin@admin.com', '2025-03-27 23:41:32', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(35, NULL, 'admin@admin.com', '2025-03-27 23:41:48', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(36, NULL, 'admin@admin.com', '2025-03-27 23:41:58', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(37, NULL, 'admin', '2025-03-27 23:43:31', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(38, NULL, 'admin', '2025-03-28 10:24:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(39, NULL, 'admin', '2025-03-28 18:15:19', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(40, NULL, 'admin', '2025-03-28 22:24:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(41, NULL, 'admin', '2025-03-29 14:25:02', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(42, NULL, 'admin', '2025-03-29 14:25:12', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(43, NULL, 'admin@admin.com', '2025-03-29 14:25:39', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(44, NULL, 'admin', '2025-03-29 14:26:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(45, NULL, 'admin', '2025-03-29 15:12:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(46, NULL, 'admin', '2025-03-29 20:41:01', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(47, NULL, 'admin', '2025-03-29 21:08:50', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(48, NULL, 'admin', '2025-03-29 21:15:33', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(49, NULL, 'admin', '2025-03-29 22:47:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(50, NULL, 'admin', '2025-03-30 09:56:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(51, NULL, 'admin', '2025-03-30 11:08:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:10'),
(52, NULL, 'admin', '2025-03-30 11:49:58', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(53, NULL, 'admin', '2025-03-30 12:11:14', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(54, NULL, 'admin', '2025-03-30 12:18:41', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(55, NULL, 'admin', '2025-03-30 12:18:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(56, NULL, 'admin', '2025-03-30 12:52:18', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(57, NULL, 'admin', '2025-03-30 13:00:02', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:10'),
(58, NULL, 'admin', '2025-03-30 13:07:43', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:10'),
(63, NULL, 'mar', '2025-03-27 15:29:49', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(64, NULL, 'admin', '2025-03-27 23:41:19', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(65, NULL, 'admin@admin.com', '2025-03-27 23:41:32', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(66, NULL, 'admin@admin.com', '2025-03-27 23:41:48', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(67, NULL, 'admin@admin.com', '2025-03-27 23:41:58', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(68, NULL, 'admin', '2025-03-27 23:43:31', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(69, NULL, 'admin', '2025-03-28 10:24:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(70, NULL, 'admin', '2025-03-28 18:15:19', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(71, NULL, 'admin', '2025-03-28 22:24:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(72, NULL, 'admin', '2025-03-29 14:25:02', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(73, NULL, 'admin', '2025-03-29 14:25:12', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(74, NULL, 'admin@admin.com', '2025-03-29 14:25:39', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(75, NULL, 'admin', '2025-03-29 14:26:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(76, NULL, 'admin', '2025-03-29 15:12:00', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(77, NULL, 'admin', '2025-03-29 20:41:01', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(78, NULL, 'admin', '2025-03-29 21:08:50', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(79, NULL, 'admin', '2025-03-29 21:15:33', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(80, NULL, 'admin', '2025-03-29 22:47:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(81, NULL, 'admin', '2025-03-30 09:56:52', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(82, NULL, 'admin', '2025-03-30 11:08:37', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 13:20:20'),
(83, NULL, 'admin', '2025-03-30 11:49:58', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(84, NULL, 'admin', '2025-03-30 12:11:14', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(85, NULL, 'admin', '2025-03-30 12:18:41', NULL, 'failed', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(86, NULL, 'admin', '2025-03-30 12:18:51', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(87, NULL, 'admin', '2025-03-30 12:52:18', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(88, NULL, 'admin', '2025-03-30 13:00:02', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-03-31 13:20:20'),
(89, NULL, 'admin', '2025-03-30 13:07:43', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 13:20:20'),
(94, NULL, 'admin', '2025-03-30 13:57:05', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36 Edg/134.0.0.0', NULL, '2025-03-31 14:00:07'),
(95, NULL, 'admin', '2025-03-30 13:58:06', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36', NULL, '2025-03-31 14:00:07'),
(97, NULL, 'admin', '2025-03-30 14:05:50', NULL, 'success', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:136.0) Gecko/20100101 Firefox/136.0', NULL, '2025-04-01 12:08:18'),
(98, NULL, 'marc123', '2025-03-30 14:20:53', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(99, NULL, 'marc123', '2025-03-30 14:26:01', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(100, NULL, 'admin', '2025-03-30 14:26:20', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(101, NULL, 'admin', '2025-03-30 14:27:29', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(102, NULL, 'admin', '2025-03-30 14:27:36', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(103, NULL, 'admin', '2025-03-30 14:29:58', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(104, NULL, 'ds', '2025-03-30 14:30:52', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(105, NULL, 'dsds', '2025-03-30 14:30:57', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(106, NULL, 'dsds', '2025-03-30 14:31:02', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(107, NULL, 'dsds', '2025-03-30 14:33:28', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(108, NULL, 'dsds', '2025-03-30 14:33:32', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(109, NULL, 'dsds', '2025-03-30 14:33:32', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(110, NULL, 'dsds', '2025-03-30 14:33:32', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(111, NULL, 'admin', '2025-03-30 14:33:39', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(112, NULL, 'marc123', '2025-03-30 14:38:23', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(113, NULL, 'admin', '2025-03-30 14:40:53', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(114, NULL, 'admin', '2025-03-30 14:41:05', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(115, NULL, 'admin', '2025-03-30 14:41:43', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(116, NULL, 'admin', '2025-03-30 14:41:50', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(117, NULL, 'admin', '2025-03-30 14:41:59', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(118, NULL, 'marc123', '2025-03-30 14:42:23', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(119, NULL, 'admin', '2025-03-30 14:43:09', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(120, NULL, 'admin', '2025-03-30 14:43:36', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(121, NULL, 'marc123', '2025-03-30 14:43:49', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(122, NULL, 'admin', '2025-03-30 14:43:56', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(123, NULL, 'admin', '2025-03-30 14:44:23', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(124, NULL, 'admin', '2025-03-30 14:45:57', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(125, NULL, 'admin', '2025-03-30 14:46:01', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(126, NULL, 'marc123', '2025-03-30 14:46:09', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(127, NULL, 'marc123', '2025-03-30 14:47:12', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(128, NULL, 'marc123', '2025-03-30 14:48:48', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(129, NULL, 'marc123', '2025-03-30 14:49:09', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(130, NULL, 'admin', '2025-03-30 14:50:14', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(131, NULL, 'admin', '2025-03-30 14:55:55', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(132, NULL, 'marc123', '2025-03-30 14:56:08', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(133, NULL, 'marc123', '2025-03-30 14:56:26', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(134, NULL, 'admin', '2025-03-30 14:57:50', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(135, NULL, 'marc123', '2025-03-30 14:58:51', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(136, NULL, 'marc123', '2025-03-30 15:00:11', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(137, NULL, 'admin', '2025-03-30 15:00:28', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(138, NULL, 'marc123', '2025-03-30 15:08:26', NULL, 'failed', NULL, NULL, '2025-04-01 12:08:18'),
(139, NULL, 'marc123', '2025-03-30 15:08:35', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(140, NULL, 'admin', '2025-03-30 15:10:06', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(141, NULL, 'admin', '2025-03-30 15:13:56', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(142, NULL, 'admin', '2025-03-30 18:05:44', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(143, NULL, 'marc123', '2025-03-30 18:12:27', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(144, NULL, 'admin', '2025-03-30 18:18:38', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(145, NULL, 'admin', '2025-03-30 18:34:34', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(146, NULL, 'admin', '2025-03-30 21:16:26', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(147, NULL, 'admin', '2025-03-30 23:43:54', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(148, NULL, 'admin', '2025-03-31 11:31:43', NULL, 'success', NULL, NULL, '2025-04-01 12:08:18'),
(160, NULL, 'admin', '2025-03-31 13:47:41', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(161, NULL, 'marc123', '2025-03-31 13:59:35', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(162, NULL, 'admdin', '2025-03-31 13:59:52', NULL, 'failed', NULL, NULL, '2025-04-02 00:18:08'),
(163, NULL, 'admin', '2025-03-31 14:00:03', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(164, NULL, 'admin', '2025-03-31 19:53:29', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(165, NULL, 'marc123', '2025-03-31 19:58:07', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(166, NULL, 'admin', '2025-03-31 21:47:37', NULL, 'success', NULL, NULL, '2025-04-02 00:18:08'),
(167, NULL, 'admin', '2025-04-01 00:28:06', NULL, 'failed', NULL, NULL, '2025-04-02 00:34:55'),
(168, NULL, 'admin', '2025-04-01 00:28:12', NULL, 'success', NULL, NULL, '2025-04-02 00:34:55'),
(170, NULL, 'admin', '2025-04-01 00:51:19', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(171, NULL, 'admin', '2025-04-01 01:38:16', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(172, NULL, 'admin', '2025-04-01 01:41:15', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(173, NULL, 'admin', '2025-04-01 01:42:04', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(174, NULL, 'marc123', '2025-04-01 01:42:44', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(175, NULL, 'admin', '2025-04-01 01:44:51', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(176, NULL, 'admin', '2025-04-01 11:28:51', NULL, 'success', NULL, NULL, '2025-04-02 18:30:46'),
(177, NULL, 'admin', '2025-04-01 21:04:57', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(178, NULL, 'admin', '2025-04-02 00:43:30', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(179, NULL, 'admin', '2025-04-02 11:32:48', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(180, NULL, 'admin', '2025-04-02 12:10:50', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(181, NULL, 'admin', '2025-04-02 17:06:39', NULL, 'failed', NULL, NULL, '2025-04-09 10:59:54'),
(182, NULL, 'admin', '2025-04-02 17:06:51', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(183, NULL, 'admin', '2025-04-02 17:36:24', NULL, 'failed', NULL, NULL, '2025-04-09 10:59:54'),
(184, NULL, 'admin', '2025-04-02 17:36:33', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(185, NULL, 'Eder', '2025-04-02 18:32:02', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(186, NULL, 'admin', '2025-04-02 18:32:21', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(187, NULL, 'marc123', '2025-04-02 18:50:12', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(188, NULL, 'admin', '2025-04-07 22:21:03', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54'),
(189, NULL, 'admin', '2025-04-07 22:27:17', NULL, 'success', NULL, NULL, '2025-04-09 10:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_archived` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `subject`, `message`, `phone`, `is_read`, `is_archived`, `created_at`) VALUES
(24, NULL, 4, 'Contact Form: Hansel Caroline Prado', 'HEloo', NULL, 0, 0, '2025-03-29 15:55:52');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `available_slots` int(11) DEFAULT NULL,
  `itinerary` text DEFAULT NULL,
  `included_services` text DEFAULT NULL,
  `excluded_services` text DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `max_persons` int(11) DEFAULT 10,
  `includes` text DEFAULT NULL,
  `excludes` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `group_pricing` text DEFAULT NULL,
  `activities` text DEFAULT NULL,
  `booking_requirements` text DEFAULT NULL,
  `payment_options` text DEFAULT NULL,
  `best_time_to_visit` text DEFAULT NULL,
  `gallery_images` text DEFAULT NULL,
  `max_people` int(11) DEFAULT 10,
  `views` int(11) DEFAULT 0,
  `discount` decimal(5,2) DEFAULT 0.00,
  `includes_hotel` tinyint(1) DEFAULT 0,
  `includes_meals` tinyint(1) DEFAULT 0,
  `includes_transport` tinyint(1) DEFAULT 0,
  `includes_guide` tinyint(1) DEFAULT 0,
  `inclusions` text DEFAULT NULL,
  `exclusions` text DEFAULT NULL,
  `difficulty` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `destination_id`, `image`, `price`, `duration`, `featured`, `status`, `description`, `start_date`, `end_date`, `available_slots`, `itinerary`, `included_services`, `excluded_services`, `cancellation_policy`, `max_persons`, `includes`, `excludes`, `category`, `group_pricing`, `activities`, `booking_requirements`, `payment_options`, `best_time_to_visit`, `gallery_images`, `max_people`, `views`, `discount`, `includes_hotel`, `includes_meals`, `includes_transport`, `includes_guide`, `inclusions`, `exclusions`, `difficulty`) VALUES
(32, 'Boracay Bliss Getaway', 36, '67ed09b12d358.png', '950.00', 4, 1, 1, '<p><em>Discover the pristine beaches and vibrant nightlife of Boracay, Philippines. This 4-day package offers a perfect mix of relaxation and adventure, from lounging on the powdery white sand of White Beach to thrilling water sports like kite surfing and parasailing. Ideal for both relaxation and exciting activities!</em></p>', NULL, NULL, 23, '<ul><li><strong><em>Day 1:</em></strong><em> Arrival in Boracay, check-in at hotel, free time at White Beach</em></li><li><strong><em>Day 2:</em></strong><em> Morning snorkeling, afternoon water sports (parasailing or kite surfing)</em></li><li><strong><em>Day 3:</em></strong><em> Island hopping tour, visit secluded beaches, free time in the evening for shopping or dining</em></li><li><strong><em>Day 4:</em></strong><em> Relaxing at the beach, optional sunset cruise, return flight to Manila</em></li></ul>', NULL, NULL, NULL, 10, '<ul><li><em>Round-trip airfare to Boracay</em></li><li><em>4-night stay at a beachfront hotel</em></li><li><em>Daily breakfast</em></li><li><em>Boracay island hopping tour</em></li><li><em>Water sport rentals and guided sessions</em></li><li><em>Sunset cruise</em></li></ul>', '<ul><li><em>Personal expenses</em></li><li><em>Meals not included in the itinerary</em></li><li><em>Travel insurance</em></li><li><em>Extra activities or upgrades</em></li></ul>', 'Beach & Adventure', '2-4 people: $900 per person\r\n\r\n5+ people: $850 per person', '<ul><li><em>Relaxing on White Beach</em></li><li><em>Snorkeling and scuba diving</em></li><li><em>Parasailing and kite surfing</em></li><li><em>Sunset cruise</em></li><li><em>Island hopping tour</em></li></ul>', 'Passport valid for at least 6 months\r\n\r\nFull payment upon booking to secure the package\r\n\r\nMinimum group size: 2 people', 'Credit card\r\n\r\nBank transfer\r\n\r\nPayPal', NULL, '[\"67ed09b12d402.jpg\"]', 10, 7, '0.00', 0, 0, 0, 0, NULL, NULL, NULL),
(33, 'Christ the Redeemer Adventure', 45, '67ed0a666afc2.png', '1400.00', 5, 1, 1, '<p><em>Explore the heart of Rio de Janeiro with a visit to the iconic Christ the Redeemer statue. This 5-day package includes guided tours of Rio’s most famous attractions, a visit to the Sugarloaf Mountain, and plenty of free time to experience the vibrant city life. Immerse yourself in the culture, history, and natural beauty of Brazil’s most famous landmarks.<span class=\"ql-cursor\">﻿</span></em></p>', NULL, NULL, 41, '<ul><li><strong><em>Day 1:</em></strong><em> Arrival in Rio de Janeiro, check-in at hotel, free time at Copacabana Beach</em></li><li><strong><em>Day 2:</em></strong><em> Morning tour of Christ the Redeemer and Sugarloaf Mountain, evening free time</em></li><li><strong><em>Day 3:</em></strong><em> City tour including Ipanema Beach, visit to Selaron Steps, and samba dance class</em></li><li><strong><em>Day 4:</em></strong><em> Optional beach day or shopping, evening free for dinner and nightlife exploration</em></li><li><strong><em>Day 5:</em></strong><em> Relaxing morning at the beach, return flight</em></li></ul>', NULL, NULL, NULL, 10, '<ul><li><em>Round-trip airfare to Rio de Janeiro</em></li><li><em>5-night stay at a 4-star hotel</em></li><li><em>Daily breakfast</em></li><li><em>Guided city tour including Christ the Redeemer and Sugarloaf Mountain</em></li><li><em>Samba dance class</em></li><li><em>Airport transfers</em></li></ul>', '<ul><li><em>Personal expenses</em></li><li><em>Meals not specified in the itinerary</em></li><li><em>Travel insurance</em></li><li><em>Extra activities or upgrades<span class=\"ql-cursor\">﻿</span></em></li></ul>', 'Culture & Adventure', '2-4 people: $1,350 per person\r\n\r\n5+ people: $1,300 per person', '<ul><li><em>Visit Christ the Redeemer</em></li><li><em>Cable car ride to Sugarloaf Mountain</em></li><li><em>City tour of Rio de Janeiro (including Copacabana and Ipanema Beaches)</em></li><li><em>Samba dance class</em></li><li><em>Optional beach relaxation and shopping</em></li></ul>', 'Passport valid for at least 6 months\r\n\r\nFull payment upon booking to secure the package\r\n\r\nMinimum group size: 2 people', 'Credit card\r\n\r\nBank transfer\r\n\r\nPayPal', NULL, '[\"67ed0a666b057.png\"]', 10, 0, '0.00', 0, 0, 0, 0, NULL, NULL, NULL),
(34, 'Magical Icehotel Escape', 49, '67ed0b4bdd0b3.jpg', '1400.00', 4, 1, 1, '<p><em>Experience the magic of the world’s most famous ice hotel in Jukkasjärvi, Sweden. This 4-day package combines the thrill of staying in a hotel made entirely of ice and snow with activities like dog sledding, snowmobiling, and Northern Lights viewing. A unique winter getaway perfect for adventure and relaxation in an icy paradise.</em></p>', NULL, NULL, 12, '<ul><li><strong><em>Day 1:</em></strong><em> Arrival in Kiruna, transfer to Icehotel, check-in to Ice Suite, evening Icebar visit</em></li><li><strong><em>Day 2:</em></strong><em> Morning dog sledding tour, afternoon ice sculpting class, evening free for relaxation</em></li><li><strong><em>Day 3:</em></strong><em> Snowmobiling adventure, Northern Lights tour at night</em></li><li><strong><em>Day 4:</em></strong><em> Optional ice fishing or cross-country skiing, return to Kiruna for flight</em></li></ul>', NULL, NULL, NULL, 10, '<ul><li><em>Round-trip airfare to Kiruna</em></li><li><em>4-night stay in an Ice Suite at Icehotel</em></li><li><em>Daily breakfast</em></li><li><em>Dog sledding and snowmobiling excursions</em></li><li><em>Northern Lights viewing tour</em></li><li><em>Ice sculpting class</em></li><li><em>Airport transfers</em></li></ul>', '<ul><li><em>Personal expenses</em></li><li><em>Meals not specified in the itinerary</em></li><li><em>Travel insurance</em></li><li><em>Extra activities or upgrades</em></li></ul><p><br></p>', 'Winter Wonderland & Adventure', '2-4 people: $1,450 per person\r\n\r\n5+ people: $1,400 per person', '<ul><li><em>Stay in an Ice Suite at Icehotel</em></li><li><em>Dog sledding and snowmobiling</em></li><li><em>Northern Lights viewing</em></li><li><em>Ice sculpting class</em></li><li><em>Visit to the Icebar</em></li><li><em>Ice fishing or cross-country skiing (optional)</em></li></ul>', 'Passport valid for at least 6 months\r\n\r\nFull payment upon booking to secure the package\r\n\r\nMinimum group size: 2 people', 'Credit card\r\n\r\nBank transfer\r\n\r\nPayPal', NULL, '[\"67ed0b4be0644.jpg\"]', 10, 1, '0.00', 0, 0, 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `package_dates`
--

CREATE TABLE `package_dates` (
  `id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `available_slots` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `package_gallery`
--

CREATE TABLE `package_gallery` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `package_images`
--

CREATE TABLE `package_images` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `package_itinerary`
--

CREATE TABLE `package_itinerary` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `package_reviews`
--

CREATE TABLE `package_reviews` (
  `id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('success','failed','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `setting_label` varchar(255) NOT NULL,
  `setting_type` enum('text','textarea','number','email','file','select') DEFAULT 'text',
  `setting_options` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_group`, `setting_label`, `setting_type`, `setting_options`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Yana Byahe Na Travel and Tours', 'general', 'Site Name', 'text', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:18'),
(2, 'site_email', 'yanabiyahena@gmail.com', 'general', 'Site Email', 'email', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:18'),
(3, 'site_phone', 'Phone 0917 311 1569 / WhatsApp +63 917 311 1569', 'general', 'Site Phone', 'text', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:18'),
(4, 'site_address', 'Space 41-5 Generoso St. Corner Cervantes Bo. Obrero, Davao City, Davao City, Philippines, 8000', 'general', 'Site Address', 'textarea', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:18'),
(5, 'site_logo', 'logo.png', 'general', 'Site Logo', 'file', NULL, '2025-03-26 14:31:47', '2025-03-26 14:31:47'),
(6, 'currency_symbol', '₱', 'payment', 'Currency Symbol', 'text', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:18'),
(7, 'booking_email', 'yanabiyahena@gmail.com', 'booking', 'Booking Email', 'email', NULL, '2025-03-26 14:31:47', '2025-03-31 06:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `package_id` int(11) DEFAULT NULL,
  `tour_title` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `customer_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `content`, `rating`, `status`, `created_at`, `package_id`, `tour_title`, `updated_at`, `customer_name`, `email`, `image`, `featured`, `customer_email`) VALUES
(4, NULL, '\"I can\'t thank Yana Byhae na Travel and Tours enough for creating the vacation of our dreams! From the moment we reached out, their team provided personalized service that made us feel valued. Our trip to Palawan was meticulously planned with the perfect balance of adventure and relaxation. The tour guides were knowledgeable and friendly, and every accommodation exceeded our expectations. What impressed me most was how they handled a last-minute weather issue by quickly arranging an alternative itinerary that turned out to be even better than our original plan! Their attention to detail and genuine care for their clients\' experiences sets them apart from other travel agencies. We\'ve already booked our next adventure with them for next year!\"', 5, 'approved', '2025-03-30 12:57:50', NULL, NULL, '2025-03-30 12:57:50', 'Hansel Caroline Prado', NULL, '67e93fcef2966.jpg', 0, 'hanselcarolineprado@gmail.com'),
(5, NULL, '\"As a solo traveler, I was nervous about booking a multi-city tour across Southeast Asia, but Yana Byhae na Travel and Tours made the entire experience seamless. Their expert guidance helped me discover hidden gems in Thailand and Vietnam that I would have completely missed on my own. The personalized itinerary respected my budget while still providing quality experiences. Their 24/7 support came in handy when I missed a connection in Bangkok - they had me rebooked and in a hotel within an hour! Their service transformed what could have been a stressful situation into another adventure story. I\'ve recommended them to all my friends who love to travel!\"', 4, 'approved', '2025-03-30 12:59:43', NULL, NULL, '2025-03-30 12:59:43', 'Marc Jullan Pague', NULL, '67e9403f39b0e.png', 0, 'marcjullanp@gmail.com'),
(6, NULL, '\"My wife and I celebrated our 30th anniversary with a dream vacation to Bohol arranged by Yana Byhae na Travel and Tours. From the moment we arrived, every detail was perfect - the flower-decorated room with ocean views, the private sunset dinner on the beach, and the customized tours of the Chocolate Hills and tarsier sanctuary. Their local guides shared fascinating stories that brought the history and culture to life. What truly stands out is how they remembered all our preferences from a brief consultation call. The entire trip felt like it was designed specifically for us, because it was! This level of personalized service is rare and incredibly valuable.\"\r\n', 5, 'approved', '2025-03-30 13:00:47', NULL, NULL, '2025-04-02 03:33:31', 'Mico john Pague', NULL, '67e9407f96ab1.jpg', 0, 'marcjullanp@gmail.com'),
(7, NULL, '\"Our company retreat needed to be both relaxing and team-building focused, a difficult balance to achieve. Yana Byhae na Travel and Tours listened carefully to our needs and created a custom 3-day experience in Batangas that exceeded everyone\'s expectations. The beachfront venue was stunning, activities were perfectly timed, and they even arranged surprise welcome packages for all 27 team members! The feedback from our employees was unanimously positive, with many calling it \'the best corporate retreat ever.\' Their attention to detail and professional service made our job as organizers so much easier. We\'ll definitely be using their services for our annual retreats going forward.\"', 3, 'approved', '2025-03-30 13:02:10', NULL, NULL, '2025-03-30 13:02:10', 'Sydney Illa Prado', NULL, '67e940d20103a.jpg', 0, 'hanselcarolineprado@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `top_client_picks`
--

CREATE TABLE `top_client_picks` (
  `id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `featured_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `top_picks`
--

CREATE TABLE `top_picks` (
  `id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `featured_order` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `client_name` varchar(255) DEFAULT NULL,
  `client_image` varchar(255) DEFAULT 'default-user.png',
  `client_rating` int(11) DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `top_picks`
--

INSERT INTO `top_picks` (`id`, `destination_id`, `featured_order`, `description`, `image`, `status`, `created_at`, `updated_at`, `client_name`, `client_image`, `client_rating`) VALUES
(9, 36, 1, 'Nice view', NULL, 1, '2025-04-02 10:26:36', '2025-04-02 10:26:36', 'Marc Jullan Pague', 'client_67ed10dc715e7.jpg', 5),
(10, 48, 1, 'eeeee', NULL, 1, '2025-04-03 11:45:34', '2025-04-03 11:45:34', 'www', 'client_67ee74de1884c.jpg', 5),
(12, 49, 1, 'wewew', NULL, 1, '2025-04-03 11:46:06', '2025-04-03 11:46:06', 'wewew', 'client_67ee74feeabe6.jpg', 5),
(13, 40, 1, 'e', NULL, 1, '2025-04-03 11:46:19', '2025-04-03 11:46:19', 'waea', 'client_67ee750b8fade.jpg', 5),
(14, 37, 1, 'dsadasdsad', NULL, 1, '2025-04-03 11:46:38', '2025-04-03 11:46:38', 'Hansel', 'client_67ee751e1f1b7.jpg', 5);

-- --------------------------------------------------------

--
-- Table structure for table `tours`
--

CREATE TABLE `tours` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `image_main` varchar(255) DEFAULT NULL,
  `image_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_gallery`)),
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `inclusions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`inclusions`)),
  `itinerary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`itinerary`)),
  `featured` tinyint(1) DEFAULT 0,
  `popular` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`, `profile_image`, `account_status`, `last_login`, `status`, `created_at`) VALUES
(4, 'admin', 'admin@gmail.com', '$2y$10$CZjqW9s4lDVBrshJln.YuO2GJLnOU8CMD2UKynROppP39xT0ZGc0q', 'admin', NULL, NULL, '67e41011c1377.jpg', 'active', '2025-03-30 11:34:47', 1, '2025-03-28 16:48:05'),
(25, 'marc123', 'marcjullanp@gmail.com', '$2y$10$obQ7pzRn.74tT8qeKDTNNu7gfcYjzMy8h9tBk80cVJzCY/9nfrotS', 'user', NULL, NULL, '67e8dfe17d5ba.png', 'active', '2025-03-30 06:08:33', 1, '2025-03-30 06:08:33'),
(27, 'Eder', 'ronaldeder@gmail.com', '$2y$10$jjVIv0jBFtbnUgcddR9G5uLl.8ZruzRxsYpn/EDAp2cEMvCMIg0se', 'user', NULL, NULL, '67ed120ee83f5.jpg', 'active', '2025-04-02 10:31:43', 1, '2025-04-02 10:31:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archived_login_history`
--
ALTER TABLE `archived_login_history`
  ADD PRIMARY KEY (`login_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `booking_inquiries`
--
ALTER TABLE `booking_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_inquiry` (`email`,`package_id`,`preferred_date`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `featured_destinations`
--
ALTER TABLE `featured_destinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking_inquiry` (`email`,`tour_id`,`created_at`),
  ADD KEY `tour_id` (`tour_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_history_archive`
--
ALTER TABLE `login_history_archive`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `package_dates`
--
ALTER TABLE `package_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `package_gallery`
--
ALTER TABLE `package_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `package_images`
--
ALTER TABLE `package_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `package_itinerary`
--
ALTER TABLE `package_itinerary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `package_reviews`
--
ALTER TABLE `package_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_testimonial_status` (`status`),
  ADD KEY `idx_testimonial_package` (`package_id`);

--
-- Indexes for table `top_client_picks`
--
ALTER TABLE `top_client_picks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `top_picks`
--
ALTER TABLE `top_picks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_destination_id` (`destination_id`),
  ADD KEY `idx_featured_order` (`featured_order`);

--
-- Indexes for table `tours`
--
ALTER TABLE `tours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `archived_login_history`
--
ALTER TABLE `archived_login_history`
  MODIFY `login_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `booking_inquiries`
--
ALTER TABLE `booking_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `featured_destinations`
--
ALTER TABLE `featured_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `login_history_archive`
--
ALTER TABLE `login_history_archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `package_dates`
--
ALTER TABLE `package_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_gallery`
--
ALTER TABLE `package_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_images`
--
ALTER TABLE `package_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_itinerary`
--
ALTER TABLE `package_itinerary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_reviews`
--
ALTER TABLE `package_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `top_client_picks`
--
ALTER TABLE `top_client_picks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `top_picks`
--
ALTER TABLE `top_picks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tours`
--
ALTER TABLE `tours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `booking_inquiries`
--
ALTER TABLE `booking_inquiries`
  ADD CONSTRAINT `booking_inquiries_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Constraints for table `featured_destinations`
--
ALTER TABLE `featured_destinations`
  ADD CONSTRAINT `featured_destinations_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `fk_package_id` FOREIGN KEY (`tour_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`);

--
-- Constraints for table `package_dates`
--
ALTER TABLE `package_dates`
  ADD CONSTRAINT `package_dates_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Constraints for table `package_gallery`
--
ALTER TABLE `package_gallery`
  ADD CONSTRAINT `package_gallery_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_reviews`
--
ALTER TABLE `package_reviews`
  ADD CONSTRAINT `package_reviews_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `package_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `fk_testimonial_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `top_client_picks`
--
ALTER TABLE `top_client_picks`
  ADD CONSTRAINT `top_client_picks_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `top_picks`
--
ALTER TABLE `top_picks`
  ADD CONSTRAINT `top_picks_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tours`
--
ALTER TABLE `tours`
  ADD CONSTRAINT `tours_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

