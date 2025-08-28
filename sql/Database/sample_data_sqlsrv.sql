-- This file is used to set up a fictionas database for billing and contract management --

USE [EasylocDb]

GO

INSERT INTO [dbo].[Billing] (CONTRACT_ID, AMOUNT)
VALUES (1, 50.00),
       (2, 75.00),
       (3, 100.00);

INSERT INTO [dbo].[Contract] (VEHICLE_UID, CUSTOMER_UID, SIGN_DATETIME, LOC_BEGIN_DATETIME, LOC_END_DATETIME, RETURNING_DATETIME, PRICE)
VALUES ('AB-123-CD', '1', '2021-01-01', '2021-01-01', '2025-12-31', '2025-12-31', 50.00),
       ('EF-456-GH', '2', '2021-01-01', '2021-01-01', '2025-11-30', '2025-11-30', 75.00),
       ('IJ-789-KL', '3', '2021-03-01', '2021-03-01', '2025-10-31', '2025-10-31', 100.00);


-- Insert admin user (password: Admin123!)
SET IDENTITY_INSERT [dbo].[User] ON;
INSERT INTO [dbo].[User] ([ID], [USERNAME], [EMAIL], [PASSWORD], [ROLE])
VALUES (
    1,
    'admin',
    'admin@easyloc.com',
    '$2y$12$3aekyUL1GGu5h8q4aXB35u1tnMUEL3hSNd93Huja8iBmxjuOgOBte',  -- Password: Admin123
    'ROLE_ADMIN'
);
SET IDENTITY_INSERT [dbo].[User] OFF;
