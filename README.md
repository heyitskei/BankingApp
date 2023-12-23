Banking App

Overview Our Banking App is a web application that facilitates user registration, login, account management and a password reset mechanism. It's built using PHP for server-side logic and HTML for front-end presentation. The application uses a MySQL database to store user information and session details.

Key Features

User Registration: Allows users to sign up by providing essential information such as first name, last name, address, email, and password. Utilizes server-side validation to ensure accurate and complete user input. Hashes and securely stores user passwords using the password_hash function.

User Login: Verifies user identity through the login page, ensuring email and password match the stored credentials. Implements secure session handling using PHP's session_start to maintain user sessions. Incorporates MySQL queries to check and authenticate users against stored credentials.

Logout Functionality: Implements a logout mechanism (start.php) to terminate user sessions securely. Clears session variables and redirects users to the login page.

Password Reset: Employs a robust password reset mechanism initiated through forgot_password.php. Generates unique, secure tokens using PHP's random_bytes for enhanced security. Utilizes MySQL to store and manage reset tokens associated with user accounts.

Responsive UI: Ensures an optimal user experience with responsive design, accommodating various devices and screen sizes. Incorporates client-side validation for user input on the registration and login forms, enhancing user interaction.

Database Interaction: Manages user data persistence using a MySQL database (banking_app). SQL queries within PHP scripts (signup.php, login.php, forgot_password.php) interact with the database for user-related operations.

Prerequisites Before running the Banking App, ensure you have the following prerequisites installed:

PHP MySQL Web server Web browser

Installation

Clone Repository: bash Copy code git clone https://github.com/your-username/banking-app.git cd banking-app

Database Setup: Create a MySQL database named banking_app. Import the SQL schema from database.sql. Configure Database Connection:

Update database connection details in PHP files: login.php signup.php forgot_password.php reset_password.php

Start Web Server: Configure your web server to serve the banking-app directory. Start the web server.

Access the Application: Open your web browser and navigate to http://localhost/banking-app/start.php.

File Structure start.php: Main entry point. Handles user logout. login.php: User login page. signup.php: User registration page. signup_success.php: Success page after user registration. forgot_password.php: Page for initiating the password reset process. reset_password.php: Placeholder for password reset logic. reset_link_sent.php: Informs the user that a password reset link has been sent.

Detailed Explanation

start.php Clears user sessions. Redirects to the login page.

login.php Validates user login inputs. Checks user existence in the database. Initiates user sessions upon successful login.

signup.php Validates user registration inputs. Inserts new user into the database. Initiates user sessions after successful registration.

signup_success.php Displays a success message after user registration.

forgot_password.php Initiates the password reset process. Generates a unique token and stores it in the database.

reset_password.php Placeholder for password reset logic. To be implemented with actual reset functionality.

reset_link_sent.php Informs the user that a password reset link has been sent to their email. Provides a button to log out.

Contribution This project is a collaborative effort of Ekaterina (Kate) Stroganova, Mher Keshishian and Jay Mo, second year Conestoga SET students. The contents of this project are proprietary knowledge and are not to be distrubutes without the consent of all the parties involved.
