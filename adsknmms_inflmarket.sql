-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 07:09 PM
-- Server version: 12.2.2-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adsknmms_inflmarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `affiliates`
--

CREATE TABLE `affiliates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `agency_name` varchar(191) DEFAULT NULL,
  `referral_code` varchar(20) NOT NULL,
  `default_commission_percent` decimal(5,2) NOT NULL DEFAULT 10.00,
  `total_earnings` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `affiliates`
--

INSERT INTO `affiliates` (`id`, `user_id`, `agency_name`, `referral_code`, `default_commission_percent`, `total_earnings`, `created_at`, `updated_at`) VALUES
(1, 2, 'Rahul Agency', 'RAHUL10', 10.00, 0.00, '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(2, 11, 'Rahul Digital Agency', '613FD820', 12.50, 0.00, '2026-04-17 19:08:26', '2026-04-17 19:14:46');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('email_verify','password_reset') NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) NOT NULL,
  `logo_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `industry_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `user_id`, `name`, `logo_url`, `description`, `website`, `industry_id`, `created_at`, `updated_at`) VALUES
(1, 4, 'Acme Cosmetics', NULL, 'Clean beauty brand.', 'https://acme-cosmetics.test', 6, '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(2, 8, 'Acme Cosmetics Updated', NULL, 'Premium clean beauty brand', 'https://acme-cosmetics.com', NULL, '2026-04-17 17:17:06', '2026-04-17 17:18:09'),
(3, 16, 'bappa', NULL, NULL, NULL, NULL, '2026-04-18 13:28:26', '2026-04-18 13:28:26');

-- --------------------------------------------------------

--
-- Table structure for table `brand_products`
--

CREATE TABLE `brand_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `description` text DEFAULT NULL,
  `campaign_goal` text DEFAULT NULL,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brand_products`
--

INSERT INTO `brand_products` (`id`, `brand_id`, `title`, `description`, `campaign_goal`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Acme Glow Serum', 'Vitamin C brightening serum.', 'Drive awareness and sales for launch.', 'active', '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(2, 2, 'Glow Serum', 'Vitamin C brightening serum for radiant skin', 'Drive awareness and sales for Q1 launch', 'archived', '2026-04-17 17:18:40', '2026-04-18 13:56:07'),
(3, 2, 'Glow Serum Pro', 'test test', NULL, 'active', '2026-04-17 17:19:22', '2026-04-17 17:23:49'),
(4, 3, 'Clenzoo car washing', 'clenzoo is a doorstep car washing company', 'To get bookings for car and bike washing', 'draft', '2026-04-18 14:02:01', '2026-04-18 14:02:01');

-- --------------------------------------------------------

--
-- Table structure for table `brand_product_media`
--

CREATE TABLE `brand_product_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `url` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `brief` text DEFAULT NULL,
  `budget` decimal(14,2) NOT NULL DEFAULT 0.00,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `brand_id`, `product_id`, `title`, `brief`, `budget`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Glow Serum Launch', 'Launch campaign targeting beauty audience.', 50000.00, '2026-04-12', '2026-05-12', 'active', '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(2, 2, NULL, 'Summer Glow Launch', 'Promote our new Glow Serum to beauty-focused audiences', 50000.00, '2026-05-01', '2026-05-31', 'draft', '2026-04-17 20:28:50', '2026-04-17 20:28:50'),
(3, 3, NULL, 'Clenzoo product launch', 'Launch of clenzoo in you area', 5000.00, '2026-04-18', '2026-04-19', 'draft', '2026-04-18 14:03:05', '2026-04-18 14:03:05');

-- --------------------------------------------------------

--
-- Table structure for table `campaign_deals`
--

