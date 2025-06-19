-- CREATE DATABASE IF NOT EXISTS hms_db;
-- USE hms_db;

-- CREATE TABLE users (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   full_name VARCHAR(100) NOT NULL,
--   email VARCHAR(100) NOT NULL UNIQUE,
--   password VARCHAR(255) NOT NULL,
--   phone VARCHAR(15),
--   picture VARCHAR(255),
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );


-- CREATE TABLE patients (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   full_name VARCHAR(100) NOT NULL,
--   email VARCHAR(100),
--   phone VARCHAR(20),
--   gender ENUM('Male', 'Female', 'Other'),
--   dob DATE,
--   address TEXT,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- CREATE TABLE doctors (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   full_name VARCHAR(100),
--   email VARCHAR(100),
--   phone VARCHAR(20),
--   specialization VARCHAR(100),
--   picture VARCHAR(255),
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );



-- CREATE TABLE appointments (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   patient_id INT NOT NULL,
--   doctor_name VARCHAR(100),
--   appointment_date DATETIME,
--   status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
--   note TEXT,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
-- );



-- CREATE TABLE medical_records (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   patient_id INT NOT NULL,
--   diagnosis TEXT,
--   treatment TEXT,
--   doctor_notes TEXT,
--   record_date DATE,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
-- );


-- CREATE TABLE prescriptions (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   patient_id INT NOT NULL,
--   medicines TEXT,
--   dosage TEXT,
--   prescribed_by VARCHAR(100),
--   date_prescribed DATE,
--   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
-- );


-- CREATE TABLE bills (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   patient_id INT NOT NULL,
--   total_amount DECIMAL(10, 2) NOT NULL,
--   status ENUM('Unpaid', 'Paid') DEFAULT 'Unpaid',
--   issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
-- );


-- CREATE TABLE payments (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   bill_id INT NOT NULL,
--   amount_paid DECIMAL(10, 2),
--   payment_method VARCHAR(50),
--   payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--   FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
-- );


CREATE DATABASE IF NOT EXISTS hms_db;
USE hms_db;

-- Users (system admins)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(15),
  picture VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE users 
ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL,
ADD COLUMN reset_expires_at DATETIME DEFAULT NULL;


-- Departments
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff Roles
CREATE TABLE staff_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) UNIQUE NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Staff
CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE,
  phone VARCHAR(20),
  gender ENUM('Male', 'Female', 'Other'),
  dob DATE,
  address TEXT,
  role_id INT NOT NULL,
  department_id INT,
  hire_date DATE,
  salary DECIMAL(10,2),
  picture VARCHAR(255),
  status ENUM('Active', 'Inactive') DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES staff_roles(id),
  FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Patients
CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  gender ENUM('Male', 'Female', 'Other'),
  dob DATE,
  blood_type VARCHAR(5),
  address TEXT,
  emergency_contact_name VARCHAR(100),
  emergency_contact_phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors (detailed)
CREATE TABLE doctors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE,
  phone VARCHAR(20),
  gender ENUM('Male', 'Female', 'Other'),
  dob DATE,
  address TEXT,
  specialization VARCHAR(100),
  department_id INT,
  license_number VARCHAR(100),
  years_of_experience INT,
  education TEXT,
  availability ENUM('Available', 'Unavailable') DEFAULT 'Available',
  status ENUM('Active', 'Suspended', 'Retired') DEFAULT 'Active',
  picture VARCHAR(255),
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (department_id) REFERENCES departments(id)
);


-- Rooms
CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(10) NOT NULL UNIQUE,
  type ENUM('General', 'Private', 'ICU', 'Emergency') NOT NULL,
  bed_count INT DEFAULT 1,
  availability_status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE wards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admissions
CREATE TABLE admissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  room_id INT NOT NULL,
  doctor_id INT,
  reason TEXT,
  admission_date DATETIME NOT NULL,
  discharge_date DATETIME,
  status ENUM('Admitted', 'Discharged') DEFAULT 'Admitted',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (room_id) REFERENCES rooms(id),
  FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Appointments
CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  appointment_date DATETIME NOT NULL,
  status ENUM('Pending', 'Completed', 'Cancelled') DEFAULT 'Pending',
  reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Medical Records
CREATE TABLE medical_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  diagnosis TEXT,
  treatment TEXT,
  doctor_notes TEXT,
  record_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Prescriptions
CREATE TABLE prescriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  doctor_id INT NOT NULL,
  date_prescribed DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id),
  FOREIGN KEY (doctor_id) REFERENCES doctors(id)
);

-- Prescription Items
CREATE TABLE prescription_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prescription_id INT NOT NULL,
  medicine_name VARCHAR(100),
  dosage VARCHAR(100),
  duration VARCHAR(50),
  notes TEXT,
  FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE
);

-- Bills
CREATE TABLE bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  total_amount DECIMAL(10, 2) NOT NULL,
  status ENUM('Unpaid', 'Paid', 'Partial') DEFAULT 'Unpaid',
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Payments
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  amount_paid DECIMAL(10, 2),
  payment_method VARCHAR(50),
  reference VARCHAR(100),
  payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bill_id) REFERENCES bills(id)
);

-- Payrolls
CREATE TABLE payrolls (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  salary_month VARCHAR(20),
  base_salary DECIMAL(10, 2),
  bonuses DECIMAL(10, 2) DEFAULT 0.00,
  deductions DECIMAL(10, 2) DEFAULT 0.00,
  net_salary DECIMAL(10, 2),
  payment_status ENUM('Pending', 'Paid') DEFAULT 'Pending',
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);
ALTER TABLE payrolls
ADD COLUMN doctor_id INT NULL AFTER staff_id,
ADD CONSTRAINT fk_doctor FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE;
ALTER TABLE payrolls 
MODIFY staff_id INT NULL,
MODIFY doctor_id INT NULL;


-- Lab Tests
CREATE TABLE lab_tests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  test_name VARCHAR(100),
  result TEXT,
  status ENUM('Requested', 'Completed', 'Cancelled') DEFAULT 'Requested',
  test_date DATE,
  FOREIGN KEY (patient_id) REFERENCES patients(id)
);

-- Inventory
CREATE TABLE inventory (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_name VARCHAR(100),
  category VARCHAR(100),
  quantity INT,
  unit VARCHAR(50),
  reorder_level INT,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
