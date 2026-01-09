-- Sample Equipment Data for Equipment Rental App
-- Run this SQL in your phpMyAdmin or MySQL client to add sample data

-- First, make sure the equipments table exists
CREATE TABLE IF NOT EXISTS `equipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(255) NOT NULL,
  `specification` varchar(500) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `availability` varchar(50) DEFAULT 'Available',
  `year` varchar(10) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `power` varchar(50) DEFAULT NULL,
  `insurance` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clear existing sample data (optional - comment out if you want to keep existing data)
-- DELETE FROM equipments WHERE id > 0;

-- =============================================
-- TRACTORS (Category: Tractor)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('John Deere 5310', '55 HP, 2WD, Hydraulic steering, Power steering', 'Tractor', 850.00, 'Available', '2022', 'JD-5310', 'Diesel', '55 HP', 'Fully Insured', 'Reliable and powerful tractor suitable for all farming operations. Features excellent fuel efficiency and comfortable seating.', 'tractor1.jpg', 1),
('Mahindra 575 DI', '45 HP, 2WD, Constant mesh transmission', 'Tractor', 700.00, 'Available', '2021', 'M-575DI', 'Diesel', '45 HP', 'Fully Insured', 'Best-selling tractor with exceptional performance for Indian farms. Great for ploughing and transportation.', 'tractor2.jpg', 1),
('Sonalika DI 750 III', '50 HP, 4WD, Oil immersed brakes', 'Tractor', 900.00, 'Booked', '2023', 'S-750III', 'Diesel', '50 HP', 'Fully Insured', 'Premium 4WD tractor with advanced features for tough terrain. Excellent for hilly areas.', 'tractor3.jpg', 2),
('Massey Ferguson 241', '42 HP, 2WD, Dual clutch', 'Tractor', 650.00, 'Available', '2020', 'MF-241', 'Diesel', '42 HP', 'Basic Insurance', 'Affordable and durable tractor for small to medium farms. Easy maintenance.', 'tractor4.jpg', 2),
('Eicher 380', '38 HP, 2WD, Sliding mesh gearbox', 'Tractor', 600.00, 'Available', '2021', 'E-380', 'Diesel', '38 HP', 'Fully Insured', 'Compact and economical tractor perfect for small farms and orchards.', 'tractor5.jpg', 1);

-- =============================================
-- HARVESTERS (Category: Harvester)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Kubota DC-70', 'Self-propelled, 70 HP, 4.2m cutting width', 'Harvester', 2500.00, 'Available', '2022', 'DC-70', 'Diesel', '70 HP', 'Fully Insured', 'High-performance combine harvester for rice and wheat. Features GPS guidance system.', 'harvester1.jpg', 1),
('Class Crop Tiger 30', '65 HP, Terra Trac system, 3m header', 'Harvester', 2800.00, 'Available', '2023', 'CT-30', 'Diesel', '65 HP', 'Fully Insured', 'German engineering for superior harvesting. Minimal grain loss technology.', 'harvester2.jpg', 2),
('Preet 987', 'Combine harvester, 87 HP, Multi-crop', 'Harvester', 2200.00, 'Booked', '2021', 'P-987', 'Diesel', '87 HP', 'Fully Insured', 'Versatile harvester suitable for paddy, wheat, and soybean. Made in India.', 'harvester3.jpg', 1),
('John Deere W70', '75 HP, Straw walker, High capacity', 'Harvester', 3000.00, 'Available', '2022', 'JD-W70', 'Diesel', '75 HP', 'Fully Insured', 'Premium harvester with large grain tank and efficient threshing system.', 'harvester4.jpg', 2);

-- =============================================
-- TILLERS (Category: Tiller)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Honda FJ500', '5.5 HP, 4-stroke engine, 500mm tilling width', 'Tiller', 400.00, 'Available', '2022', 'FJ500', 'Petrol', '5.5 HP', 'Basic Insurance', 'Lightweight power tiller perfect for vegetable gardens and small plots.', 'tiller1.jpg', 1),
('Kamco Power Tiller', '12 HP, Diesel, 750mm working width', 'Tiller', 550.00, 'Available', '2021', 'KPT-12', 'Diesel', '12 HP', 'Fully Insured', 'Heavy-duty power tiller for larger fields. Great for paddy cultivation.', 'tiller2.jpg', 2),
('VST Shakti 130 DI', '13 HP, 4WD, Rotary tiller attached', 'Tiller', 600.00, 'Available', '2023', 'VS-130DI', 'Diesel', '13 HP', 'Fully Insured', 'Versatile power tiller with multiple attachments available.', 'tiller3.jpg', 1),
('Kirloskar Mega T', '15 HP, Water-cooled engine, Heavy duty', 'Tiller', 650.00, 'Booked', '2022', 'KMT-15', 'Diesel', '15 HP', 'Fully Insured', 'Industrial-grade tiller for commercial farming operations.', 'tiller4.jpg', 2);

-- =============================================
-- SPRAYERS (Category: Sprayer)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Aspee Boom Sprayer', 'Tractor mounted, 500L tank, 12m boom', 'Sprayer', 350.00, 'Available', '2022', 'ABS-500', 'N/A', 'N/A', 'Basic Insurance', 'Large capacity boom sprayer for efficient pesticide application.', 'sprayer1.jpg', 1),
('Mitra Knapsack Sprayer', '16L capacity, Manual pump, Adjustable nozzle', 'Sprayer', 100.00, 'Available', '2023', 'MKS-16', 'Manual', 'N/A', 'Not Insured', 'Portable knapsack sprayer for small farms and gardens.', 'sprayer2.jpg', 2),
('Neptune Power Sprayer', 'Battery operated, 20L tank, 6 bar pressure', 'Sprayer', 200.00, 'Available', '2022', 'NPS-20', 'Battery', 'N/A', 'Basic Insurance', 'Rechargeable battery sprayer with long operation time.', 'sprayer3.jpg', 1),
('Maruti Mist Blower', '14 HP, 400L tank, Orchard sprayer', 'Sprayer', 500.00, 'Booked', '2021', 'MMB-14', 'Petrol', '14 HP', 'Fully Insured', 'Powerful mist blower for orchards and vineyards. Covers large areas quickly.', 'sprayer4.jpg', 2);

-- =============================================
-- PLOUGHS (Category: Plough)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('MB Plough 3 Furrow', '3 furrow, Hydraulic reversible, Heavy duty', 'Plough', 300.00, 'Available', '2022', 'MBP-3F', 'N/A', 'N/A', 'Basic Insurance', 'Reversible mould board plough for deep tillage. Suitable for 45+ HP tractors.', 'plough1.jpg', 1),
('Disc Plough 3 Disc', '3 disc, 660mm diameter, Tractor mounted', 'Plough', 280.00, 'Available', '2021', 'DP-3D', 'N/A', 'N/A', 'Not Insured', 'Disc plough for tough and stony soils. Excellent for land clearing.', 'plough2.jpg', 2),
('Chisel Plough 7 Tyne', '7 tyne, 2.1m width, Heavy duty', 'Plough', 350.00, 'Available', '2023', 'CP-7T', 'N/A', 'N/A', 'Basic Insurance', 'Deep tillage chisel plough for breaking hardpan. Improves soil structure.', 'plough3.jpg', 1),
('Ridger Plough', '2 furrow, Adjustable width, Bed former', 'Plough', 250.00, 'Booked', '2022', 'RP-2F', 'N/A', 'N/A', 'Not Insured', 'Ideal for making ridges and furrows for vegetable cultivation.', 'plough4.jpg', 2);

-- =============================================
-- SEEDERS (Category: Seeder)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Khedut Seed Drill', '9 row, Tractor mounted, Fertilizer attachment', 'Seeder', 400.00, 'Available', '2022', 'KSD-9R', 'N/A', 'N/A', 'Basic Insurance', 'Precision seed drill with simultaneous fertilizer application capability.', 'seeder1.jpg', 1),
('Mahindra Planter', '4 row, Precision planting, Vacuum type', 'Seeder', 500.00, 'Available', '2023', 'MP-4R', 'N/A', 'N/A', 'Fully Insured', 'Advanced precision planter for maize and cotton. Ensures optimal seed spacing.', 'seeder2.jpg', 2),
('Zero Till Drill', '11 tyne, Direct seeding, Residue management', 'Seeder', 450.00, 'Booked', '2022', 'ZTD-11', 'N/A', 'N/A', 'Basic Insurance', 'Zero tillage seed drill for conservation agriculture. Saves fuel and time.', 'seeder3.jpg', 1),
('Rotary Dibbler', 'Manual operated, 4 row, For wet paddy', 'Seeder', 150.00, 'Available', '2021', 'RD-4', 'Manual', 'N/A', 'Not Insured', 'Simple manual seeder for transplanting rice seedlings.', 'seeder4.jpg', 2);

-- =============================================
-- CULTIVATORS (Category: Cultivator)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Spring Loaded Cultivator', '9 tyne, 2.1m width, Heavy spring', 'Cultivator', 280.00, 'Available', '2022', 'SLC-9', 'N/A', 'N/A', 'Basic Insurance', 'Robust cultivator for secondary tillage. Spring tynes handle stones well.', 'cultivator1.jpg', 1),
('Duck Foot Cultivator', '7 tyne, Sweeps attached, Weed control', 'Cultivator', 250.00, 'Available', '2021', 'DFC-7', 'N/A', 'N/A', 'Not Insured', 'Excellent for inter-row cultivation and weed control.', 'cultivator2.jpg', 2),
('Rigid Tyne Cultivator', '11 tyne, Heavy duty frame, Deep tillage', 'Cultivator', 320.00, 'Booked', '2023', 'RTC-11', 'N/A', 'N/A', 'Basic Insurance', 'Heavy-duty cultivator for deep secondary tillage operations.', 'cultivator3.jpg', 1);

-- =============================================
-- ROTAVATORS (Category: Rotavator)
-- =============================================
INSERT INTO `equipments` (`equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`) VALUES
('Shaktiman Rotavator', '5 feet, 42 blades, Multi-speed gearbox', 'Rotavator', 500.00, 'Available', '2022', 'SR-5F', 'N/A', 'N/A', 'Fully Insured', 'Premium quality rotavator for perfect seedbed preparation. Heavy-duty gearbox.', 'rotavator1.jpg', 1),
('Fieldking Rotavator', '6 feet, L-shaped blades, Chain drive', 'Rotavator', 550.00, 'Available', '2023', 'FK-6F', 'N/A', 'N/A', 'Fully Insured', 'Wide working width rotavator for faster field coverage.', 'rotavator2.jpg', 2),
('Maschio Rotavator', '4 feet, Italian design, Side shift', 'Rotavator', 600.00, 'Available', '2022', 'MR-4F', 'N/A', 'N/A', 'Fully Insured', 'Imported Italian rotavator with superior build quality and finish.', 'rotavator3.jpg', 1),
('Landforce Rotavator', '5 feet, 36 blades, Economy model', 'Rotavator', 400.00, 'Booked', '2021', 'LF-5F', 'N/A', 'N/A', 'Basic Insurance', 'Affordable rotavator suitable for 35-45 HP tractors.', 'rotavator4.jpg', 2);

-- Verify the data was inserted
SELECT type, COUNT(*) as count FROM equipments GROUP BY type ORDER BY type;
