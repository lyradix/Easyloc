-- This file is used to set up a fictionas database for billing and contract management --

USE [EasylocDb]

GO

INSERT INTO [dbo].[Billing] ([Id], [CustomerId], [Amount], [BillingDate])
VALUES (1, 1, 50.00, '2025-10-01'),
       (2, 2, 75.00, '2025-10-02'),
       (3, 3, 100.00, '2025-10-03');

INSERT INTO [dbo].[Contract] ([Id], [CustomerId], [StartDate], [EndDate], [Status])
VALUES (1, 1, '2021-01-01', '2025-12-31', 'Active'),
       (2, 2, '2021-01-01', '2025-11-30', 'Active'),
       (3, 3, '2021-03-01', '2025-10-31', 'Expired');