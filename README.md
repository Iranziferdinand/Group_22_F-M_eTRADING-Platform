# Group_22_F-M_eTRADING-Platform

# F&I Trading Platform USSD Application

This is a USSD application for the F&I Trading Platform that allows users to register and access trading services through their mobile phones. The application includes SMS notifications for user registration and other important events.

## Developers
- Manirakiza Daniel
- Iranzi Ferdinand

## Prerequisites

1. XAMPP (or similar PHP development environment)
2. PHP 7.4 or higher
3. MySQL database
4. Africa's Talking account (for SMS functionality)
5. Composer (for PHP dependencies)

3. *Database Setup*
   - Create a new MySQL database
   - Import the database schema from database.sql
   - Update database credentials in config.php

4. *Africa's Talking Setup*
   - Sign up for an Africa's Talking account
   - Get your API key from the dashboard
   - Add your phone number to the sandbox environment
   - Update the following in config.php:
     php
     define('AT_USERNAME', 'sandbox');  // Use 'sandbox' for testing
     define('AT_API_KEY', 'your_api_key_here');
     define('AT_SENDER_ID', '45702');  // Your sender ID or shortcod
     

5. *Configure XAMPP*
   - Place the project in htdocs directory
   - Start Apache and MySQL services
   - Ensure PHP has OpenSSL extension enabled

## Testing SMS Functionality

1. *Test SMS Service*
  
   php test_sms.php
   
   This will send a test message to verify SMS functionality.



## USSD Flow

1. *New User Registration*
   - User dials USSD code
   - Selects "Register" option
   - Enters name, email, and phone number
   - Receives confirmation SMS

2. *Registered User Menu*
   - Buyer Services
   - Seller Services
   - Back option


## Troubleshooting

1. *SMS Not Working*
   - Verify Africa's Talking credentials
   - Check if phone number is in sandbox
   - Review PHP error logs
   - Run test_sms.php for detailed debugging

2. *USSD Issues*
   - Check database connection
   - Verify USSD code configuration
   - Review session handling

3. *Common Errors*
   - SSL/TLS issues: Ensure proper SSL configuration
   - Database connection: Verify credentials
   - API errors: Check Africa's Talking dashboard

## Development

1. *Adding New Features*
   - Update Menu.php for new USSD options
   - Add corresponding database tables if needed
   - Test thoroughly in sandbox environment

2. *Testing*
   - Use test_sms.php for SMS testing
   - Test USSD flow with different scenarios
   - Verify database operations

## Security Considerations

1. *API Keys*
   - Never commit API keys to version control
   - Use environment variables or secure configuration
   - Rotate keys periodically

2. *User Data*
   - Validate all user inputs
   - Sanitize database queries
   - Implement proper error handling
