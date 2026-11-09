-- Run once against an existing booking_manager database.
-- Adds a monetary amount to each booking. Existing bookings default to R0.00.
USE booking_manager;
ALTER TABLE bookings
    ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER status;