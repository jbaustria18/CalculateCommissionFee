## Running the Application (Command Prompt)
1. Go to applicaition directory
2. Run command **php artisan calculate:commission {path}**
    - the parameter is the path of csv file including filename
3. This app uses cache to store data to clear cache just run command
   **php artisan cache:clear**
   
## Environment Variables
- **Total Withdrawal Operation Limit Free of Charge**
  - MAX_WITHDRAW_PER_WEEK=3

- **CSV Indexes**
  - OPERATION_DATE_IDX=0
  - UID_IDX=1
  - USER_TYPE_IDX=2
  - OPT_TYPE_IDX=3
  - OPT_AMOUNT_IDX=4
  - OPT_CURRENCY_IDX=5

- **Operation Charges**
  - DEPOSIT_CHARGE=0.03
  - WITHDRAW_PRIVATE=0.3
  - WITHDRAW_BUSINESS=0.5

- **Exchange Rates**
  - USD_EXCHANGE_RATE=1.1497
  - JPY_EXCHANGE_RATE=129.53
