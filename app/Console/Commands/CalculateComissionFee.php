<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CalculateComissionFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:commission {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Commission Fee Calculation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $data = $this->readCsv();
        $output = array();

        for($i = 0; $i < count($data); $i++) {
            array_push($output,$this->process($data[$i]));
        }

        foreach ($output as $val)
        {
            $this->info($val);
        }
    }

    private function process($data)
    {
        $uid = $data[config('commission.csv_settings.uid_idx')];
        $optDate = $data[config('commission.csv_settings.date_idx')];
        $amount = $data[config('commission.csv_settings.amount_idx')];
        $userType = $data[config('commission.csv_settings.type_idx')];
        $currency = $data[config('commission.csv_settings.currency_idx')];
        $fee = 0;

        switch($data[config('commission.csv_settings.opt_idx')])
        {
            case 'deposit':
                $fee = $amount * (config('commission.deposit_charge') / 100);
                break;
            case 'withdraw':
                if($userType == 'business')
                {
                    $fee = $amount * (config('commission.withdraw_business_charge') / 100);
                }
                else
                {
                    $date = Carbon::parse($optDate);
                    $startOfWeek = $date->startOfWeek(Carbon::MONDAY)->format('Y-m-d H:i');
                    $endOfWeek = $date->endOfWeek(Carbon::SUNDAY)->format('Y-m-d H:i');
                    Cache::add($startOfWeek . $endOfWeek . $uid . 'withdrawal',1, 86400);
                    Cache::add($startOfWeek . $endOfWeek . $uid . 'limit',1000, 86400);

                    $converted = $currency != 'EUR' ? $this->convertToEur($amount,$currency) : $amount;

                    if(Cache::get($startOfWeek . $endOfWeek . $uid . 'withdrawal') <= intval(config('commission.withdraw_per_week_limit')))
                    {
                        Cache::increment($startOfWeek . $endOfWeek . $uid . 'withdrawal');

                        if (Cache::get($startOfWeek . $endOfWeek . $uid . 'limit') == 0)
                        {
                            $fee = $converted * (config('commission.withdraw_private_charge') / 100);
                        }
                        else
                        {
                            if($converted <= Cache::get($startOfWeek . $endOfWeek . $uid . 'limit'))
                            {
                                Cache::decrement($startOfWeek . $endOfWeek . $uid . 'limit', $converted);
                            }
                            else
                            {
                                $converted -= Cache::get($startOfWeek . $endOfWeek . $uid . 'limit');
                                $fee = $converted * (config('commission.withdraw_private_charge') / 100);

                                Cache::decrement($startOfWeek . $endOfWeek . $uid . 'limit', Cache::get($startOfWeek . $endOfWeek . $uid . 'limit'));
                            }
                        }
                    }
                    else
                    {
                        $fee = $converted * (config('commission.withdraw_private_charge') / 100);
                    }

                    $fee = $currency != 'EUR' ? $this->convertBack($fee,$currency) : $fee;
                }
                break;
            default:
                break;
        }

        return $this->formattedValue($amount,$fee);
    }

    private function convertToEur($amount, $currency)
    {
        return $amount / env(strtoupper($currency) . '_EXCHANGE_RATE');
    }

    private function convertBack($amount, $currency)
    {
        return $amount * env(strtoupper($currency) . '_EXCHANGE_RATE');
    }

    private function formattedValue($amount, $fee)
    {
        $amountDecimalPlaces = str_contains($amount, '.') ? strlen(strrchr($amount,'.')) - 1 : 0;
        $feeDecimalPlaces = str_contains($fee, '.') ? strlen(strrchr($fee,'.')) - 1 : 0;

        if(str_contains($fee,'.') && $amountDecimalPlaces == 0)
        {
            $fee += 1;
        }

        if($amountDecimalPlaces > 0 && $feeDecimalPlaces > $amountDecimalPlaces)
        {

        }

        return number_format($fee, $amountDecimalPlaces, '.', '');
    }

    private function readCsv(): array
    {
        $csv = file($this->argument('path'));
        $data = array();

        foreach ($csv as $line) {
            array_push($data, str_getcsv($line));
        }

        return $data;
    }
}
