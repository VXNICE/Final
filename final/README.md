# StayFind Manual Payment Demo

## Installation
1. Place the `final/` directory in your web root (e.g. `htdocs`).
2. Create a MySQL database and run the migrations:
   - `migrations/2025_09_create_bookings_and_payments.sql`
   - existing files in `sql/migrations/` as needed.
3. Seed sample data:
```sql
INSERT INTO users (id,name,email,password,role_name) VALUES
 (1,'Admin','admin@example.com',PASSWORD('secret'),'admin'),
 (2,'User','user@example.com',PASSWORD('secret'),'user');

INSERT INTO rooms (id,owner_id,title,location,price_per_night,capacity,image_path)
VALUES (1,1,'Sample Room','City',1000,2,NULL);
```
4. Adjust `includes/db.php` with your DB credentials if necessary.

## Usage
- Open `login.html` and sign in using the seeded users.
- Browse rooms in `dashboard.html`.
- Click **View** then **Book Now** to create a booking. After creation the payment modal opens to upload proof (GCash, PayMaya, bank transfer or COD).
- Admins can view and approve/decline payments at `admin_bookings.php`.

## Tests
- Create a booking and verify `bookings` table row has `status_id=2` and `payment_status='unpaid'`.
- Upload a payment receipt and confirm `payment_status='pending'` and `payment_receipt_path` is set.
- As admin, approve the booking and ensure `payment_status='paid'` and `status_id=1`.
- Ensure modals open/close without console errors.
