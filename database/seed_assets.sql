-- =========================================================================
-- SEED DATA UPDATE: CATEGORIES & 20 REALISTIC ASSETS
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `asset_categories`;
TRUNCATE TABLE `assets`;
SET FOREIGN_KEY_CHECKS = 1;

-- Seed Categories
INSERT INTO `asset_categories` (`id`, `name`, `description`) VALUES
(1, 'Conference Rooms', 'Shared organizational meeting spaces and boardrooms'),
(2, 'Project Vehicles', 'Company fleet cars, trucks, and vans for site transit'),
(3, 'IT Hardware', 'Enterprise laptops, workstations, and server nodes'),
(4, 'Office Furniture', 'Ergonomic desks, task chairs, and modular setups'),
(5, 'Lab Equipment', 'Staging sandbox setups, network hardware, and testers');

-- Seed 20 Assets
INSERT INTO `assets` (`id`, `asset_tag`, `category_id`, `name`, `model`, `serial_number`, `purchase_date`, `purchase_cost`, `depreciation_rate`, `status`, `location`) VALUES
(1,  'AF-000001', 1, 'Boardroom Alpha', 'EXEC-BOARD-A', 'SR-CONF-001', '2024-01-15', 50000.00, 10.00, 'Available',   'Headquarters 4th Floor'),
(2,  'AF-000002', 1, 'Executive Suite B', 'EXEC-SUITE-B', 'SR-CONF-002', '2024-03-10', 35000.00, 10.00, 'Available',   'Headquarters 3rd Floor'),
(3,  'AF-000003', 2, 'Toyota Hilux 4x4', 'T-HILUX-2024', 'SR-VEH-0003', '2024-05-20', 45000.00, 15.00, 'Available',   'Garage Bay 1'),
(4,  'AF-000004', 2, 'Ford Transit Van', 'F-TRANSIT-V',  'SR-VEH-0004', '2024-02-18', 38000.00, 15.00, 'Allocated',   'Warehouse Area A'),
(5,  'AF-000005', 3, 'MacBook Pro 16"',  'M3-PRO-16',    'SR-HW-00005', '2024-06-01', 2500.00,  20.00, 'Allocated',   'IT Support Office'),
(6,  'AF-000006', 3, 'ThinkPad T14s',    'LENOVO-T14S',  'SR-HW-00006', '2024-06-15', 1500.00,  20.00, 'Available',   'IT Storage Locker'),
(7,  'AF-000007', 3, 'Dell UltraSharp 32"','DELL-U32-4K', 'SR-HW-00007', '2024-07-02', 850.00,   20.00, 'Available',   'Engineering Lab'),
(8,  'AF-000008', 5, 'Cisco Catalyst 9300','CISCO-9300-S','SR-NET-0008', '2024-01-20', 4200.00,  20.00, 'Maintenance', 'Server Room B'),
(9,  'AF-000009', 4, 'Ergonomic Task Chair','STEELCASE-G','SR-FURN-009', '2024-04-12', 650.00,   10.00, 'Available',   'Main Office Open Space'),
(10, 'AF-000010', 4, 'Standing Desk Pro', 'HERMAN-S-D',   'SR-FURN-010', '2024-04-14', 980.00,   10.00, 'Available',   'Executive Wing Office'),
(11, 'AF-000011', 1, 'Seminar Room C',    'SEM-ROOM-C',   'SR-CONF-011', '2024-08-01', 22000.00, 10.00, 'Reserved',    'Training Center Annex'),
(12, 'AF-000012', 2, 'Tesla Model 3 Fleet','TESLA-M3-24',  'SR-VEH-0012', '2024-09-12', 40000.00, 15.00, 'Available',   'Ev Charger Station'),
(13, 'AF-000013', 3, 'iPad Pro 12.9"',    'APPLE-IPAD-P', 'SR-HW-00013', '2024-07-22', 1200.00,  20.00, 'Available',   'Marketing Storage'),
(14, 'AF-000014', 5, 'Oscilloscope Rig',  'RIGOL-DS7000', 'SR-LAB-0014', '2024-05-18', 6800.00,  15.00, 'Available',   'Research & Dev Area'),
(15, 'AF-000015', 3, 'iPhone 15 Pro Dev', 'IPHONE-15-PD', 'SR-HW-00015', '2024-10-01', 1100.00,  25.00, 'Lost',        'Mobile Dev Sandbox'),
(16, 'AF-000016', 1, 'Meeting Room 101',  'CONF-101',     'SR-CONF-016', '2024-11-10', 12000.00, 10.00, 'Available',   'Main Building Wing A'),
(17, 'AF-000017', 1, 'Meeting Room 102',  'CONF-102',     'SR-CONF-017', '2024-11-12', 12500.00, 10.00, 'Available',   'Main Building Wing A'),
(18, 'AF-000018', 2, 'Heavy Cargo Trailer', 'CARGO-TRAIL', 'SR-VEH-0018', '2024-02-05', 8500.00,  15.00, 'Retired',     'Logistics Parking Yard'),
(19, 'AF-000019', 5, 'Precision Power Supply','KEYSIGHT-P','SR-LAB-0019', '2024-06-30', 3100.00,  15.00, 'Available',   'Electronics Lab Desk 4'),
(20, 'AF-000020', 5, 'Gigabit Firewall Lab','FORTINET-60F','SR-NET-0020', '2024-07-15', 1800.00,  20.00, 'Available',   'Network Test Sandbox');