CREATE TABLE `campaign_deals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `campaign_id` bigint(20) UNSIGNED NOT NULL,
  `influencer_id` bigint(20) UNSIGNED NOT NULL,
  `affiliate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `platform` enum('instagram','facebook','youtube','twitter','tiktok') NOT NULL,
  `content_type` enum('post','video','reel','story') NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `influencer_base_cost` decimal(12,2) NOT NULL,
  `affiliate_commission_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `platform_commission_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `affiliate_commission_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `platform_commission_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','accepted','rejected','in_progress','submitted','approved','completed','cancelled') NOT NULL DEFAULT 'pending',
  `deadline` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campaign_deals`
--

INSERT INTO `campaign_deals` (`id`, `campaign_id`, `influencer_id`, `affiliate_id`, `platform`, `content_type`, `quantity`, `influencer_base_cost`, `affiliate_commission_percent`, `platform_commission_percent`, `affiliate_commission_amount`, `platform_commission_amount`, `total_amount`, `status`, `deadline`, `created_at`, `updated_at`) VALUES
(2, 2, 3, NULL, 'instagram', 'post', 2, 5000.00, 0.00, 15.00, 0.00, 1500.00, 11500.00, 'completed', '2026-05-15', '2026-04-17 20:32:47', '2026-04-17 20:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`) VALUES
(1, 'Technology', 'technology', NULL),
(2, 'Fashion', 'fashion', NULL),
(3, 'Fitness', 'fitness', NULL),
(4, 'Food & Cooking', 'food-cooking', NULL),
(5, 'Travel', 'travel', NULL),
(6, 'Beauty', 'beauty', NULL),
(7, 'Gaming', 'gaming', NULL),
(8, 'Education', 'education', NULL),
(9, 'Lifestyle', 'lifestyle', NULL),
(10, 'Finance', 'finance', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deal_deliverables`
--

CREATE TABLE `deal_deliverables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `post_url` varchar(500) DEFAULT NULL,
  `media_url` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `status` enum('pending','submitted','approved','rejected') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deal_deliverables`
--

INSERT INTO `deal_deliverables` (`id`, `deal_id`, `post_url`, `media_url`, `notes`, `submitted_at`, `approved_at`, `status`) VALUES
(1, 2, 'https://instagram.com/p/ABC123', NULL, 'Posted the sponsored content with required hashtags', '2026-04-17 20:36:24', NULL, 'submitted');

-- --------------------------------------------------------

--
-- Table structure for table `influencers`
--

CREATE TABLE `influencers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `affiliate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `display_name` varchar(150) NOT NULL,
  `bio` text DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `languages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages`)),
  `base_cost_per_post` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_cost_per_video` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `influencers`
--

INSERT INTO `influencers` (`id`, `user_id`, `affiliate_id`, `display_name`, `bio`, `category_id`, `location`, `languages`, `base_cost_per_post`, `base_cost_per_video`, `currency`, `is_verified`, `created_at`, `updated_at`) VALUES
(3, 10, NULL, '', 'Beauty and lifestyle creator based in Mumbai', 6, 'Mumbai, IN', '[\"en\",\"hi\"]', 5000.00, 12000.00, '', 0, '2026-04-17 18:47:37', '2026-04-17 18:47:55'),
(4, 12, 2, '', 'Fashion and beauty influencer', NULL, NULL, NULL, 5000.00, 12000.00, 'INR', 0, '2026-04-17 19:15:02', '2026-04-17 19:15:34');

-- --------------------------------------------------------

--
-- Table structure for table `influencer_posts`
--

CREATE TABLE `influencer_posts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `social_account_id` bigint(20) UNSIGNED NOT NULL,
  `external_post_id` varchar(191) NOT NULL,
  `post_type` enum('image','video','reel','story','carousel') NOT NULL,
  `caption` text DEFAULT NULL,
  `media_url` varchar(500) DEFAULT NULL,
  `permalink` varchar(500) DEFAULT NULL,
  `views` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `likes` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `comments` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `shares` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `posted_at` datetime DEFAULT NULL,
  `fetched_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `influencer_social_accounts`
--

CREATE TABLE `influencer_social_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `influencer_id` bigint(20) UNSIGNED NOT NULL,
  `platform` enum('instagram','facebook','youtube','twitter','tiktok') NOT NULL,
  `handle` varchar(150) NOT NULL,
  `profile_url` varchar(500) DEFAULT NULL,
  `platform_user_id` varchar(191) DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `followers_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `following_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `posts_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `engagement_rate` decimal(6,3) NOT NULL DEFAULT 0.000,
  `avg_views` decimal(14,2) NOT NULL DEFAULT 0.00,
  `avg_likes` decimal(14,2) NOT NULL DEFAULT 0.00,
  `avg_comments` decimal(14,2) NOT NULL DEFAULT 0.00,
  `last_synced_at` datetime DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `influencer_social_accounts`
--

INSERT INTO `influencer_social_accounts` (`id`, `influencer_id`, `platform`, `handle`, `profile_url`, `platform_user_id`, `access_token`, `token_expires_at`, `followers_count`, `following_count`, `posts_count`, `engagement_rate`, `avg_views`, `avg_likes`, `avg_comments`, `last_synced_at`, `is_primary`, `created_at`, `updated_at`) VALUES
(7, 3, 'instagram', 'priyasharma', 'https://instagram.com/priyasharma', NULL, NULL, NULL, 125000, 0, 0, 0.000, 0.00, 0.00, 0.00, NULL, 1, '2026-04-17 18:49:34', '2026-04-17 18:49:34'),
(8, 3, 'facebook', 'Priya Sharma', 'https://clenzoo.com/login/manager_staff_tracking.php', NULL, NULL, NULL, 3000, 0, 0, 0.000, 0.00, 0.00, 0.00, NULL, 0, '2026-04-18 13:59:06', '2026-04-18 13:59:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(191) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_accounts`
--

CREATE TABLE `oauth_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `provider` enum('google','facebook','instagram','twitter') NOT NULL,
  `provider_user_id` varchar(191) NOT NULL,
  `access_token` text DEFAULT NULL,
  `refresh_token` text DEFAULT NULL,
  `token_expires_at` datetime DEFAULT NULL,
  `profile_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`profile_data`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `gateway` enum('razorpay','stripe','manual') NOT NULL,
  `gateway_txn_id` varchar(191) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'INR',
  `status` enum('initiated','success','failed','refunded') NOT NULL DEFAULT 'initiated',
  `paid_at` datetime DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `deal_id`, `brand_id`, `gateway`, `gateway_txn_id`, `amount`, `currency`, `status`, `paid_at`, `raw_response`, `created_at`) VALUES
(1, 2, 2, 'manual', 'TXN-MANUAL-001', 11500.00, 'INR', 'success', '2026-04-17 17:43:36', NULL, '2026-04-17 21:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `payouts`
--

CREATE TABLE `payouts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `payee_type` enum('influencer','affiliate','platform') NOT NULL,
  `payee_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','processing','paid','failed') NOT NULL DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payouts`
--

INSERT INTO `payouts` (`id`, `payment_id`, `deal_id`, `payee_type`, `payee_user_id`, `amount`, `status`, `paid_at`, `notes`, `created_at`) VALUES
(1, 1, 2, 'influencer', 10, 10000.00, 'pending', NULL, NULL, '2026-04-17 21:13:36'),
(2, 1, 2, 'platform', NULL, 1500.00, 'pending', NULL, NULL, '2026-04-17 21:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(500) NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`id`, `setting_key`, `setting_value`, `updated_by`, `updated_at`) VALUES
(1, 'default_platform_commission_percent', '18.00', 15, '2026-04-17 21:27:26'),
(2, 'default_affiliate_commission_percent', '10.00', NULL, '2026-04-12 07:03:47'),
(3, 'default_currency', 'INR', NULL, '2026-04-12 07:03:47'),
(4, 'min_payout_amount', '1000.00', 15, '2026-04-17 21:27:26'),
(5, 'jwt_access_ttl_minutes', '15', NULL, '2026-04-12 07:03:47'),
(6, 'jwt_refresh_ttl_days', '30', NULL, '2026-04-12 07:03:47');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rate_key` varchar(191) NOT NULL,
  `hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

CREATE TABLE `refresh_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `user_agent`, `ip_address`, `expires_at`, `revoked_at`, `created_at`) VALUES
(1, 5, '11d5ff625614d30567e66738e433f6d316c7197b53fa9f6c139c4760a9df6585', 'curl/8.18.0', '::1', '2026-05-12 14:58:06', NULL, '2026-04-12 14:58:06'),
(2, 5, '6c6dbad2c267efaf90d888ffb353f551b1141d59bafb598a87d98db8320053cf', 'curl/8.18.0', '::1', '2026-05-12 14:59:47', NULL, '2026-04-12 14:59:47'),
(3, 5, '7d4e00d132ca67212edbaae662aa53fcd4baeb69655a1e78d8479b7ce6bedafd', 'curl/8.18.0', '::1', '2026-05-13 23:57:34', NULL, '2026-04-13 23:57:34'),
(4, 5, '7472fefc1d802a4be8ea23aa2985e117ec246b26df6fa68e8acb610d0f40e7e8', 'curl/8.18.0', '::1', '2026-05-14 00:15:39', NULL, '2026-04-14 00:15:39'),
(5, 5, '36aab8cc76844cf6a58f7e2b03d0dc30945e382da1404a8ae95fb22cbce96aaf', 'curl/8.18.0', '::1', '2026-05-14 00:46:20', NULL, '2026-04-14 00:46:20'),
(6, 5, '393d94c01afb04bcf9148fb16481aeaf5072e7c1fbf4526a55eafc476c1c8b5c', 'curl/8.18.0', '::1', '2026-05-14 01:06:17', NULL, '2026-04-14 01:06:17'),
(7, 6, 'a704133df77289abd597b6b8af1cafdfb8dafb7787e6bfb5d0f43b7af62df27e', 'curl/8.18.0', '::1', '2026-05-14 01:10:36', NULL, '2026-04-14 01:10:36'),
(8, 6, '3a5f5db623977eb786bcf13dcfc7271b87d723004b9ef01eeb101d077c6826c7', 'curl/8.18.0', '::1', '2026-05-14 01:11:33', NULL, '2026-04-14 01:11:33'),
(9, 6, '8175ee7b20e52784e59107db0f287c5b2ab601029a6a31e4ed66915301f54b2f', 'curl/8.18.0', '::1', '2026-05-17 16:13:29', NULL, '2026-04-17 16:13:29'),
(10, 6, '893d105fe9000dd45591eaf32ca2a994b5e06d1a0d65d24894d026742f590ab3', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:21:59', NULL, '2026-04-17 16:21:59'),
(11, 6, 'c4fe89a182f9349bd7d730821813c4d56c246295a91a2a8803e1f4dd1eb9f564', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:23:12', '2026-04-17 16:23:13', '2026-04-17 16:23:12'),
(12, 6, 'faaed0b2abe2f065c6cd45fd40a720b3a8b407b63432198daa4ebf6e74cd9276', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:23:13', '2026-04-17 16:23:13', '2026-04-17 16:23:13'),
(13, 6, '462d190362af51c703e290de58e7b7668675e9b4442b0e5d7054796bdcd43d21', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:27:02', NULL, '2026-04-17 16:27:02'),
(14, 7, '92349d302ae3aac12d5c1ecd4733ebb20c09d6088d98b834792cc4e49d617424', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:27:57', '2026-04-17 16:30:21', '2026-04-17 16:27:57'),
(15, 7, 'a5e6b61a9f923be5ab4ea123eaf9c11fbeb655c04504f97d8e1ddabb04e7bafa', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 16:30:21', '2026-04-17 16:32:21', '2026-04-17 16:30:21'),
(16, 7, '7ad02c40eeb2219db9b4248778e0d9451fef2b3d87fb4cecbd6cb2375fa4fd1a', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 17:16:19', '2026-04-17 17:16:44', '2026-04-17 17:16:19'),
(22, 10, '5d67aecd7e78a4d49dd525bc08bc5a291e074e4437f82463dd0bb27eee6f8d73', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 18:47:43', '2026-04-17 19:07:27', '2026-04-17 18:47:43'),
(23, 10, '0e9cf2d8b82f4b8b4684bfffbb13b8099f0d0acd63810ca8818bfbfc6355832c', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 19:07:27', '2026-04-17 19:07:31', '2026-04-17 19:07:27'),
(24, 11, 'd51bd86e6cf8d6ef6b70f51e6baab8d6f88c044ecfb0a5acb232bd6e2e19e1ec', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 19:11:56', '2026-04-17 20:25:16', '2026-04-17 19:11:56'),
(25, 11, 'd43f49d83cfd1ae58d7e2a3a33627e51b26482f4d6f8d02ea724e7f84378dedf', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:25:16', '2026-04-17 20:25:21', '2026-04-17 20:25:16'),
(26, 8, '48044d9fd02ed7ae408b34267aedc675dbf6a592f398aee872e478f66a4fec12', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:25:44', NULL, '2026-04-17 20:25:44'),
(27, 6, '9632d22d8f2c6036f8c91c25c46a2c349153758e43dcca5cfcf5a42bc789444e', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:27:30', '2026-04-17 20:28:39', '2026-04-17 20:27:30'),
(28, 8, 'a66a374065ed94a4dc8f754ec9fbf1d620ab177efb2af2c2505eee534cb2717e', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:28:45', '2026-04-17 20:35:12', '2026-04-17 20:28:45'),
(29, 10, '6eb4c72956b340705e8491916b8b0ec1c7c269ab794d0594e7dbf810a793a3e1', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:35:24', '2026-04-17 20:38:02', '2026-04-17 20:35:24'),
(30, 6, 'd0b76995410952607b85aeab0a913d57d713690127f1eb24fed6fdc80ab769c8', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:38:07', '2026-04-17 20:39:45', '2026-04-17 20:38:07'),
(31, 8, '3f020f44ccd90b733d49e484eca95e63e20dbb39afb5e0c18a22025a56cfe372', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 20:39:50', '2026-04-17 21:13:25', '2026-04-17 20:39:50'),
(32, 8, '4a47a8bb039023691c2606ed23b18457ad47485e132a27593fb0f92fe59f1702', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 21:13:25', NULL, '2026-04-17 21:13:25'),
(33, 15, '5ba4da3a3236c382a0fdd337cd8fa419f9fce23f51417666aef9da7f7106f149', 'bruno-runtime/3.2.2', '127.0.0.1', '2026-05-17 21:24:00', NULL, '2026-04-17 21:24:00'),
(34, 16, 'b5ad6eb1e9d951b748e07868cf9b563688ac69457e46d6c3e7861ebc5a816404', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 13:28:26', '2026-04-18 13:41:43', '2026-04-18 13:28:26'),
(35, 7, '275227b32510b5892430fc481f909f700b2630b8109fab3464cebe6e82cb1686', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 13:45:10', NULL, '2026-04-18 13:45:10'),
(36, 15, '1661043b8c33a2bf57e667daa23e396d2ad9596e98780e6a6f9ca74362777b6c', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 13:50:36', '2026-04-18 13:55:23', '2026-04-18 13:50:36'),
(37, 8, 'e5f69b2da28ae95dd17bed219bd14105dbd3e0698b3fd84a6a9773927ec64cea', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 13:55:38', '2026-04-18 13:57:25', '2026-04-18 13:55:38'),
(38, 10, '2f737c94b17244f5a76f77113c14166f7ed5583996838bf8ffb080f8701365e2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 13:57:37', '2026-04-18 14:00:11', '2026-04-18 13:57:37'),
(39, 16, '7bbf1f202625b1bb178e724bcfb269d8713cb7432a156d0f8813a77057b838f2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:00:32', '2026-04-18 14:03:27', '2026-04-18 14:00:32'),
(40, 11, 'ca048cb76b31bf6046852bfdf3b981285f5a4f0b76e507b366e35d2759a5c4b9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:03:53', '2026-04-18 14:04:37', '2026-04-18 14:03:53'),
(41, 10, 'fa60a338b581a2817cb5033a23d9523cb7da77de1947c5c31dea25244857d15a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:05:07', '2026-04-18 14:06:00', '2026-04-18 14:05:07'),
(42, 16, '7ad2f9bd51d277a603a3bb4a01ad9c908fa62692bb2d0c7f002dc9552dc0dec5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:08:28', '2026-04-18 14:32:32', '2026-04-18 14:08:28'),
(43, 16, '5f8b40d6ce925ea581e0d61fd7f0a43ae9047439c9d92f5681d281c57d4ea009', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:32:32', NULL, '2026-04-18 14:32:32'),
(44, 10, '9d99af98d5df10c6e8809855c8adad18d89e7018c66be1fb48ca0c1f1980bff3', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-18 14:35:50', '2026-04-20 11:35:54', '2026-04-18 14:35:50'),
(45, 10, '015d942d456cca4dad94e26905abf4260dfd0669ce9034a63b1a2425dbbe1cbc', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-20 11:35:54', '2026-04-20 13:04:13', '2026-04-20 11:35:54'),
(46, 10, '982c51868539e91a07f929c0aba08071e00ac169a8a5648e9a4e5d22ac85d397', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-20 13:04:13', '2026-04-20 14:47:28', '2026-04-20 13:04:13'),
(47, 10, 'e2a14ce07cabb30be61925dfead36377be36dd008904890887ea3d0c2d3cf02a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-20 14:47:28', '2026-04-20 14:56:31', '2026-04-20 14:47:28'),
(48, 7, '4e851e5920f16ed1b1518356d7ec84bde68674a8bbc101c71632f1f7b0183b28', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-20 14:57:37', '2026-04-20 15:15:48', '2026-04-20 14:57:37'),
(49, 7, 'f56877153904420fc9721b2f7ea7cf51d96de0246ebde958313213d545a9b39a', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', '127.0.0.1', '2026-05-20 15:15:48', NULL, '2026-04-20 15:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `deal_id` bigint(20) UNSIGNED NOT NULL,
  `reviewer_user_id` bigint(20) UNSIGNED NOT NULL,
  `reviewee_user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `deal_id`, `reviewer_user_id`, `reviewee_user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 8, 10, 5, 'Excellent content quality and delivered on time', '2026-04-17 21:15:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `role` enum('influencer','brand','affiliate','admin') NOT NULL,
  `status` enum('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending',
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `email`, `phone`, `password_hash`, `full_name`, `avatar_url`, `role`, `status`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`) VALUES
(2, '22222222-2222-2222-2222-222222222222', 'affiliate1@platform.test', NULL, '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ12345', 'Rahul Agency', NULL, 'affiliate', 'active', '2026-04-12 07:03:47', NULL, '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(3, '33333333-3333-3333-3333-333333333333', 'influencer1@platform.test', NULL, '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ12345', 'Priya Sharma', NULL, 'influencer', 'active', '2026-04-12 07:03:47', NULL, '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(4, '44444444-4444-4444-4444-444444444444', 'brand1@platform.test', NULL, '$2y$10$abcdefghijklmnopqrstuvABCDEFGHIJKLMNOPQRSTUVWXYZ12345', 'Acme Cosmetics', NULL, 'brand', 'active', '2026-04-12 07:03:47', NULL, '2026-04-12 07:03:47', '2026-04-12 07:03:47'),
(5, 'c348a092-0810-4106-9c90-53aff9fb10d2', 'test@example.com', NULL, '$2y$10$nt4N1SNJbEZyZo63Rbgs1ejxKaPqwW3x9TZl7KA6MQc.Z4y7tnDfa', 'Test User', NULL, 'brand', 'active', NULL, NULL, '2026-04-12 14:45:11', '2026-04-12 14:45:11'),
(6, 'b7222876-129f-4335-87ab-2d0a92297e5c', 'flow@test.com', NULL, '$2y$10$67KkTBcjlV5zJIqE0XR1EO56hbKvWlSB4owEQbjIr8/A/MbUs9Am6', 'Flow Test', NULL, 'brand', 'active', NULL, NULL, '2026-04-14 01:10:10', '2026-04-14 01:10:10'),
(7, '3d39103c-6b9b-40a2-992f-3c81d345f1a8', 'flow@gmail.com', NULL, '$2y$10$4j0HGrnFC1AUDaSc1BMDme4zuue5axPsfmAplNSoXpvV.cvwqZZ16', 'Flow Test 2', NULL, 'brand', 'active', NULL, NULL, '2026-04-17 16:24:53', '2026-04-17 16:24:53'),
(8, '830c222e-b486-4456-be50-9e0c1c5d7331', 'flow3@gmail.com', NULL, '$2y$10$jqXSf89ud45fK.1lDpeedepBg5M4ZGkpBD7MJwwV8ekTcR3wRGP6O', 'Flow Test 3', NULL, 'brand', 'active', NULL, NULL, '2026-04-17 17:17:06', '2026-04-17 17:17:06'),
(10, '4687f868-ae30-43cb-b632-f23e81a43f41', 'influencer@test.com', NULL, '$2y$10$uVpJEd4zdJHxKTZ1tQW3H.JsZmU/CbMZnM3G2Oi557.W9T3019VYm', 'Priya Sharma', NULL, 'influencer', 'active', NULL, NULL, '2026-04-17 18:47:37', '2026-04-17 18:47:37'),
(11, 'dbd4110d-d672-40aa-b0b3-f950a95fbcee', 'affiliate@test.com', NULL, '$2y$10$IJvtuQs7LftKKywt5ZI3k.YnpM8GST7tJYZbww9ilQJLlbbdzOluS', 'Rahul Agency', NULL, 'affiliate', 'active', NULL, NULL, '2026-04-17 19:08:26', '2026-04-17 19:08:26'),
(12, '1d5b8c4c-4910-4210-9b12-1fec846062ae', 'onboarded-influencer@test.com', NULL, '$2y$10$RRUcaVyMmCZQis/WAuxbee1KZ0SprUnFDBuOb4Cx6W9lJSRCqmyzy', 'Sneha Kapoor', NULL, 'influencer', 'active', NULL, NULL, '2026-04-17 19:15:02', '2026-04-17 19:15:02'),
(15, '9308f257-3a75-11f1-a6cc-4851c5a0e84d', 'admin@test.com', NULL, '$2y$10$IJvtuQs7LftKKywt5ZI3k.YnpM8GST7tJYZbww9ilQJLlbbdzOluS', 'Platform Admin', NULL, 'admin', 'active', '2026-04-17 21:23:27', NULL, '2026-04-17 21:23:27', '2026-04-17 21:23:50'),
(16, 'ab76909c-c946-43d0-b7c0-ff03f16f246b', 'bappa@gmail.com', NULL, '$2y$10$nuzMip3grdQ0AJjQuNB.Re00hnMJWKSb.Y5rj/5dfxB1OENYqZ5km', 'bappa', NULL, 'brand', 'active', NULL, NULL, '2026-04-18 13:28:26', '2026-04-18 13:28:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `affiliates`
--
ALTER TABLE `affiliates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_at_user` (`user_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_brand_industry` (`industry_id`);

--
-- Indexes for table `brand_products`
--
ALTER TABLE `brand_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bp_brand` (`brand_id`);

--
-- Indexes for table `brand_product_media`
--
ALTER TABLE `brand_product_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bpm_product` (`product_id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_camp_brand` (`brand_id`),
  ADD KEY `idx_camp_status` (`status`),
  ADD KEY `fk_camp_product` (`product_id`);

--
-- Indexes for table `campaign_deals`
--
ALTER TABLE `campaign_deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cd_campaign` (`campaign_id`),
  ADD KEY `idx_cd_influencer` (`influencer_id`),
  ADD KEY `idx_cd_status` (`status`),
  ADD KEY `fk_cd_affiliate` (`affiliate_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_cat_parent` (`parent_id`);

--
-- Indexes for table `deal_deliverables`
--
ALTER TABLE `deal_deliverables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dd_deal` (`deal_id`);

--
-- Indexes for table `influencers`
--
ALTER TABLE `influencers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_inf_category` (`category_id`),
  ADD KEY `idx_inf_affiliate` (`affiliate_id`);

--
-- Indexes for table `influencer_posts`
--
ALTER TABLE `influencer_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_post` (`social_account_id`,`external_post_id`);

--
-- Indexes for table `influencer_social_accounts`
--
ALTER TABLE `influencer_social_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_platform_handle` (`platform`,`handle`),
  ADD KEY `idx_isa_influencer` (`influencer_id`),
  ADD KEY `idx_isa_followers` (`followers_count`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user_unread` (`user_id`,`read_at`);

--
-- Indexes for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_oauth_provider` (`provider`,`provider_user_id`),
  ADD KEY `idx_oauth_user` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pay_deal` (`deal_id`),
  ADD KEY `idx_pay_status` (`status`),
  ADD KEY `fk_pay_brand` (`brand_id`);

--
-- Indexes for table `payouts`
--
ALTER TABLE `payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_po_payment` (`payment_id`),
  ADD KEY `idx_po_payee` (`payee_type`,`payee_user_id`),
  ADD KEY `fk_po_deal` (`deal_id`),
  ADD KEY `fk_po_user` (`payee_user_id`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `fk_ps_user` (`updated_by`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_key` (`rate_key`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rt_user` (`user_id`),
  ADD KEY `idx_rt_token` (`token_hash`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rev_deal` (`deal_id`),
  ADD KEY `fk_rev_reviewer` (`reviewer_user_id`),
  ADD KEY `fk_rev_reviewee` (`reviewee_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `affiliates`
--
ALTER TABLE `affiliates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `brand_products`
--
ALTER TABLE `brand_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brand_product_media`
--
ALTER TABLE `brand_product_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `campaign_deals`
--
ALTER TABLE `campaign_deals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deal_deliverables`
--
ALTER TABLE `deal_deliverables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `influencers`
--
ALTER TABLE `influencers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `influencer_posts`
--
ALTER TABLE `influencer_posts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `influencer_social_accounts`
--
ALTER TABLE `influencer_social_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payouts`
--
ALTER TABLE `payouts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `platform_settings`
--
ALTER TABLE `platform_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `affiliates`
--
ALTER TABLE `affiliates`
  ADD CONSTRAINT `fk_aff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `fk_at_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `fk_brand_industry` FOREIGN KEY (`industry_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_brand_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brand_products`
--
ALTER TABLE `brand_products`
  ADD CONSTRAINT `fk_bp_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brand_product_media`
--
ALTER TABLE `brand_product_media`
  ADD CONSTRAINT `fk_bpm_product` FOREIGN KEY (`product_id`) REFERENCES `brand_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `fk_camp_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_camp_product` FOREIGN KEY (`product_id`) REFERENCES `brand_products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `campaign_deals`
--
ALTER TABLE `campaign_deals`
  ADD CONSTRAINT `fk_cd_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cd_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cd_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_deliverables`
--
ALTER TABLE `deal_deliverables`
  ADD CONSTRAINT `fk_dd_deal` FOREIGN KEY (`deal_id`) REFERENCES `campaign_deals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencers`
--
ALTER TABLE `influencers`
  ADD CONSTRAINT `fk_inf_affiliate` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inf_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_inf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencer_posts`
--
ALTER TABLE `influencer_posts`
  ADD CONSTRAINT `fk_ip_account` FOREIGN KEY (`social_account_id`) REFERENCES `influencer_social_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `influencer_social_accounts`
--
ALTER TABLE `influencer_social_accounts`
  ADD CONSTRAINT `fk_isa_influencer` FOREIGN KEY (`influencer_id`) REFERENCES `influencers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oauth_accounts`
--
ALTER TABLE `oauth_accounts`
  ADD CONSTRAINT `fk_oauth_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `fk_pay_deal` FOREIGN KEY (`deal_id`) REFERENCES `campaign_deals` (`id`);

--
-- Constraints for table `payouts`
--
ALTER TABLE `payouts`
  ADD CONSTRAINT `fk_po_deal` FOREIGN KEY (`deal_id`) REFERENCES `campaign_deals` (`id`),
  ADD CONSTRAINT `fk_po_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_po_user` FOREIGN KEY (`payee_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD CONSTRAINT `fk_ps_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_deal` FOREIGN KEY (`deal_id`) REFERENCES `campaign_deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_reviewee` FOREIGN KEY (`reviewee_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_rev_reviewer` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
