-- Database: digital_store
-- Create Tables

-- Table structure for table `orders`
CREATE TABLE [orders] (
  [id] INT NOT NULL IDENTITY(1,1),  -- Auto Increment
  [user_id] INT NOT NULL,
  [product_id] INT NOT NULL,
  [order_date] DATETIME NOT NULL DEFAULT GETDATE(),  -- Changed to DATETIME
  PRIMARY KEY ([id])
);

-- Table structure for table `products`
CREATE TABLE [products] (
  [id] INT NOT NULL IDENTITY(1,1),  -- Auto Increment
  [name] NVARCHAR(100) NOT NULL,  -- Changed to NVARCHAR
  [description] NVARCHAR(MAX) NULL,  -- Changed to NVARCHAR(MAX)
  [price] DECIMAL(10, 2) NOT NULL,
  [file_path] NVARCHAR(255) NOT NULL,  -- Changed to NVARCHAR
  [image_url] NVARCHAR(255) DEFAULT 'https://placehold.co/400x250/cccccc/333333?text=No+Image'  -- Changed to NVARCHAR
  PRIMARY KEY ([id])
);

-- Table structure for table `users`
CREATE TABLE [users] (
  [id] INT NOT NULL IDENTITY(1,1),  -- Auto Increment
  [username] NVARCHAR(50) NOT NULL UNIQUE,  -- Changed to NVARCHAR
  [password] NVARCHAR(255) NOT NULL,  -- Changed to NVARCHAR
  [created_at] DATETIME NOT NULL DEFAULT GETDATE(),  -- Changed to DATETIME
  [is_admin] BIT DEFAULT 0,  -- Changed to BIT
  [profile_picture_url] NVARCHAR(255) NULL,  -- Changed to NVARCHAR
  [admin_profile_picture_url] NVARCHAR(255) NULL,  -- Changed to NVARCHAR
  PRIMARY KEY ([id])
);

-- Insert Data for Table `orders`
INSERT INTO [orders] ([user_id], [product_id], [order_date]) VALUES
(8, 4, '2025-07-16 06:46:27'),
(7, 5, '2025-07-16 08:21:01'),
(6, 6, '2025-07-16 09:41:32'),
(6, 5, '2025-07-16 09:41:34'),
(6, 4, '2025-07-16 09:41:37'),
(6, 4, '2025-07-17 05:11:06'),
(10, 4, '2025-07-17 06:26:07');

-- Insert Data for Table `products`
INSERT INTO [products] ([name], [description], [price], [file_path], [image_url]) VALUES
('Solo Leveling Volumes 1-12', 'Experience the full journey of Sung Jin-Woo in this complete 12-volume Solo Leveling manga set. This collection includes Volumes 1 through 12 in official English paperback editions published by Yen Press.', 219.99, 'https://m.media-amazon.com/images/I/51xXzQiqEDL._SX342_SY445_.jpg', 'https://m.media-amazon.com/images/I/51xXzQiqEDL._SX342_SY445_.jpg'),
('Naruto, Vol. 1: Uzumaki Naruto', 'Naruto is a young shinobi with an incorrigible knack for mischief. He’s got a wild sense of humor, but Naruto is completely serious about his mission to be the world’s greatest ninja!', 23.89, 'https://m.media-amazon.com/images/I/91RpwagB7uL._SY466_.jpg', 'https://m.media-amazon.com/images/I/91RpwagB7uL._SY466_.jpg'),
('Blue Lock Season3 Manga', 'Earlier, the producer Ryoya Arisawa shared that ‘Blue Lock Season 3’ is expected to happen. Meanwhile, there are a lot of things left to show in anime from the manga series ‘Blue Lock’, which is one of the most popular manga in the world.', 30.63, 'https://news24online.com/wp-content/uploads/2024/12/blue-lock-season-3.jpg', 'https://news24online.com/wp-content/uploads/2024/12/blue-lock-season-3.jpg'),
('Attack on Titan Season 3 Part 1', 'After helping the Garrison to victory, retaking Trost District from the Titans, Eren awakens in a prison cell. He may be a hero to the common people, but among the leaders of humanity, fear of Eren\'s mysterious powers threatens his continued survival.', 39.47, 'https://m.media-amazon.com/images/I/81HyYI4bBbL._SY342_.jpg', 'https://m.media-amazon.com/images/I/81HyYI4bBbL._SY342_.jpg');

-- Insert Data for Table `users`
INSERT INTO [users] ([username], [password], [created_at], [is_admin], [profile_picture_url], [admin_profile_picture_url]) VALUES
('NxaYGzz', '$2y$10$E2SsbgFix/x4cnRvLA1U9O/uyv6eXGgJuh75P1NWjTdO5AAiTlzIm', '2025-07-16 05:45:33', 1, '../uploads/admin_profile_pictures/admin_profile_68789ea409fcd.png', NULL),
('vathna', '$2y$10$GBMM2mODR0X7OryHl93YteMS.LPAU5h/BG76CRWTtHKGxzJCJmwA.', '2025-07-16 06:34:52', 0, NULL, NULL),
('naayg', '$2y$10$cUdbs2EK474TEfMGIAg1e.FZIO9f6m.kjV5cozjDuLnEhheS2a/iS', '2025-07-16 06:46:10', 0, NULL, NULL),
('hostaaa', '$2y$10$LZk0LhWrv98BEt/0I5zvy.4UThHlS0hwyatZ3R5pjVFt8DidVPlEy', '2025-07-17 06:22:32', 0, 'https://via.placeholder.com/40/0A5CFF/FFFFFF?text=H', NULL),
('testUser', '$2y$10$3OM1FaSsOKU7HXOSHU/8POlZzhh6/OxfJYH4UFYCKBkf18K4TW9i6', '2025-07-17 06:25:32', 0, 'uploads/profile_pictures/profile_6878977a63fe4.png', NULL),
('123456', '$2y$10$7rS.4BCcPloVIGqJlF0ldO4Jfg.jEM54HP.P8T3Oh08g4u71l3oEG', '2025-07-17 06:29:41', 0, '../uploads/profile_pictures/profile_6878985559720.png', NULL);

-- Create Foreign Key Constraints

-- Foreign Key for `orders` table (user_id and product_id references)
ALTER TABLE [orders] ADD CONSTRAINT FK_orders_user_id FOREIGN KEY ([user_id]) REFERENCES [users]([id]) ON DELETE CASCADE;
ALTER TABLE [orders] ADD CONSTRAINT FK_orders_product_id FOREIGN KEY ([product_id]) REFERENCES [products]([id]) ON DELETE CASCADE;
