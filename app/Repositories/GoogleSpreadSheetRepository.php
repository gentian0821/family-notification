<?php

namespace App\Repositories;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Drive;
use Google_Service_Sheets_ValueRange;
use Illuminate\Support\Facades\Config;

class GoogleSpreadSheetRepository
{
    private Google_Service_Sheets $googleServiceSheet;

    public function __construct(private Google_Client $googleClient)
    {
        $json = json_decode(Config::get('const.google_api_credential'), true);

        $this->googleClient->setApplicationName('calendar');
        $this->googleClient->setAuthConfig($json);
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setScopes([Google_Service_Sheets::SPREADSHEETS, Google_Service_Drive::DRIVE]);
        $this->googleServiceSheet = new Google_Service_Sheets($googleClient);
    }

    public function getSheet(string $sheetId, string $range): array
    {
        // $response = $this->googleServiceSheet->spreadsheets->get($sheetId);
        $response = $this->googleServiceSheet->spreadsheets_values->get($sheetId, $range);

        return $response->getValues();
    }

    public function appendRows(string $sheetId, string $range, array $rows): void
    {
        // $rows はすでに [[行1], [行2], ...] の形式であることを想定
        $body = new Google_Service_Sheets_ValueRange([
            'values' => $rows
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED',
            'insertDataOption' => 'INSERT_ROWS'
        ];

        $this->googleServiceSheet->spreadsheets_values->append($sheetId, $range, $body, $params);
    }
}
