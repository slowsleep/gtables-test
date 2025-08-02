<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;
use App\Models\GoogleSheetSettings;

class GetGoogleSheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google-sheet:get {count? : Количество записей}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получить все записи из Google Sheet';

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
        $settings = GoogleSheetSettings::latest()->first();

        if (!$settings || !$settings->spreadsheet_id) {
            $this->error('Google Spreadsheet ID не задан.');
            return 0;
        }

        $googleSheetsService = new GoogleSheetsService($settings->spreadsheet_id);
        $count = (int)$this->argument('count');

        $sheetData = $count
            ? $googleSheetsService->getAllSheetData('A2:Z' . ($count + 1))
            : $googleSheetsService->getAllSheetData();

        $progressBar = $this->output->createProgressBar(count($sheetData));
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%%");
        $progressBar->start();

        foreach ($sheetData as $row) {
            $id = $row[0] ?? 'N/A';
            $comment = $row[5] ?? '';

            // Очищаем строку ProgressBar, выводим данные, затем обновляем ProgressBar
            $progressBar->clear();
            $this->line("ID: <comment>$id</comment> | Комментарий: <info>$comment</info>");
            $progressBar->display();

            $progressBar->advance();
            usleep(50000); // Задержка 50 мс для плавности
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Готово! Данные выведены.');

        return 0;
    }
}
