<?php


namespace App\Service;


use GuzzleHttp\Client;

class FinmindService
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.finmindtrade.com/api/'
        ]);
    }
    public function getStockInfo()
    {
        $result = $this->finmind('v4/data', [
            'dataset' => 'TaiwanStockInfo',
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    public function getStockPrice($stockId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->format('Y-m-d');
        $endDate = $endDate ?: now()->format('Y-m-d');

        $result = $this->finmind('v4/data', [
            'dataset' => 'TaiwanStockPrice',
            'data_id' => $stockId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    public function getStockPriceTick($stockId, $date = null)
    {
        $date = $date ?: now()->format('Y-m-d');

        $result = $this->finmind('v4/data', [
            'dataset' => 'TaiwanStockPriceTick',
            'data_id' => $stockId,
            'start_date' => $date,
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    public function getTrader()
    {
        $result = $this->finmind('v4/data', [
            'dataset' => 'SecuritiesTraderInfo',
            'token' => config('finance.finmind_token'),
        ]);
        return $result['data'];
    }

    protected function finmind($uri, $query)
    {
        $response = $this->client->request('GET', $uri, ['query' => $query]);

        $result = $response->getBody()->getContents();
        return json_decode($result, true);
    }
}
